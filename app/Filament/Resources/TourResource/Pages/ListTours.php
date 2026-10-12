<?php

namespace App\Filament\Resources\TourResource\Pages;

use App\Filament\Resources\TourResource;
use App\Models\Tour;
use App\Models\TourPricingTier;
use App\Models\TourDeparture;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use Filament\Actions\Action;
use Carbon\Carbon;

class ListTours extends ListRecords
{
    protected static string $resource = TourResource::class;
    protected static string $view = 'filament.pages.list-tours';

    #[Url(as: 'pricing_id')]
    public ?int $selectedPricingOfferId = null;

    #[Url(as: 'departure_id')]
    public ?int $selectedDepartureId = null;

    public ?string $pricingSearch = null;
    public string $pricingTripTypeFilter = 'all';
    public ?string $departureDateFilter = null;
    public ?string $departureTimeFilter = null;
    public string $pricingVehicleFilter = 'all';
    public string $pricingTypeFilter = 'all';

    private function cardQuery(TourPricingTier $tier): Builder
    {
        return TourPricingTier::where('is_base', $tier->is_base)
            ->where('vehicle_type', $tier->vehicle_type)
            ->where('trip_type', $tier->trip_type)
            ->where('pax_count', $tier->pax_count)
            ->where('offer_name', $tier->offer_name)
            ->where('total_price', $tier->total_price)
            ->where('deposit_amount', $tier->deposit_amount);
    }

    private function applyDepartureCardConditions(Builder $query, TourDeparture $dep): Builder
    {
        $normalizedTime = Carbon::parse($dep->departure_time)->format('H:i:s');
        $query->whereTime('departure_time', $normalizedTime);

        if ($dep->departure_date) {
            $query->whereDate('departure_date', Carbon::parse($dep->departure_date)->toDateString());
        } else {
            $query->whereNull('departure_date');
        }

        if ($dep->return_time) {
            $normalizedReturn = Carbon::parse($dep->return_time)->format('H:i:s');
            $query->whereTime('return_time', $normalizedReturn);
        } else {
            $query->whereNull('return_time');
        }

        return $query;
    }

    private function departureCardQuery(TourDeparture $dep): Builder
    {
        return $this->applyDepartureCardConditions(TourDeparture::query(), $dep);
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if ($this->selectedPricingOfferId) {
            $referenceTier = TourPricingTier::find($this->selectedPricingOfferId);
            if ($referenceTier) {
                $query->whereHas('pricingTiers', function ($q) use ($referenceTier) {
                    $q->where('is_base', $referenceTier->is_base)
                      ->where('vehicle_type', $referenceTier->vehicle_type)
                      ->where('trip_type', $referenceTier->trip_type)
                      ->where('pax_count', $referenceTier->pax_count)
                      ->where('offer_name', $referenceTier->offer_name)
                      ->where('total_price', $referenceTier->total_price)
                      ->where('deposit_amount', $referenceTier->deposit_amount);
                });
            }
        }

        if ($this->selectedDepartureId) {
            $dep = TourDeparture::find($this->selectedDepartureId);
            if ($dep) {
                $query->whereHas('departures', function ($q) use ($dep) {
                    $this->applyDepartureCardConditions($q, $dep);
                });
            }
        }

        return $query;
    }

    #[Computed]
    public function availableDepartures()
    {
        $allDeps = TourDeparture::with('tour')
            ->where(function ($query) {
                $query->whereNull('tour_id')
                      ->orWhereHas('tour', fn($sub) => $sub->whereNull('deleted_at'));
            })->get();
            
        $uniqueDeps = collect();
        $seen = [];

        foreach ($allDeps as $dep) {
            if (!is_null($dep->departure_date)) {
                $timeOnly = Carbon::parse($dep->departure_time)->format('H:i:s');
                $targetDateTime = Carbon::parse(Carbon::parse($dep->departure_date)->toDateString() . ' ' . $timeOnly);
                if ($targetDateTime->isPast()) {
                    continue; 
                }
            }

            $timeSafe = Carbon::parse($dep->departure_time)->format('H:i:s');
            $returnSafe = $dep->return_time ? Carbon::parse($dep->return_time)->format('H:i:s') : 'none';

            $key = ($dep->departure_date ? Carbon::parse($dep->departure_date)->toDateString() : 'daily') . '-' . $timeSafe . '-' . $returnSafe;
            
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $uniqueDeps->push($dep);
            }
        }

        return $uniqueDeps
            ->when(!empty($this->departureDateFilter), fn ($collection) =>
                $collection->filter(fn ($dep) => is_null($dep->departure_date) || Carbon::parse($dep->departure_date)->toDateString() === $this->departureDateFilter)
            )
            ->when(!empty($this->departureTimeFilter), fn ($collection) =>
                $collection->filter(fn ($dep) => date('H:i', strtotime($dep->departure_time)) === date('H:i', strtotime($this->departureTimeFilter)))
            )
            ->sortBy(fn ($dep) => ($dep->departure_date ? Carbon::parse($dep->departure_date)->toDateString() : '0000-00-00') . ' ' . Carbon::parse($dep->departure_time)->format('H:i:s'))->values();
    }

    #[Computed]
    public function availablePricingTiers()
    {
        $rows = TourPricingTier::with('tour')
            ->where(function ($query) {
                $query->whereNull('tour_id')
                      ->orWhereHas('tour', fn($sub) => $sub->whereNull('deleted_at'));
            })
            ->when(trim($this->pricingSearch ?? '') !== '', function($q) {
                $search = trim($this->pricingSearch);
                $q->where(function($query) use ($search) {
                    $query->where('offer_name', 'LIKE', "%{$search}%")
                          ->orWhere('is_base', 1)
                          ->orWhere('is_base', true);
                });
            })
            ->when(($this->pricingVehicleFilter ?? 'all') !== 'all', function ($q) {
                $q->where('vehicle_type', $this->pricingVehicleFilter);
            })
            ->when(($this->pricingTripTypeFilter ?? 'all') !== 'all', function ($q) {
                $q->where('trip_type', $this->pricingTripTypeFilter);
            })
            ->when(($this->pricingTypeFilter ?? 'all') !== 'all', function ($q) {
                if ($this->pricingTypeFilter === 'base') {
                    $q->where(function($sub) {
                        $sub->where('is_base', 1)->orWhere('is_base', true);
                    });
                }
                if ($this->pricingTypeFilter === 'offer') {
                    $q->where(function($sub) {
                        $sub->where('is_base', 0)->orWhereNull('is_base')->orWhere('is_base', false);
                    });
                }
            })
            ->orderBy('is_base', 'desc')
            ->orderBy('pax_count', 'asc')
            ->get();

        return $rows
            ->groupBy(function (TourPricingTier $tier) {
                return implode('|', [
                    $tier->is_base ? 1 : 0,
                    $tier->vehicle_type,
                    $tier->trip_type,
                    $tier->pax_count,
                    $tier->offer_name,
                    $tier->total_price,
                    $tier->deposit_amount,
                ]);
            })
            ->map(function ($group) {
                $representative = $group->first();
                $representative->setRelation('cardTours', $group->pluck('tour')->filter()->unique('id')->values());
                $representative->setAttribute('card_tour_count', $group->count());
                return $representative;
            })
            ->values();
    }

    public function selectPricingOffer(int $id): void
    {
        if ($this->selectedPricingOfferId === $id) {
            $this->selectedPricingOfferId = null;
        } else {
            $this->selectedPricingOfferId = $id;
        }
        $this->resetPage();
    }

    public function selectDepartureCard(int $id): void
    {
        if ($this->selectedDepartureId === $id) {
            $this->selectedDepartureId = null;
        } else {
            $this->selectedDepartureId = $id;
        }
        $this->resetPage();
    }

    public function clearPricingFilter(): void
    {
        $this->selectedPricingOfferId = null;
        $this->resetPage();
    }

    public function clearPricingCardFilters(): void
    {
        $this->pricingSearch = null;
        $this->pricingTripTypeFilter = 'all';
        $this->pricingVehicleFilter = 'all';
        $this->pricingTypeFilter = 'all';
    }

    public function clearTimeFilter(): void
    {
        $this->selectedDepartureId = null;
        $this->departureDateFilter = null;
        $this->departureTimeFilter = null;
        $this->resetPage();
    }

    public function togglePricingActive(int $id): void
    {
        $tier = TourPricingTier::find($id);
        if ($tier) {
            $newState = !$tier->is_active;
            $this->cardQuery($tier)->update(['is_active' => $newState]);
            Notification::make()->title($newState ? __('admin.notifications.config_active') : __('admin.notifications.config_paused'))->success()->send();
        }
    }

    public function toggleDepartureActive(int $id): void
    {
        $dep = TourDeparture::find($id);
        if ($dep) {
            $newState = !$dep->is_active;
            $this->departureCardQuery($dep)->update(['is_active' => $newState]);
            Notification::make()->title($newState ? __('admin.notifications.time_active') : __('admin.notifications.time_paused'))->success()->send();
        }
    }

    public function deleteAllPricingAction(): Action
    {
        return Action::make('deleteAllPricing')
            ->requiresConfirmation()
            ->modalHeading(fn () => __('admin.actions.delete_all_pricing'))
            ->color('danger')
            ->action(function () {
                TourPricingTier::query()->delete(); 
                $this->selectedPricingOfferId = null;
                Notification::make()->title(__('admin.notifications.pricing_deleted'))->success()->send();
            });
    }

    public function deleteAllDeparturesAction(): Action
    {
        return Action::make('deleteAllDepartures')
            ->requiresConfirmation()
            ->modalHeading(fn () => __('admin.actions.delete_all_departures'))
            ->color('danger')
            ->action(function () {
                TourDeparture::query()->delete(); 
                $this->selectedDepartureId = null;
                Notification::make()->title(__('admin.notifications.departures_deleted'))->success()->send();
            });
    }

    public function editPricingOfferAction(): Action
    {
        return Action::make('editPricingOffer')
            ->modalHeading(fn () => __('admin.actions.edit_global_pricing'))
            ->modalWidth('4xl')
            ->form($this->getPricingFormSchema())
            ->fillForm(function (array $arguments): array {
                $tier = TourPricingTier::find($arguments['id']);
                if (!$tier) return [];

                return [
                    'is_base' => (bool) $tier->is_base,
                    'offer_name' => $tier->offer_name,
                    'vehicle_type' => $tier->vehicle_type,
                    'trip_type' => $tier->trip_type,
                    'pax_count' => $tier->pax_count,
                    'total_price' => $tier->total_price,
                    'deposit_amount' => $tier->deposit_amount,
                    'is_active' => $tier->is_active,
                    'tours' => $this->cardQuery($tier)
                        ->whereNotNull('tour_id')
                        ->whereHas('tour', fn($q) => $q->whereNull('deleted_at')) 
                        ->pluck('tour_id')
                        ->filter()
                        ->toArray(),
                ];
            })
            ->action(function (array $data, array $arguments) {
                $originalTier = TourPricingTier::find($arguments['id']);
                $existingIds = $this->cardQuery($originalTier)->pluck('id')->toArray();
                
                $hasBookings = \App\Models\Booking::whereIn('tour_pricing_tier_id', $existingIds)->exists();

                $tourIds = $data['tours'] ?? [];
                unset($data['tours']);

                if ($hasBookings) {
                    TourPricingTier::whereIn('id', $existingIds)->delete();
                    
                    $newTierId = null;
                    if (!empty($tourIds)) {
                        $tours = Tour::whereIn('id', $tourIds)->get();
                        foreach ($tours as $tour) {
                            $created = $tour->pricingTiers()->create($data);
                            if (!$newTierId) $newTierId = $created->id;
                        }
                    } else {
                        $created = TourPricingTier::create(array_merge($data, ['tour_id' => null]));
                        $newTierId = $created->id;
                    }

                    if ($this->selectedPricingOfferId === $arguments['id']) {
                        $this->selectedPricingOfferId = $newTierId;
                    }
                    Notification::make()->title(__('admin.notifications.price_updated_archived'))->success()->send();
                } else {
                    TourPricingTier::whereIn('id', $existingIds)->update([
                        'is_base' => $data['is_base'],
                        'offer_name' => $data['offer_name'],
                        'vehicle_type' => $data['vehicle_type'],
                        'trip_type' => $data['trip_type'],
                        'pax_count' => $data['pax_count'],
                        'total_price' => $data['total_price'],
                        'deposit_amount' => $data['deposit_amount'],
                        'is_active' => $data['is_active'],
                    ]);

                    $existingTourIds = $this->cardQuery($originalTier)->pluck('tour_id')->filter()->toArray();
                    $toRemove = array_diff($existingTourIds, $tourIds);
                    $toAdd = array_diff($tourIds, $existingTourIds);

                    if (!empty($toRemove)) {
                        TourPricingTier::whereIn('id', $existingIds)->whereIn('tour_id', $toRemove)->delete();
                    }
                    if (!empty($toAdd)) {
                        $tours = Tour::whereIn('id', $toAdd)->get();
                        foreach ($tours as $tour) {
                            $tour->pricingTiers()->create($data);
                        }
                    }
                    Notification::make()->title(__('admin.notifications.pricing_globally_updated'))->success()->send();
                }
            });
    }

    public function deletePricingOfferAction(): Action
    {
        return Action::make('deletePricingOffer')
            ->requiresConfirmation()
            ->modalHeading(fn () => __('admin.actions.delete_pricing_offer'))
            ->color('danger')
            ->action(function (array $arguments) {
                $tier = TourPricingTier::find($arguments['id']);
                if ($tier) {
                    $this->cardQuery($tier)->delete(); 
                    if ($this->selectedPricingOfferId === $arguments['id']) {
                        $this->selectedPricingOfferId = null;
                    }
                    Notification::make()->title(__('admin.notifications.configuration_deleted'))->success()->send();
                }
            });
    }

    public function editDepartureAction(): Action
    {
        return Action::make('editDeparture')
            ->modalHeading(fn () => __('admin.actions.edit_departure'))
            ->form($this->getDepartureFormSchema())
            ->fillForm(function (array $arguments): array {
                $dep = TourDeparture::find($arguments['id']);
                if (!$dep) return [];

                $query = $this->departureCardQuery($dep);

                return [
                    'departure_date' => $dep->departure_date,
                    'departure_time' => $dep->departure_time,
                    'return_time' => $dep->return_time,
                    'is_active' => (bool)$dep->is_active,
                    'tours' => $query->whereNotNull('tour_id')
                        ->whereHas('tour', fn($q) => $q->whereNull('deleted_at'))
                        ->pluck('tour_id')
                        ->filter()
                        ->toArray(),
                ];
            })
            ->action(function (array $data, array $arguments) {
                $originalDep = TourDeparture::find($arguments['id']);
                $query = $this->departureCardQuery($originalDep);
                $existingIds = $query->pluck('id')->toArray();
                
                $hasBookings = \App\Models\Booking::whereIn('tour_departure_id', $existingIds)->exists();

                $tourIds = $data['tours'] ?? [];
                unset($data['tours']);

                if ($hasBookings) {
                    TourDeparture::whereIn('id', $existingIds)->delete();

                    if (!empty($tourIds)) {
                        $tours = Tour::whereIn('id', $tourIds)->get();
                        foreach ($tours as $tour) {
                            $tour->departures()->create([
                                'departure_date' => $data['departure_date'] ?: null,
                                'departure_time' => $data['departure_time'],
                                'return_time' => $data['return_time'] ?? null,
                                'is_active' => $data['is_active']
                            ]);
                        }
                    } else {
                        TourDeparture::create([
                            'tour_id' => null,
                            'departure_date' => $data['departure_date'] ?: null,
                            'departure_time' => $data['departure_time'],
                            'return_time' => $data['return_time'] ?? null,
                            'is_active' => $data['is_active']
                        ]);
                    }
                    Notification::make()->title(__('admin.notifications.schedule_updated_archived'))->success()->send();
                } else {
                    TourDeparture::whereIn('id', $existingIds)->update([
                        'departure_date' => $data['departure_date'] ?: null,
                        'departure_time' => $data['departure_time'],
                        'return_time' => $data['return_time'] ?? null,
                        'is_active' => $data['is_active']
                    ]);

                    $existingTourIds = $this->departureCardQuery($originalDep)->pluck('tour_id')->filter()->toArray();
                    $toRemove = array_diff($existingTourIds, $tourIds);
                    $toAdd = array_diff($tourIds, $existingTourIds);

                    if (!empty($toRemove)) {
                        TourDeparture::whereIn('id', $existingIds)->whereIn('tour_id', $toRemove)->delete();
                    }
                    if (!empty($toAdd)) {
                        $tours = Tour::whereIn('id', $toAdd)->get();
                        foreach ($tours as $tour) {
                            $tour->departures()->create([
                                'departure_date' => $data['departure_date'] ?: null,
                                'departure_time' => $data['departure_time'],
                                'return_time' => $data['return_time'] ?? null,
                                'is_active' => $data['is_active']
                            ]);
                        }
                    }
                    Notification::make()->title(__('admin.notifications.departure_updated_globally'))->success()->send();
                }
            });
    }

    public function deleteDepartureAction(): Action
    {
        return Action::make('deleteDeparture')
            ->requiresConfirmation()
            ->modalHeading(fn () => __('admin.actions.delete_departure'))
            ->color('danger')
            ->action(function (array $arguments) {
                $originalDep = TourDeparture::find($arguments['id']);
                if ($originalDep) {
                    $this->departureCardQuery($originalDep)->delete(); 
                    if ($this->selectedDepartureId === $arguments['id']) {
                        $this->selectedDepartureId = null;
                    }
                    Notification::make()->title(__('admin.notifications.deleted_successfully'))->success()->send();
                }
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('assign_global_departure')
                ->label(fn () => __('admin.actions.assign_global_departure'))
                ->icon('heroicon-o-clock')
                ->color('info')
                ->modalHeading(fn () => __('admin.actions.create_assign_departure'))
                ->form($this->getDepartureFormSchema())
                ->action(function (array $data) {
                    if(!empty($data['tours'])) {
                        $tours = Tour::whereIn('id', $data['tours'])->get();
                        foreach ($tours as $tour) {
                            $tour->departures()->create([
                                'departure_date' => $data['departure_date'] ?: null,
                                'departure_time' => $data['departure_time'],
                                'return_time' => $data['return_time'] ?? null,
                                'is_active' => $data['is_active']
                            ]);
                        }
                    } else {
                        TourDeparture::create([
                            'tour_id' => null,
                            'departure_date' => $data['departure_date'] ?: null,
                            'departure_time' => $data['departure_time'],
                            'return_time' => $data['return_time'] ?? null,
                            'is_active' => $data['is_active']
                        ]);
                    }
                    Notification::make()->title(__('admin.notifications.departure_created'))->success()->send();
                }),

            Actions\Action::make('assign_global_pricing')
                ->label(fn () => __('admin.actions.assign_global_pricing'))
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->modalHeading(fn () => __('admin.actions.create_assign_pricing'))
                ->modalWidth('4xl')
                ->form($this->getPricingFormSchema())
                ->action(function (array $data) {
                    $tourIds = $data['tours'] ?? [];
                    unset($data['tours']);

                    if(!empty($tourIds)) {
                        $tours = Tour::whereIn('id', $tourIds)->get();
                        foreach ($tours as $tour) {
                            $tour->pricingTiers()->create($data);
                        }
                    } else {
                        TourPricingTier::create(array_merge($data, ['tour_id' => null]));
                    }
                    Notification::make()->title(__('admin.notifications.pricing_created'))->success()->send();
                }),

            Actions\CreateAction::make(),
        ];
    }

    private function getDepartureFormSchema(): array
    {
        return [
            Forms\Components\DatePicker::make('departure_date')
                ->label(fn () => __('admin.fields.specific_date'))
                ->helperText(fn () => __('admin.fields.specific_date_help')),
                
            Forms\Components\TimePicker::make('departure_time')
                ->label(fn () => __('admin.fields.departure_time'))
                ->required()
                ->displayFormat('h:i A') 
                ->helperText(fn () => __('admin.fields.departure_time_help')),

            Forms\Components\TimePicker::make('return_time')
                ->label(fn () => __('admin.fields.return_time'))
                ->displayFormat('h:i A') 
                ->helperText(fn () => __('admin.fields.return_time_help')),
                
            Forms\Components\Toggle::make('is_active')
                ->label(fn () => __('admin.fields.currently_active'))
                ->default(true),
                
            Forms\Components\CheckboxList::make('tours')
                ->label(fn () => __('admin.fields.assign_departures_tours'))
               
                ->options(fn () => Tour::where('is_active', true)->pluck('name_en', 'id')->toArray())
                ->searchable()
                ->bulkToggleable()
                ->columns(2)
                ->columnSpanFull()
        ];
    }

    private function getPricingFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('vehicle_type')
                    ->label(fn () => __('admin.fields.type'))
                    ->options([
                        'microbus' => __('admin.options.shared_microbus'), 
                        'private' => __('admin.options.private_car')
                    ])
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        if ($get('is_base')) { $set('pax_count', $state === 'private' ? 3 : 1); }
                    })->required(),

                Forms\Components\Select::make('trip_type')
                    ->label(fn () => __('admin.columns.trip_type'))
                    ->options([
                        'one_way' => __('admin.options.one_way'), 
                        'round_trip' => __('admin.options.round_trip')
                    ])
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        if ($get('is_base')) { 
                            $set('offer_name', $state === 'round_trip' ? __('admin.options.standard_round_trip') : __('admin.options.standard_one_way')); 
                        }
                    })->required(),

                Forms\Components\Toggle::make('is_base')
                    ->label(fn () => __('admin.fields.is_base_price'))
                    ->live()->columnSpanFull()
                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        if ($state) {
                            $trip = $get('trip_type') === 'round_trip' ? __('admin.options.standard_round_trip') : __('admin.options.standard_one_way');
                            $set('offer_name', $trip);
                            $set('pax_count', $get('vehicle_type') === 'private' ? 3 : 1);
                        }
                    }),

                Forms\Components\TextInput::make('offer_name')
                    ->label(fn () => __('admin.fields.offer_name'))
                    ->readOnly(fn (Get $get) => $get('is_base'))
                    ->dehydrated(true)
                    ->required(),

                Forms\Components\TextInput::make('pax_count')
                    ->label(fn () => __('admin.fields.pax_count'))
                    ->numeric()
                    ->minValue(1)
                    ->readOnly(fn (Get $get) => $get('is_base'))
                    ->dehydrated(true)
                    ->required(),

                Forms\Components\TextInput::make('deposit_amount')
                    ->label(fn () => __('admin.fields.deposit_amount'))
                    ->numeric()->prefix('$')->required(),
                    
                Forms\Components\TextInput::make('total_price')
                    ->label(fn () => __('admin.fields.total_price'))
                    ->numeric()->prefix('$')->required(),
                    
                Forms\Components\Toggle::make('is_active')
                    ->label(fn () => __('admin.fields.active'))
                    ->default(true)->columnSpanFull(),
                
                Forms\Components\CheckboxList::make('tours')
                    ->label(fn () => __('admin.fields.assign_pricing_tours'))
                    // INSTANT CRASH FIX: Safely retrieve active English names only.
                    ->options(fn () => Tour::where('is_active', true)->pluck('name_en', 'id')->toArray())
                    ->searchable()
                    ->bulkToggleable()
                    ->columns(3)
                    ->columnSpanFull(),
            ])
        ];
    }
}