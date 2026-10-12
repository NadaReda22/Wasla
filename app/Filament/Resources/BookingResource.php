<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\Tour;
use App\Models\TourDeparture;
use App\Models\Area;
use App\Services\PricingCalculatorService;
use App\Services\Messaging\BookingWhatsAppMessage;
use App\Services\Manifest\DriverManifestBuilder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotificationAction;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;
use Livewire\Component;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.finance');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.bookings');
    }

    public static function getModelLabel(): string
    {
        return __('admin.models.booking');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.bookings');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'client',
                'tour',
                'area',
                'outboundVehicle',
                'returnVehicle',
                'departure',
                'pricingTier',
            ]);
    }

   
    private static function paymentStatuses(): array
    {
        return [
            'pending' => __('admin.booking.payment_statuses.pending'),
            'paid'    => __('admin.booking.payment_statuses.paid'),
            'failed'  => __('admin.booking.payment_statuses.failed'),
        ];
    }

    private static function whatsappStatuses(): array
    {
        return [
            'pending' => __('admin.booking.whatsapp_statuses.pending'),
            'sent'    => __('admin.booking.whatsapp_statuses.sent'),
            'failed'  => __('admin.booking.whatsapp_statuses.failed'),
        ];
    }

    private static function tripTypes(): array
    {
        return [
            'one_way'    => __('admin.options.one_way'),
            'round_trip' => __('admin.options.round_trip'),
        ];
    }

    private static function recalculatePricing(Forms\Set $set, Forms\Get $get, string $operation = 'create'): void
    {
        $pricing = app(PricingCalculatorService::class)->calculate(
            $get('tour_id') ? (int) $get('tour_id') : null,
            $get('trip_type'),
            $get('pricing_vehicle_type'),
            (int) $get('seats_count')
        );

        $total = $pricing['total_price'];
        $deposit = $operation === 'create' ? 0 : (float) $get('deposit_paid');

        $set('tour_pricing_tier_id', $pricing['tier_id']);
        $set('total_price', $total);
        $set('deposit_paid', $deposit);
        $set('cash_due_on_board', max(0, $total - $deposit));
    }

    private static function clientFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('full_name')->label(fn () => __('admin.booking.full_name'))->required(),
            Forms\Components\TextInput::make('whatsapp_number')->label(fn () => __('admin.booking.whatsapp_number'))->required(),
            Forms\Components\TextInput::make('email')->label(fn () => __('admin.booking.email_address'))->email(),
            Forms\Components\Select::make('nationality')
                ->label(fn () => __('admin.columns.nationality'))
                ->searchable()
                ->options(config('countries'))
                ->required(),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(fn () => __('admin.booking.client_details'))
                    ->schema([
                        Forms\Components\Placeholder::make('client_name')
                            ->label(fn () => __('admin.booking.full_name'))
                            ->content(fn (?Booking $record) => $record?->client?->full_name ?? '-'),

                        Forms\Components\Placeholder::make('client_phone')
                            ->label(fn () => __('admin.booking.whatsapp_number'))
                            ->content(fn (?Booking $record) => $record?->client?->whatsapp_number ?? '-'),

                        Forms\Components\Placeholder::make('client_email')
                            ->label(fn () => __('admin.booking.email_address'))
                            ->content(fn (?Booking $record) => $record?->client?->email ?? __('admin.booking.no_email')),

                        Forms\Components\Placeholder::make('client_nationality')
                            ->label(fn () => __('admin.columns.nationality'))
                            ->content(fn (?Booking $record) => $record?->client?->nationality ?? __('admin.options.unknown')),
                    ])
                    ->columns(4)
                    ->hiddenOn(['create', 'edit']),

                Forms\Components\Section::make(fn () => __('admin.sections.booking_overview'))->schema([

                    Forms\Components\TextInput::make('booking_reference')
                        ->label(fn () => __('admin.columns.ref'))
                        ->disabled()
                        ->dehydrated(false)
                        ->hiddenOn('create'),

                    Forms\Components\Select::make('client_id')
                        ->label(fn () => __('admin.booking.client_search'))
                        ->relationship('client', 'whatsapp_number')
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->whatsapp_number} - {$record->full_name}")
                        ->searchable(['whatsapp_number', 'full_name'])
                        ->preload()
                        ->required()
                        ->createOptionForm(self::clientFormSchema())
                        ->createOptionUsing(fn (array $data) => Client::upsertByContact($data)->getKey())
                        ->editOptionForm(self::clientFormSchema()),

                    Forms\Components\Select::make('tour_id')
                        ->label(fn () => __('admin.booking.tour'))
                        ->relationship('tour', 'name_en')
                        ->options(fn () => Tour::where('is_active', true)->pluck('name_en', 'id'))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, string $operation) {
                            $set('booking_date', null);
                            $set('tour_departure_id', null);
                            self::recalculatePricing($set, $get, $operation);
                        })
                        ->required(),

                    Forms\Components\Select::make('booking_date')
                        ->label(fn () => __('admin.booking.tour_date'))
                        ->options(function (Forms\Get $get) {
                            $tour = Tour::with('departures')->find($get('tour_id'));
                            return $tour ? $tour->getAvailableDates() : [];
                        })
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set) => $set('tour_departure_id', null))
                        ->required(),

                    Forms\Components\Select::make('tour_departure_id')
                        ->label(fn () => __('admin.fields.departure_time'))
                        ->options(function (Forms\Get $get, ?Booking $record) {
                            $tourId = $get('tour_id');
                            if (! $tourId) {
                                return [];
                            }

                            return TourDeparture::withTrashed()
                                ->where('tour_id', $tourId)
                                ->where(fn ($q) => $q
                                    ->where(fn ($active) => $active->where('is_active', true)->whereNull('deleted_at'))
                                    ->when($record?->tour_departure_id, fn ($q, $id) => $q->orWhere('id', $id)))
                                ->orderBy('departure_time')
                                ->get()
                                ->mapWithKeys(fn ($dep) => [
                                    $dep->id => Carbon::parse($dep->departure_time)->format('h:i A')
                                        . ($dep->return_time ? ' (' . __('admin.list_tours.ret') . ' ' . Carbon::parse($dep->return_time)->format('h:i A') . ')' : ''),
                                ]);
                        })
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Hidden::make('tour_pricing_tier_id')->required(),

                    Forms\Components\Select::make('pricing_vehicle_type')
                        ->label(fn () => __('admin.booking.vehicle_for_pricing'))
                        ->options([
                            'microbus' => __('admin.options.shared_microbus'),
                            'private' => __('admin.options.private_car'),
                        ])
                        ->dehydrated(false)
                        ->formatStateUsing(fn (?Booking $record) => $record?->pricingTier?->vehicle_type ?? 'microbus')
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get, string $operation) => self::recalculatePricing($set, $get, $operation))
                        ->required(),

                    Forms\Components\Select::make('trip_type')
                        ->label(fn () => __('admin.columns.trip_type'))
                        ->options(self::tripTypes())
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get, string $operation) => self::recalculatePricing($set, $get, $operation))
                        ->required(),

                    Forms\Components\TextInput::make('seats_count')
                        ->label(fn () => __('admin.booking.seats_count'))
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->live(debounce: 500)
                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get, string $operation) => self::recalculatePricing($set, $get, $operation))
                        ->required(),
                ])->columns(4),

                Forms\Components\Section::make(fn () => __('admin.sections.location_dispatch'))->schema([

                    Forms\Components\Select::make('area_id')
                        ->label(fn () => __('admin.columns.zone'))
                        ->options(fn () => Area::pluck('name_en', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\TextInput::make('pickup_name')->label(fn () => __('admin.booking.hotel_location'))->required(),
                    Forms\Components\TextInput::make('room_number')->label(fn () => __('admin.booking.room_number'))->required(),
                    Forms\Components\TextInput::make('reception_number')->label(fn () => __('admin.booking.reception_number'))->required(),
                    Forms\Components\TextInput::make('pickup_address')->label(fn () => __('admin.booking.pickup_address'))->columnSpanFull(),

                    Forms\Components\Placeholder::make('google_maps_url')
                        ->label(fn () => __('admin.booking.google_maps_location'))
                        ->columnSpanFull()
                        ->hiddenOn('create')
                        ->content(function (?Booking $record) {
                            if ($record && $record->pickup_lat && $record->pickup_lng) {
                                $url = "https://www.google.com/maps/search/?api=1&query={$record->pickup_lat},{$record->pickup_lng}";
                                $label = e(__('admin.booking.open_in_maps'));
                                return new HtmlString("<a href='{$url}' target='_blank' class='text-primary-600 font-bold hover:underline'>{$label}</a>");
                            }
                            return __('admin.booking.no_map_coordinates');
                        }),

                ])->columns(4),

                Forms\Components\Section::make(fn () => __('admin.sections.financials'))->schema([
                    Forms\Components\TextInput::make('total_price')
                        ->label(fn () => __('admin.fields.total_price'))
                        ->numeric()
                        ->prefix('$')
                        ->required(),

                    Forms\Components\TextInput::make('deposit_paid')
                        ->label(fn () => __('admin.booking.deposit_paid'))
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                            $total = (float) $get('total_price');
                            $deposit = (float) $state;
                            $set('cash_due_on_board', max(0, $total - $deposit));
                        })
                        ->required(),

                    Forms\Components\TextInput::make('cash_due_on_board')
                        ->label(fn () => __('admin.booking.cash_due_on_board'))
                        ->numeric()
                        ->prefix('$')
                        ->required(),

                    Forms\Components\Select::make('payment_status')
                        ->label(fn () => __('admin.booking.payment_status'))
                        ->options(self::paymentStatuses())
                        ->default('pending')
                        ->required(),

                    Forms\Components\Toggle::make('is_cash_on_board_paid')
                        ->label(fn () => __('admin.columns.cash_paid'))
                        ->default(false),
                ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('booking_reference')
                    ->label(fn () => __('admin.columns.ref'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('booking_date')
                    ->label(fn () => __('admin.booking.tour_date'))
                    ->date('M d, Y')
                    ->description(function (Booking $record) {
                        $out = $record->departure?->departure_time ? Carbon::parse($record->departure->departure_time)->format('g:i A') : '09:00 AM';
                        $ret = $record->departure?->return_time ? ' | ' . __('admin.list_tours.ret') . ' ' . Carbon::parse($record->departure->return_time)->format('g:i A') : '';
                        return __('admin.list_tours.out') . " {$out}{$ret}";
                    })
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('locks_status')
                    ->label(fn () => __('admin.columns.ensured'))
                    ->getStateUsing(function (Booking $record) {

                        $ensuredTitle = e(__('admin.booking.leg_ensured'));
                        $waitingTitle = e(__('admin.booking.leg_waiting'));
                        $outboundTitle = e(__('admin.distribution.outbound_short'));
                        $returnTitle = e(__('admin.distribution.return_short'));

                        $check = '<div style="color: #16a34a; margin-top: 4px;" title="' . $ensuredTitle . '">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 20px; height: 20px;"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" /></svg>
                                  </div>';

                        $wait = '<div style="color: #d1d5db; margin-top: 4px;" title="' . $waitingTitle . '">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 20px; height: 20px;"><path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" /></svg>
                                 </div>';

                        $outStatus = $record->is_outbound_ensured ? $check : $wait;
                        $outColor  = $record->is_outbound_ensured ? '#2563eb' : '#d1d5db'; // أزرق في حالة التأكيد، رمادي للانتظار
                        $outArrow  = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" style="width: 24px; height: 24px; color: ' . $outColor . ';"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>';

                        $outIcon = '<div style="display: flex; flex-direction: column; align-items: center; justify-content: center;" title="' . $outboundTitle . '">
                                        ' . $outArrow . '
                                        ' . $outStatus . '
                                    </div>';

                        if ($record->trip_type === 'one_way') {
                            return new \Illuminate\Support\HtmlString('<div style="display: flex; align-items: center; justify-content: center;">' . $outIcon . '</div>');
                        }

                        $retStatus = $record->is_return_ensured ? $check : $wait;
                        $retColor  = $record->is_return_ensured ? '#f97316' : '#d1d5db'; // برتقالي في حالة التأكيد
                        $retArrow  = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" style="width: 24px; height: 24px; color: ' . $retColor . ';"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>';

                        $retIcon = '<div style="display: flex; flex-direction: column; align-items: center; justify-content: center;" title="' . $returnTitle . '">
                                        ' . $retArrow . '
                                        ' . $retStatus . '
                                    </div>';

                        return new \Illuminate\Support\HtmlString('<div style="display: flex; align-items: center; gap: 16px;">' . $outIcon . $retIcon . '</div>');
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('client.full_name')
                    ->label(fn () => __('admin.columns.client'))
                    ->searchable()
                    ->description(fn (Booking $record): string => $record->client?->whatsapp_number ?? __('admin.options.no_phone'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('tour.display_name')
                    ->label(fn () => __('admin.columns.tour_vehicle'))
                    ->description(function (Booking $record) {
                        $private = __('admin.distribution.private');
                        $shared  = __('admin.distribution.shared');

                        $outIsPrivate = $record->outboundVehicle?->type === 'private_car';
                        $outType = $outIsPrivate ? $private : $shared;
                        $outIcon = $outIsPrivate ? '🚗' : '🚐';

                        if ($record->trip_type === 'round_trip' && $record->return_vehicle_id) {
                            $retIsPrivate = $record->returnVehicle?->type === 'private_car';
                            if ($outIsPrivate !== $retIsPrivate) {
                                $retType = $retIsPrivate ? $private : $shared;
                                $retIcon = $retIsPrivate ? '🚗' : '🚐';
                                return __('admin.list_tours.out') . " {$outIcon} {$outType} / " . __('admin.list_tours.ret') . " {$retIcon} {$retType}";
                            }
                        }
                        return "{$outIcon} {$outType}";
                    })
                    ->searchable(query: fn (Builder $query, string $search) => $query->whereHas(
                        'tour',
                        fn (Builder $q) => $q->where('name_en', 'like', "%{$search}%")
                            ->orWhere('name_ru', 'like', "%{$search}%")
                    ))
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy(
                        Tour::withTrashed()->select('name_en')->whereColumn('tours.id', 'bookings.tour_id'),
                        $direction
                    ))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('pickup_name')
                    ->label(fn () => __('admin.columns.location'))
                    ->description(fn (Booking $record): string => $record->area ? "📍 " . (app()->getLocale() === 'ar' ? ($record->area->name_ar ?? $record->area->name_en) : $record->area->name_en) : __('admin.booking.no_zone'))
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('seats_count')
                    ->label(fn () => __('admin.columns.pax'))
                    ->numeric()
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label(fn () => __('admin.booking.payment'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => self::paymentStatuses()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('whatsapp_status')
                    ->label(fn () => __('admin.columns.whatsapp'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => self::whatsappStatuses()[$state] ?? $state)
                    ->color(fn (?string $state): string => match ($state) {
                        'sent' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\ToggleColumn::make('is_cash_on_board_paid')
                    ->label(fn () => __('admin.columns.cash_paid'))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('outboundVehicle.plate_number')
                    ->label(fn () => __('admin.columns.dispatch'))
                    ->getStateUsing(fn (Booking $record) => $record->outboundVehicle?->plate_number ?? __('admin.options.unassigned'))
                    ->formatStateUsing(fn (string $state) => "🛫 {$state}")
                    ->description(function (Booking $record) {
                        if ($record->return_vehicle_id) {
                            return "🛬 {$record->returnVehicle->plate_number}";
                        }
                        return $record->trip_type === 'round_trip' ? "🛬 " . __('admin.options.unassigned') : null;
                    })
                    ->badge()
                    ->color(fn (Booking $record) => $record->outbound_vehicle_id ? 'success' : 'danger')
                    ->toggleable(),

                Tables\Columns\ViewColumn::make('driver_whatsapp')
                    ->label(fn () => __('admin.booking.message_driver'))
                    ->view('filament.tables.columns.driver-whatsapp')
                    ->disabledClick()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('advanced_schedule')
                    ->columnSpan(4)
                    ->form([
                        Forms\Components\Fieldset::make(fn () => __('admin.sections.schedule_search'))->schema([

                            Forms\Components\DatePicker::make('booking_date')
                                ->live()
                                ->label(fn () => __('admin.booking.specific_tour_date'))
                                ->afterStateUpdated(function (Forms\Set $set) {
                                    $set('tour_id', null);
                                    $set('departure_time', null);
                                    $set('driver_id', null);
                                }),

                            Forms\Components\Select::make('tour_id')
                                ->label(fn () => __('admin.booking.tour'))
                                ->options(function (Forms\Get $get) {
                                    $date = $get('booking_date');
                                    $q = Tour::query();

                                    if ($date) {
                                        $q->whereHas('bookings', fn($b) => $b->where('booking_date', $date));
                                    }

                                    return $q->pluck('name_en', 'id');
                                })
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function (Forms\Set $set) {
                                    $set('departure_time', null);
                                    $set('driver_id', null);
                                }),

                            Forms\Components\Select::make('departure_time')
                                ->label(fn () => __('admin.fields.departure_time'))
                                ->options(function (Forms\Get $get) {
                                    $date = $get('booking_date');
                                    $tourId = $get('tour_id');

                                    $q = TourDeparture::query();

                                    if ($tourId) $q->where('tour_id', $tourId);
                                    if ($date) {
                                        $q->whereHas('bookings', fn($b) => $b->where('booking_date', $date));
                                    }

                                    return $q->get()
                                        ->map(fn($dep) => $dep->departure_time ? Carbon::parse($dep->departure_time)->format('H:i') : null)
                                        ->filter()
                                        ->unique()
                                        ->mapWithKeys(fn($time) => [$time => Carbon::parse($time)->format('g:i A')])
                                        ->all();
                                })
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function (Forms\Set $set) {
                                    $set('driver_id', null);
                                }),

                            Forms\Components\Select::make('driver_id')
                                ->label(fn () => __('admin.columns.driver_name'))
                                ->options(function (Forms\Get $get) {
                                    $date = $get('booking_date');
                                    $tourId = $get('tour_id');
                                    $time = $get('departure_time');

                                    if (!$date && !$tourId && !$time) {
                                        return Vehicle::whereNotNull('driver_name')
                                            ->get()
                                            ->mapWithKeys(fn ($v) => [$v->id => "{$v->driver_name} ({$v->plate_number})"]);
                                    }

                                    $bQuery = Booking::query();
                                    if ($date) $bQuery->where('booking_date', $date);
                                    if ($tourId) $bQuery->where('tour_id', $tourId);
                                    if ($time) {
                                        $bQuery->whereHas('departure', fn($q) => $q->where('departure_time', 'LIKE', $time . '%'));
                                    }

                                    $vehicleIds = $bQuery->get()
                                        ->flatMap(fn($b) => [$b->outbound_vehicle_id, $b->return_vehicle_id])
                                        ->filter()
                                        ->unique();

                                    return Vehicle::whereIn('id', $vehicleIds)
                                        ->whereNotNull('driver_name')
                                        ->get()
                                        ->mapWithKeys(fn ($v) => [$v->id => "{$v->driver_name} ({$v->plate_number})"]);
                                })
                                ->searchable()
                                ->preload()
                                ->live(),
                        ])->columns(4),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['booking_date'] ?? null, fn (Builder $q, $date) => $q->where('booking_date', $date))
                            ->when($data['tour_id'] ?? null, fn (Builder $q, $tour) => $q->where('tour_id', $tour))
                            ->when($data['departure_time'] ?? null, fn (Builder $q, $time) => $q->whereHas(
                                'departure',
                                fn (Builder $sub) => $sub->where('departure_time', 'LIKE', $time . '%')
                            ))
                            ->when($data['driver_id'] ?? null, fn (Builder $q, $driver) => $q->where(
                                fn ($sub) => $sub->where('outbound_vehicle_id', $driver)->orWhere('return_vehicle_id', $driver)
                            ));
                    }),

                Tables\Filters\SelectFilter::make('area_id')
                    ->label(fn () => __('admin.columns.zone'))
                    ->options(fn () => Area::pluck('name_en', 'id'))
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('trip_type')
                    ->label(fn () => __('admin.columns.trip_type'))
                    ->options(self::tripTypes()),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label(fn () => __('admin.booking.payment_status'))
                    ->options(self::paymentStatuses()),

                Tables\Filters\SelectFilter::make('whatsapp_status')
                    ->label(fn () => __('admin.booking.whatsapp_status'))
                    ->options(self::whatsappStatuses()),

                Tables\Filters\TernaryFilter::make('is_outbound_ensured')
                    ->label(fn () => '🛫 ' . __('admin.booking.distribution_ensured') . ' (' . __('admin.distribution.outbound_short') . ')')
                    ->trueLabel(__('admin.booking.ensured_locked'))
                    ->falseLabel(__('admin.booking.not_ensured')),

                Tables\Filters\TernaryFilter::make('is_return_ensured')
                    ->label(fn () => '🛬 ' . __('admin.booking.distribution_ensured') . ' (' . __('admin.distribution.return_short') . ')')
                    ->trueLabel(__('admin.booking.ensured_locked'))
                    ->falseLabel(__('admin.booking.not_ensured')),

            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),

                    Tables\Actions\Action::make('whatsapp_client')
                        ->label(fn () => __('admin.actions.message_client'))
                        ->icon('heroicon-o-device-phone-mobile')
                        ->color('info')
                        ->visible(fn (Booking $record) => filled($record->client?->whatsapp_number))
                        ->url(fn (Booking $record) => BookingWhatsAppMessage::forClient($record))
                        ->openUrlInNewTab(),
                ])->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('whatsapp_driver_manifest_bulk')
                        ->label(fn () => __('admin.actions.send_manifest'))
                        ->icon('heroicon-s-paper-airplane')
                        ->color('primary')
                        ->action(function (Collection $records, Component $livewire) {
                            $filters = $livewire->getTableFilterState('advanced_schedule') ?? [];

                            if (empty($filters['booking_date']) || empty($filters['driver_id'])) {
                                Notification::make()->danger()
                                    ->title(__('admin.booking.notifications.action_forbidden'))
                                    ->body(__('admin.booking.notifications.manifest_filters_required'))
                                    ->send();
                                return;
                            }

                            $driverVehicle = Vehicle::find((int) $filters['driver_id']);

                            if (! $driverVehicle || blank($driverVehicle->driver_phone)) {
                                Notification::make()->danger()
                                    ->title(__('admin.booking.notifications.missing_driver_title'))
                                    ->body(__('admin.booking.notifications.missing_driver_body'))
                                    ->send();
                                return;
                            }

                            $targetDateIso = Carbon::parse($filters['booking_date'])->format('Y-m-d');

                            $validBookingsExist = Booking::query()
                                ->whereDate('booking_date', $targetDateIso)
                                ->where('payment_status', 'paid')
                                ->where(function ($q) use ($driverVehicle) {
                                    $q->where(fn($sub) => $sub->where('outbound_vehicle_id', $driverVehicle->id)->where('is_outbound_ensured', true))
                                      ->orWhere(fn($sub) => $sub->where('return_vehicle_id', $driverVehicle->id)->where('is_return_ensured', true));
                                })
                                ->exists();

                            if (!$validBookingsExist) {
                                Notification::make()
                                    ->danger()
                                    ->title(__('admin.booking.notifications.no_valid_title'))
                                    ->body(__('admin.booking.notifications.no_valid_body'))
                                    ->send();
                                return;
                            }

                            $url = DriverManifestBuilder::buildUrl($driverVehicle, $targetDateIso);

                            Notification::make()
                                ->success()
                                ->title(__('admin.booking.notifications.manifest_ready'))
                                ->persistent()
                                ->actions([
                                    NotificationAction::make('open')
                                        ->label(__('admin.booking.open_whatsapp'))
                                        ->button()
                                        ->url($url, shouldOpenInNewTab: true),
                                ])
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('mark_cash_paid')
                        ->label(fn () => __('admin.actions.mark_cash_paid'))
                        ->icon('heroicon-o-currency-dollar')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['is_cash_on_board_paid' => true]))
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('mark_cash_unpaid')
                        ->label(fn () => __('admin.actions.mark_cash_unpaid'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn (Collection $records) => $records->each->update(['is_cash_on_board_paid' => false]))
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Collection $records, Tables\Actions\DeleteBulkAction $action) {
                            if ($records->contains(fn($r) => $r->hasLockedLeg())) {
                                Notification::make()
                                    ->danger()
                                    ->title(__('admin.booking.notifications.locked_title'))
                                    ->body(__('admin.booking.notifications.locked_body'))
                                    ->send();

                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
            'distribution' => Pages\BookingDistribution::route('/distribution'),
        ];
    }

    public static function canEdit(Model $record): bool
    {
        return ! $record->hasLockedLeg();
    }

    public static function canDelete(Model $record): bool
    {
        return ! $record->hasLockedLeg();
    }
}