<?php

namespace App\Filament\Resources\TourResource\Pages;

use App\Filament\Resources\TourResource;
use App\Models\Tour;
use App\Models\TourPricingTier;
use App\Models\TourDeparture;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use Filament\Actions\Action;

class ListTours extends ListRecords
{
    protected static string $resource = TourResource::class;
    protected static string $view = 'filament.pages.list-tours';

    /**
     *  livewire URL query parameters for filtering and searching
     */

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



    /**
     * CARD QUERY (Pricing)
     * 
     * Takes a single pricing row ($tier) and returns a query builder 
     * containing EVERY row in the database that has the exact same settings.
     * 
     * Why? If the admin clicks "Delete" on the $40 Private Car card, 
     * we use this query to delete all connected rows at once.
     */

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


    /**
     * APPLY DEPARTURE CONDITIONS (The Engine)
     * 
     * Takes an existing query and attaches the specific Time and Date 
     * filters needed to find matching departures.
     */

    private function applyDepartureCardConditions(Builder $query, TourDeparture $dep): Builder
    {
        $normalizedTime = \Carbon\Carbon::parse($dep->departure_time)->format('H:i:s');
        $query->whereTime('departure_time', $normalizedTime);

        if ($dep->departure_date) {
            $query->whereDate('departure_date', \Carbon\Carbon::parse($dep->departure_date)->toDateString());
        } else {
            $query->whereNull('departure_date');
        }

        return $query;
    }

    /**
     * DEPARTURE CARD QUERY (The Wrapper)
     * 
     * Takes a single departure row ($dep) and starts a fresh query, 
     * then passes it to the engine above to find all matching rows.
     * 
     * Why? Just like the Pricing Card Query, this allows us to "Pause" 
     * or "Delete" an entire departure time slot across all assigned tours.
     */

    private function departureCardQuery(TourDeparture $dep): Builder
    {
        return $this->applyDepartureCardConditions(TourDeparture::query(), $dep);
    }


    /**
     * GET TABLE QUERY (The Interactive Filter)
     * 
     * Overrides Filament's default table query. This allows us to intercept 
     * the list of tours before they are drawn on the screen and filter them
     * based on which Pricing or Departure card the admin clicked.
     */

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

    /************************************************************************
     * Calculate this data once, cache it in memory, and give it to the Blade file whenever it asks.
     ************************************************************************/

    /** (1)...
     * AVAILABLE DEPARTURES (The Time Card Generator)
     * 
     * This method fetches all departure times, groups identical times together,
     * applies the frontend filters, and passes the clean list to the view.
     */

    #[Computed]
    public function availableDepartures()
    {
        $allDeps = TourDeparture::query()->get();
        $uniqueDeps = collect();
        $seen = [];

        foreach ($allDeps as $dep) {
            $key = ($dep->departure_date ? \Carbon\Carbon::parse($dep->departure_date)->toDateString() : 'daily') . '-' . $dep->departure_time;
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $uniqueDeps->push($dep);
            }
        }

        return $uniqueDeps
            ->when(!empty($this->departureDateFilter), fn ($collection) =>
                $collection->filter(fn ($dep) => is_null($dep->departure_date) || \Carbon\Carbon::parse($dep->departure_date)->toDateString() === $this->departureDateFilter)
            )
            ->when(!empty($this->departureTimeFilter), fn ($collection) =>
                $collection->filter(fn ($dep) => date('H:i', strtotime($dep->departure_time)) === date('H:i', strtotime($this->departureTimeFilter)))
            )
            ->sortBy(fn ($dep) => ($dep->departure_date ? \Carbon\Carbon::parse($dep->departure_date)->toDateString() : '0000-00-00') . ' ' . $dep->departure_time)->values();
    }

  
    /** (2)...
     * 
     * AVAILABLE PRICING TIERS (The Price Card Generator)
     * 
     * This method runs a highly filtered database query to get pricing rows, 
     * groups identical prices together into a single "Card", and attaches 
     * the assigned tours to it so they display neatly on the dashboard.
     */

    #[Computed]
    public function availablePricingTiers()
    {
        $rows = TourPricingTier::with('tour')
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
                /** @var TourPricingTier $representative */
                $representative = $group->first();
                $representative->setRelation('cardTours', $group->pluck('tour')->filter()->values());
                $representative->setAttribute('card_tour_count', $group->count());
                return $representative;
            })
            ->values();
    }

  



    // =========================================================================
    // 1. STATE & FILTER MANAGEMENT (Clicking Cards)
    // =========================================================================

    /**
     * Toggles the active Pricing Card. If they click the same card twice, it deselects it.
     * $this->resetPage() ensures the table jumps back to page 1 of the new filtered results.
     */

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


    // Methods to quickly clear out the search bars and dropdown filters

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

    // =========================================================================
    // 2. QUICK TOGGLES (Bulk Pause/Activate)
    // =========================================================================

    /**
     * When the admin clicks the toggle switch on a Price Card, it finds ONE row ($tier),
     * determines the opposite status ($newState), and then uses cardQuery() to 
     * instantly update ALL connected rows in the database to match.
     */

    public function togglePricingActive(int $id): void
    {
        $tier = TourPricingTier::find($id);
        if ($tier) {
            $newState = !$tier->is_active;
            $this->cardQuery($tier)->update(['is_active' => $newState]);

            Notification::make()->title($newState ? 'Configuration Active' : 'Configuration Paused')->success()->send();
        }
    }

    public function toggleDepartureActive(int $id): void
    {
        $dep = TourDeparture::find($id);
        if ($dep) {
            $newState = !$dep->is_active;

            $this->departureCardQuery($dep)->update(['is_active' => $newState]);
            Notification::make()->title($newState ? 'Time is now Active' : 'Time Paused')->success()->send();
        }
    }

    // =========================================================================
    // 3. DANGER ZONE (Bulk Deletions)
    // =========================================================================

    /**
     * Nuclear option: Wipes the entire pricing table. 
     * Foreign key checks are temporarily disabled to prevent database lockups.
     */

    public function deleteAllPricingAction(): Action
    {
        return Action::make('deleteAllPricing')
            ->requiresConfirmation()
            ->modalHeading('Delete All Pricing Combinations')
            ->modalDescription('Are you sure? This action cannot be undone.')
            ->color('danger')
            ->action(function () {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                TourPricingTier::truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                $this->selectedPricingOfferId = null;
                Notification::make()->title('All pricing configurations deleted')->success()->send();
            });
    }

    public function deleteAllDeparturesAction(): Action
    {
        return Action::make('deleteAllDepartures')
            ->requiresConfirmation()
            ->modalHeading('Delete All Departures')
            ->modalDescription('Are you sure? This action cannot be undone.')
            ->color('danger')
            ->action(function () {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                TourDeparture::truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                $this->selectedDepartureId = null;
                Notification::make()->title('All departure times deleted')->success()->send();
            });
    }


    // =========================================================================
    // 4. CRUD MODALS (Edit & Delete Specific Cards)
    // =========================================================================

    public function editPricingOfferAction(): Action
    {
        return Action::make('editPricingOffer')
            ->modalHeading('Edit Global Pricing Configuration')
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
                    'tours' => $this->cardQuery($tier)->pluck('tour_id')->filter()->toArray(),
                ];
            })
            ->action(function (array $data, array $arguments) {
                $originalTier = TourPricingTier::find($arguments['id']);

                $this->cardQuery($originalTier)->delete();

                $tourIds = $data['tours'] ?? [];
                unset($data['tours']);

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

                Notification::make()->title('Configuration updated globally')->success()->send();
            });
    }

    public function deletePricingOfferAction(): Action
    {
        return Action::make('deletePricingOffer')
            ->requiresConfirmation()
            ->color('danger')
            ->action(function (array $arguments) {
                $tier = TourPricingTier::find($arguments['id']);
                if ($tier) {
                    $this->cardQuery($tier)->delete();
                }

                if ($this->selectedPricingOfferId === $arguments['id']) {
                    $this->selectedPricingOfferId = null;
                }
                Notification::make()->title('Configuration deleted')->success()->send();
            });
    }

    public function editDepartureAction(): Action
    {
        return Action::make('editDeparture')
            ->modalHeading('Edit Departure Time')
            ->form($this->getDepartureFormSchema())
            ->fillForm(function (array $arguments): array {
                $dep = TourDeparture::find($arguments['id']);
                if (!$dep) return [];

                $query = TourDeparture::where('departure_time', $dep->departure_time);
                if ($dep->departure_date) $query->whereDate('departure_date', $dep->departure_date);
                else $query->whereNull('departure_date');

                return [
                    'departure_date' => $dep->departure_date,
                    'departure_time' => $dep->departure_time,
                    'is_active' => (bool)$dep->is_active,
                    'tours' => $query->pluck('tour_id')->filter()->toArray(),
                ];
            })
            ->action(function (array $data, array $arguments) {
                $originalDep = TourDeparture::find($arguments['id']);

                $query = TourDeparture::where('departure_time', $originalDep->departure_time);
                if ($originalDep->departure_date) $query->whereDate('departure_date', $originalDep->departure_date);
                else $query->whereNull('departure_date');
                $query->delete();

                if (!empty($data['tours'])) {
                    $tours = Tour::whereIn('id', $data['tours'])->get();
                    foreach ($tours as $tour) {
                        $tour->departures()->create([
                            'departure_date' => $data['departure_date'] ?: null,
                            'departure_time' => $data['departure_time'],
                            'is_active' => $data['is_active']
                        ]);
                    }
                } else {
                    TourDeparture::create([
                        'tour_id' => null,
                        'departure_date' => $data['departure_date'] ?: null,
                        'departure_time' => $data['departure_time'],
                        'is_active' => $data['is_active']
                    ]);
                }

                $this->selectedDepartureId = null;
                Notification::make()->title('Departure updated successfully')->success()->send();
            });
    }

    public function deleteDepartureAction(): Action
    {
        return Action::make('deleteDeparture')
            ->requiresConfirmation()
            ->color('danger')
            ->action(function (array $arguments) {
                $originalDep = TourDeparture::find($arguments['id']);
                if ($originalDep) {
                    $query = TourDeparture::where('departure_time', $originalDep->departure_time);
                    if ($originalDep->departure_date) $query->whereDate('departure_date', $originalDep->departure_date);
                    else $query->whereNull('departure_date');
                    $query->delete();
                }

                if ($this->selectedDepartureId === $arguments['id']) {
                    $this->selectedDepartureId = null;
                }
                Notification::make()->title('Departure time deleted')->success()->send();
            });
    }

    // =========================================================================
    // 5. HEADER ACTIONS (Top Right Buttons)
    // =========================================================================

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('assign_global_departure')
                ->label('Assign Departures to Tours')
                ->icon('heroicon-o-clock')
                ->color('info')
                ->modalHeading('Create & Assign Departure Time')
                ->form($this->getDepartureFormSchema())
                ->action(function (array $data) {
                    if(!empty($data['tours'])) {
                        $tours = Tour::whereIn('id', $data['tours'])->get();
                        foreach ($tours as $tour) {
                            $tour->departures()->create([
                                'departure_date' => $data['departure_date'] ?: null,
                                'departure_time' => $data['departure_time'],
                                'is_active' => $data['is_active']
                            ]);
                        }
                    } else {
                        TourDeparture::create([
                            'tour_id' => null,
                            'departure_date' => $data['departure_date'] ?: null,
                            'departure_time' => $data['departure_time'],
                            'is_active' => $data['is_active']
                        ]);
                    }
                    Notification::make()->title('Departure created successfully!')->success()->send();
                }),

            Actions\Action::make('assign_global_pricing')
                ->label('Assign Pricing to Tours')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->modalHeading('Create & Assign Pricing Configuration')
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
                    Notification::make()->title('Pricing created successfully!')->success()->send();
                }),

            Actions\CreateAction::make(),
        ];
    }

    // =========================================================================
    // REUSABLE FORM SCHEMAS
    // =========================================================================

    /**
     * Defines the reusable form schema for Departure Times.
     */
    private function getDepartureFormSchema(): array
    {
        return [
            Forms\Components\DatePicker::make('departure_date')
                ->label('Specific Date')
                ->helperText('Leave this completely blank to make the tour run every single day.'),
                
            Forms\Components\TimePicker::make('departure_time')
                ->required()
                ->displayFormat('H:i')
                ->helperText('When does the transport leave?'),
                
            Forms\Components\Toggle::make('is_active')
                ->label('Currently Active')
                ->default(true),
                
            Forms\Components\CheckboxList::make('tours')
                ->label('Select the Tours that depart at this time')
                ->options(fn () => Tour::pluck('name_en', 'id'))
                ->searchable()
                ->bulkToggleable()
                ->columns(2)
                ->columnSpanFull()
        ];
    }

    /**
     * Defines the reusable form schema for Pricing Tiers.
     */
    private function getPricingFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('vehicle_type')
                    ->label('Transport Type')
                    ->options(['microbus' => 'Shared Microbus', 'private' => 'Private Car'])
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                        if ($get('is_base')) { $set('pax_count', $state === 'private' ? 3 : 1); }
                    })->required(),

                Forms\Components\Select::make('trip_type')
                    ->options(['one_way' => 'One Way', 'round_trip' => 'Round Trip'])
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                        if ($get('is_base')) { $set('offer_name', $state === 'round_trip' ? 'Standard Round Trip' : 'Standard One Way'); }
                    })->required(),

                Forms\Components\Toggle::make('is_base')->label('Is Base Price?')->live()->columnSpanFull()
                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                        if ($state) {
                            $trip = $get('trip_type') === 'round_trip' ? 'Round Trip' : 'One Way';
                            $set('offer_name', 'Standard ' . $trip);
                            $set('pax_count', $get('vehicle_type') === 'private' ? 3 : 1);
                        }
                    }),

                Forms\Components\TextInput::make('offer_name')
                    ->label('Offer / Base Name')
                    ->readOnly(fn (Forms\Get $get) => $get('is_base'))
                    ->dehydrated(true)
                    ->required(),

                Forms\Components\TextInput::make('pax_count')
                    ->label('Number of Persons')
                    ->numeric()
                    ->minValue(1)
                    ->readOnly(fn (Forms\Get $get) => $get('is_base'))
                    ->dehydrated(true)
                    ->required(),

                Forms\Components\TextInput::make('deposit_amount')->numeric()->prefix('$')->required(),
                Forms\Components\TextInput::make('total_price')->numeric()->prefix('$')->required(),
                Forms\Components\Toggle::make('is_active')->default(true)->columnSpanFull(),
                
                Forms\Components\CheckboxList::make('tours')
                    ->label('Assign this pricing to the following tours:')
                    ->options(fn () => Tour::pluck('name_en', 'id'))
                    ->searchable()
                    ->bulkToggleable()
                    ->columns(3)
                    ->columnSpanFull(),
            ])
        ];
    }
}