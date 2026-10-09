<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Tour;
use App\Models\TourDeparture;
use App\Models\TourPricingTier;
use App\Models\Area;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
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
                'tour' => fn($q) => $q->withTrashed(), 
                'area', 
                'outboundVehicle', 
                'returnVehicle', 
                'departure' => fn($q) => $q->withTrashed(), 
                'pricingTier' => fn($q) => $q->withTrashed()
            ])
            ->where('booking_date', '<=', Carbon::today()->addDays(4))
            ->orderBy('created_at', 'desc');
    }

    public static function updatePricing(Forms\Set $set, Forms\Get $get): void
    {
        $tourId = $get('tour_id');
        $tripType = $get('trip_type');
        $vehicleType = $get('pricing_vehicle_type'); 
        $pax = (int) $get('seats_count');

        if (!$tourId || !$tripType || !$vehicleType || $pax < 1) {
            $set('tour_pricing_tier_id', null);
            return;
        }

        $tiers = TourPricingTier::where('tour_id', $tourId)
            ->where('is_active', true)
            ->where('trip_type', $tripType)
            ->where('vehicle_type', $vehicleType)
            ->orderBy('pax_count', 'desc')
            ->get();

        $baseTier = $tiers->where('is_base', true)->first();
        $offers = $tiers->where('is_base', false);

        if (!$baseTier && $offers->isEmpty()) {
            Notification::make()->warning()->title('No pricing configured for these options.')->send();
            $set('tour_pricing_tier_id', null);
            $set('total_price', 0);
            $set('deposit_paid', 0);
            $set('cash_due_on_board', 0);
            return;
        }

        $totalPrice = 0;
        $depositDue = 0;
        $remainingPax = $pax;
        $primaryTierId = null;

        if ($vehicleType === 'private') {
            $carTier = $offers->first() ?? $baseTier;
            $capacity = $carTier->pax_count > 0 ? $carTier->pax_count : 4; 
            $carsNeeded = (int) ceil($remainingPax / $capacity);
            
            $totalPrice = $carsNeeded * $carTier->total_price;
            $depositDue = $carsNeeded * $carTier->deposit_amount;
            $primaryTierId = $carTier->id;
        } else {
            foreach ($offers as $offer) {
                if ($remainingPax <= 0) break;
                if ($offer->pax_count <= 0) continue;

                $multiplier = (int) floor($remainingPax / $offer->pax_count);
                if ($multiplier <= 0) continue;

                if (!$primaryTierId) $primaryTierId = $offer->id;

                $totalPrice += $offer->total_price * $multiplier;
                $depositDue += $offer->deposit_amount * $multiplier;
                $remainingPax -= $offer->pax_count * $multiplier;
            }

            if ($remainingPax > 0 && $baseTier) {
                if (!$primaryTierId) $primaryTierId = $baseTier->id;
                $totalPrice += $remainingPax * $baseTier->total_price;
                $depositDue += $remainingPax * $baseTier->deposit_amount;
            }
        }

        $set('tour_pricing_tier_id', $primaryTierId);
        $set('total_price', $totalPrice);
        $set('deposit_paid', $depositDue); 
        $set('cash_due_on_board', max(0, $totalPrice - $depositDue));
    }

    private static function legVehicleType(Booking $record, ?Vehicle $vehicle): string
    {
        if ($vehicle && !empty($vehicle->type)) {
            return $vehicle->type === 'private_car' ? 'private' : 'microbus';
        }
        return ($record->pricingTier && $record->pricingTier->vehicle_type === 'private') ? 'private' : 'microbus';
    }

    private static function typeToLabel(string $type): string
    {
        return $type === 'private' ? (__('admin.options.private_car') ?? 'Private Car') : (__('admin.options.shared_microbus') ?? 'Shared Microbus');
    }

    public static function vehicleTypeLabel(Booking $record): string
    {
        return self::typeToLabel(self::legVehicleType($record, $record->outboundVehicle));
    }

    private static function vehicleTypeCombinedLabel(Booking $record): string
    {
        $outbound = self::legVehicleType($record, $record->outboundVehicle);
        if ($record->trip_type !== 'round_trip' || !$record->return_vehicle_id) {
            return self::typeToLabel($outbound);
        }
        $return = self::legVehicleType($record, $record->returnVehicle);
        if ($outbound === $return) {
            return self::typeToLabel($outbound);
        }
        return self::typeToLabel($outbound) . ' / ' . self::typeToLabel($return);
    }

    private static function tripTypeLabel(?string $tripType): string
    {
        return $tripType === 'round_trip' ? (__('admin.options.round_trip') ?? 'Round Trip') : (__('admin.options.one_way') ?? 'One Way');
    }

    public static function staysLabel(?Tour $tour, ?string $returnTime = null): string
    {
        if (!$tour) return "❓ " . (__('admin.options.unknown') ?? 'Unknown');
        if ($tour->is_bus_staying) {
            return $returnTime ? "✅ Yes — stays until *{$returnTime}*" : "✅ Yes — waits with group";
        }
        return "❌ No — drop-off only";
    }

    public static function dateBanner(string $cleanDate): string
    {
        return "📅📅📅  *" . mb_strtoupper($cleanDate) . "*  📅📅📅\n════════════════════\n";
    }

    public static function driverInfoBlock(string $driverName, string $driverPhoneDisplay, string $plate, ?string $vehicleType = null): string
    {
        $text = "👤 *DRIVER:* {$driverName}\n🚙 *PLATE:* {$plate}\n";
        if ($vehicleType) $text .= "🚐 *TYPE:* {$vehicleType}\n";
        $text .= "📱 *PHONE:* {$driverPhoneDisplay}\n════════════════════\n\n";
        return $text;
    }

    public static function clientStopLine(Booking $record, ?int $stopNumber = null, bool $includeCashLine = true): string
    {
        $prefix = $stopNumber !== null ? "*{$stopNumber}.* " : '';
        $text = "{$prefix}👤 *{$record->client->full_name}*\n   ▸ *Phone:* {$record->client->whatsapp_number}\n";
        if ($record->client->nationality) $text .= "   ▸ *Nationality:* {$record->client->nationality}\n";
        if ($record->client->email) $text .= "   ▸ *Email:* {$record->client->email}\n";
        $text .= "   ▸ *Pax:* {$record->seats_count}\n   ▸ *Hotel:* {$record->pickup_name}\n";
        if ($record->pickup_address) $text .= "   ▸ *Full Location:* {$record->pickup_address}\n";
        
        if (!empty($record->room_number)) {
            $text .= "   ▸ *Room:* {$record->room_number}\n";
        }
        
        $reception = !empty($record->reception_number) ? $record->reception_number : 'Lobby / Main Reception';
        $text .= "   > *Meet at:* {$reception}\n";

        if ($record->pickup_lat && $record->pickup_lng) {
            $mapQuery = urlencode($record->pickup_name . ' ' . $record->pickup_lat . ',' . $record->pickup_lng);
            $text .= "   ▸ *Map:* https://maps.google.com/maps?q={$mapQuery}\n";
        }
        if ($includeCashLine && $record->cash_due_on_board > 0 && !$record->is_cash_on_board_paid) {
            $text .= "   ▸ 💰 *Collect Cash: $" . number_format($record->cash_due_on_board, 2) . "*\n";
        }
        return $text . "\n";
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(fn () => __('admin.sections.booking_overview') ?? 'Booking Overview')->schema([
                    
                    Forms\Components\TextInput::make('booking_reference')
                        ->disabled()
                        ->dehydrated(false)
                        ->hiddenOn('create'),

                    Forms\Components\Select::make('client_id')
                        ->label('Client (Search by Phone)')
                        ->relationship('client', 'whatsapp_number')
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->whatsapp_number} - {$record->full_name}")
                        ->searchable(['whatsapp_number'])
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('full_name')->required(),
                            Forms\Components\TextInput::make('whatsapp_number')->required(),
                            Forms\Components\TextInput::make('email')->email(),
                            Forms\Components\Select::make('nationality')
                                ->searchable()
                                ->options([
                                    'AF' => 'Afghanistan', 'AL' => 'Albania', 'DZ' => 'Algeria', 'AR' => 'Argentina', 
                                    'AM' => 'Armenia', 'AU' => 'Australia', 'AT' => 'Austria', 'AZ' => 'Azerbaijan', 
                                    'BH' => 'Bahrain', 'BD' => 'Bangladesh', 'BY' => 'Belarus', 'BE' => 'Belgium', 
                                    'BR' => 'Brazil', 'BG' => 'Bulgaria', 'CA' => 'Canada', 'CN' => 'China', 
                                    'CO' => 'Colombia', 'HR' => 'Croatia', 'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 
                                    'DK' => 'Denmark', 'EG' => 'Egypt', 'EE' => 'Estonia', 'FI' => 'Finland', 
                                    'FR' => 'France', 'GE' => 'Georgia', 'DE' => 'Germany', 'GR' => 'Greece', 
                                    'HU' => 'Hungary', 'IN' => 'India', 'ID' => 'Indonesia', 'IR' => 'Iran', 
                                    'IQ' => 'Iraq', 'IE' => 'Ireland', 'IL' => 'Israel', 'IT' => 'Italy', 
                                    'JP' => 'Japan', 'JO' => 'Jordan', 'KZ' => 'Kazakhstan', 'KE' => 'Kenya', 
                                    'KW' => 'Kuwait', 'LB' => 'Lebanon', 'LY' => 'Libya', 'LT' => 'Lithuania', 
                                    'LU' => 'Luxembourg', 'MY' => 'Malaysia', 'MV' => 'Maldives', 'MT' => 'Malta', 
                                    'MX' => 'Mexico', 'MD' => 'Moldova', 'MA' => 'Morocco', 'NL' => 'Netherlands', 
                                    'NZ' => 'New Zealand', 'NG' => 'Nigeria', 'OM' => 'Oman', 'PK' => 'Pakistan', 
                                    'PS' => 'Palestine', 'PH' => 'Philippines', 'PL' => 'Poland', 'PT' => 'Portugal', 
                                    'QA' => 'Qatar', 'RO' => 'Romania', 'RU' => 'Russia', 'SA' => 'Saudi Arabia', 
                                    'RS' => 'Serbia', 'SG' => 'Singapore', 'SK' => 'Slovakia', 'SI' => 'Slovenia', 
                                    'ZA' => 'South Africa', 'KR' => 'South Korea', 'ES' => 'Spain', 'LK' => 'Sri Lanka', 
                                    'SE' => 'Sweden', 'CH' => 'Switzerland', 'SY' => 'Syria', 'TW' => 'Taiwan', 
                                    'TJ' => 'Tajikistan', 'TH' => 'Thailand', 'TN' => 'Tunisia', 'TR' => 'Turkey', 
                                    'TM' => 'Turkmenistan', 'AE' => 'United Arab Emirates', 'GB' => 'United Kingdom', 
                                    'US' => 'United States', 'UA' => 'Ukraine', 'UZ' => 'Uzbekistan', 'VN' => 'Vietnam', 'YE' => 'Yemen'
                                ])
                                ->required(),
                        ]),
                        
                    Forms\Components\Select::make('tour_id')
                        ->label('Tour')
                        ->options(fn () => Tour::where('is_active', true)->pluck('name_en', 'id'))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                            $set('booking_date', null);
                            $set('tour_departure_id', null);
                            self::updatePricing($set, $get);
                        })
                        ->required(),
                        
                    Forms\Components\Select::make('booking_date')
                        ->label('Travel Date')
                        ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->toDateString() : null)
                        ->options(function (Forms\Get $get, ?Booking $record) {
                            $tourId = $get('tour_id');
                            if (!$tourId) return [];

                            $tour = Tour::with(['departures' => fn($q) => $q->where('is_active', true)->orderBy('departure_time')])->find($tourId);
                            if (!$tour) return [];

                            $today = Carbon::today();
                            $dates = [];

                            if ($record && $record->booking_date) {
                                $savedDate = Carbon::parse($record->booking_date);
                                $dates[$savedDate->toDateString()] = $savedDate->format('D, M j, Y') . ' (Current)';
                            }

                            if ($tour->departures->isEmpty()) {
                                for ($i = 0; $i <= 4; $i++) {
                                    $target = $today->copy()->addDays($i);
                                    $targetDateTime = Carbon::parse($target->toDateString() . ' 09:00:00');
                                    if ($targetDateTime->isFuture()) {
                                        $iso = $target->toDateString();
                                        $dates[$iso] = $i === 0 ? 'Today (' . $target->format('D, M j') . ')' : ($i === 1 ? 'Tomorrow (' . $target->format('D, M j') . ')' : $target->format('D, M j, Y'));
                                    }
                                }
                            } else {
                                foreach ($tour->departures as $dep) {
                                    $timeOnly = Carbon::parse($dep->departure_time)->format('H:i:s');
                                    if (is_null($dep->departure_date)) {
                                        for ($i = 0; $i <= 4; $i++) {
                                            $target = $today->copy()->addDays($i);
                                            $targetDateTime = Carbon::parse($target->toDateString() . ' ' . $timeOnly);
                                            if ($targetDateTime->isFuture()) {
                                                $iso = $target->toDateString();
                                                $dates[$iso] = $i === 0 ? 'Today (' . $target->format('D, M j') . ')' : ($i === 1 ? 'Tomorrow (' . $target->format('D, M j') . ')' : $target->format('D, M j, Y'));
                                            }
                                        }
                                    } else {
                                        $target = Carbon::parse($dep->departure_date);
                                        $targetDateTime = Carbon::parse($target->toDateString() . ' ' . $timeOnly);
                                        if ($targetDateTime->isFuture()) {
                                            $iso = $target->toDateString();
                                            $dates[$iso] = $target->format('D, M j, Y');
                                        }
                                    }
                                }
                            }

                            ksort($dates);
                            return $dates;
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set) => $set('tour_departure_id', null))
                        ->required(),
                    
                    Forms\Components\Select::make('tour_departure_id')
                        ->label('Departure Time')
                        ->options(function (Forms\Get $get, ?Booking $record) {
                            $tourId = $get('tour_id');
                            $rawDate = $get('booking_date');
                            if (!$tourId || !$rawDate) return [];

                            $date = Carbon::parse($rawDate)->toDateString();

                            $tour = Tour::with(['departures' => fn($q) => $q->where('is_active', true)->orderBy('departure_time')])->find($tourId);
                            if (!$tour) return [];

                            if ($tour->departures->isEmpty()) {
                                return [0 => '09:00 AM (Default Daily)'];
                            }

                            $options = [];
                            foreach ($tour->departures as $dep) {
                                $timeOnly = Carbon::parse($dep->departure_time)->format('H:i:s');
                                $targetDateTime = Carbon::parse($date . ' ' . $timeOnly);

                                if (!$record && $targetDateTime->isPast()) {
                                    continue;
                                }

                                if (is_null($dep->departure_date) || Carbon::parse($dep->departure_date)->toDateString() === $date) {
                                    $depTime = Carbon::parse($dep->departure_time)->format('h:i A');
                                    $retTime = $dep->return_time ? Carbon::parse($dep->return_time)->format('h:i A') : null;
                                    $options[$dep->id] = $retTime ? "{$depTime} (Ret: {$retTime})" : $depTime;
                                }
                            }

                            return $options;
                        })
                        ->searchable()
                        ->preload()
                        ->required(),
                        
                    Forms\Components\Hidden::make('tour_pricing_tier_id')->required(),

                    Forms\Components\Select::make('pricing_vehicle_type')
                        ->label('Vehicle (For Pricing)')
                        ->options([
                            'microbus' => 'Shared Microbus',
                            'private' => 'Private Car',
                        ])
                        ->dehydrated(false)
                        ->formatStateUsing(fn (?Booking $record) => $record?->pricingTier?->vehicle_type ?? 'microbus')
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => self::updatePricing($set, $get))
                        ->required(),

                    Forms\Components\Select::make('trip_type')
                        ->options([
                            'one_way' => __('admin.options.one_way') ?? 'One Way',
                            'round_trip' => __('admin.options.round_trip') ?? 'Round Trip'
                        ])
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => self::updatePricing($set, $get))
                        ->required(),
                        
                    Forms\Components\TextInput::make('seats_count')
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => self::updatePricing($set, $get))
                        ->required(),
                ])->columns(4),

                Forms\Components\Section::make(fn () => __('admin.sections.location_dispatch') ?? 'Location & Dispatch')->schema([
                    
                    Forms\Components\Select::make('area_id')
                        ->label('Zone')
                        ->options(fn () => Area::pluck('name_en', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),
                        
                    Forms\Components\TextInput::make('pickup_name')->label('Hotel/Location')->required(),
                    Forms\Components\TextInput::make('room_number'),
                    Forms\Components\TextInput::make('reception_number'),
                    Forms\Components\TextInput::make('pickup_address')->columnSpanFull(),

                    Forms\Components\Placeholder::make('google_maps_url')
                        ->label('Google Maps Location')
                        ->columnSpanFull()
                        ->hiddenOn('create')
                        ->content(function (?Booking $record) {
                            if ($record && $record->pickup_lat && $record->pickup_lng) {
                                $mapQuery = urlencode($record->pickup_name . ' ' . $record->pickup_lat . ',' . $record->pickup_lng);
                                $url = "https://maps.google.com/maps?q={$mapQuery}";
                                return new HtmlString("<a href='{$url}' target='_blank' class='text-primary-600 font-bold hover:underline'>Open Location on Google Maps</a>");
                            }
                            return 'No map coordinates provided.';
                        }),
                        
                ])->columns(4), 

                Forms\Components\Section::make(fn () => __('admin.sections.financials') ?? 'Financials')->schema([
                    Forms\Components\TextInput::make('total_price')
                        ->numeric()
                        ->prefix('$')
                        ->required(),

                    Forms\Components\TextInput::make('deposit_paid')
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
                        ->numeric()
                        ->prefix('$')
                        ->required(),

                    Forms\Components\Toggle::make('is_cash_on_board_paid')
                        ->default(false),
                ])->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking_reference')
                    ->label(fn () => __('admin.columns.ref') ?? 'Ref')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Booked On')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('booking_date')
                    ->label('Travel Date')
                    ->date('M d, Y')
                    ->description(function (Booking $record) {
                        $out = $record->departure?->departure_time ? Carbon::parse($record->departure->departure_time)->format('g:i A') : '09:00 AM';
                        $ret = $record->departure?->return_time ? ' | Ret: ' . Carbon::parse($record->departure->return_time)->format('g:i A') : '';
                        return "Out: {$out}{$ret}";
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_distribution_ensured')
                    ->label(fn () => __('admin.columns.ensured') ?? 'Ensured')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-s-lock-closed' : 'heroicon-o-lock-open')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('client.full_name')
                    ->label(fn () => __('admin.columns.client') ?? 'Client')
                    ->searchable()
                    ->description(fn (Booking $record): string => $record->client->whatsapp_number ?? 'No Phone'),

                Tables\Columns\TextColumn::make('tour_name')
                    ->label(fn () => __('admin.columns.tour_vehicle') ?? 'Tour & Vehicle')
                    ->getStateUsing(function (Booking $record) {
                        $en = $record->tour?->name_en ?? 'Unknown';
                        $ru = $record->tour?->name_ru;
                        return $ru ? "{$en} / {$ru}" : $en;
                    })
                    ->description(function (Booking $record) {
                        $combined = self::vehicleTypeCombinedLabel($record);
                        $outbound = self::legVehicleType($record, $record->outboundVehicle);
                        $return   = $record->trip_type === 'round_trip' && $record->return_vehicle_id ? self::legVehicleType($record, $record->returnVehicle) : $outbound;
                        
                        $icon = $outbound === $return ? ($outbound === 'private' ? "🚗" : "🚌") : "🚗🚌";
                        
                        return "{$icon} {$combined}";
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pickup_name')
                    ->label(fn () => __('admin.columns.location') ?? 'Location')
                    ->description(fn (Booking $record): string => $record->area ? "📍 " . (app()->getLocale() === 'ar' ? ($record->area->name_ar ?? $record->area->name_en) : $record->area->name_en) : 'No Zone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('seats_count')
                    ->label(fn () => __('admin.columns.pax') ?? 'Pax')
                    ->numeric()
                    ->badge()
                    ->color('info'),

                Tables\Columns\ToggleColumn::make('is_cash_on_board_paid')
                    ->label(fn () => __('admin.columns.cash_paid') ?? 'Cash Paid')
                    ->sortable(),

                Tables\Columns\TextColumn::make('outboundVehicle.plate_number')
                    ->label(fn () => __('admin.columns.dispatch') ?? 'Dispatch')
                    ->getStateUsing(fn (Booking $record) => $record->outboundVehicle?->plate_number ?? __('admin.options.unassigned') ?? 'Unassigned')
                    ->formatStateUsing(fn (string $state) => "🛫 {$state}")
                    ->description(function (Booking $record) {
                        if ($record->return_vehicle_id) return "🛬 {$record->returnVehicle->plate_number}";
                        return $record->trip_type === 'round_trip' ? "🛬 " . (__('admin.options.unassigned') ?? 'Unassigned') : null;
                    })
                    ->badge()
                    ->color(fn (Booking $record) => $record->outbound_vehicle_id ? 'success' : 'danger'),
            ])
            ->filters([
                Tables\Filters\Filter::make('advanced_schedule')
                    ->columnSpan(4)
                    ->form([
                        Forms\Components\Fieldset::make(fn () => __('admin.sections.schedule_search') ?? 'Schedule Search')->schema([
                            Forms\Components\DatePicker::make('booking_date')->live(),
                            Forms\Components\Select::make('tour_id')->options(function (Forms\Get $get) {
                                $date = $get('booking_date'); $driverId = $get('driver_id'); $time = $get('departure_time');
                                if (!$date && !$driverId && !$time) return Tour::where('is_active', true)->get()->mapWithKeys(fn($t) => [$t->id => app()->getLocale() === 'ar' ? ($t->name_ar ?? $t->name_en) : ($t->name_ru ? "{$t->name_en} / {$t->name_ru}" : $t->name_en)])->toArray();
                                $tourIds = Booking::query()->when($date, fn($q) => $q->whereDate('booking_date', $date))->when($driverId, fn($q) => $q->where(fn($sub) => $sub->where('outbound_vehicle_id', $driverId)->orWhere('return_vehicle_id', $driverId)))->when($time, fn($q) => $q->whereHas('departure', fn($sub) => $sub->withTrashed()->where('departure_time', 'LIKE', $time . '%')))->pluck('tour_id')->filter()->unique();
                                return Tour::withTrashed()->whereIn('id', $tourIds)->get()->mapWithKeys(fn($t) => [$t->id => app()->getLocale() === 'ar' ? ($t->name_ar ?? $t->name_en) : ($t->name_ru ? "{$t->name_en} / {$t->name_ru}" : $t->name_en)])->toArray();
                            })->searchable()->preload()->live(),
                            Forms\Components\Select::make('driver_id')->options(function (Forms\Get $get) {
                                $date = $get('booking_date'); $tourId = $get('tour_id'); $time = $get('departure_time');
                                if (!$date && !$tourId && !$time) return Vehicle::all()->mapWithKeys(fn ($v) => [$v->id => ($v->driver_name ?? 'No Driver') . " ({$v->plate_number})"])->toArray();
                                $vehicleIds = Booking::query()->when($tourId, fn($q) => $q->where('tour_id', $tourId))->when($date, fn($q) => $q->whereDate('booking_date', $date))->when($time, fn($q) => $q->whereHas('departure', fn($sub) => $sub->withTrashed()->where('departure_time', 'LIKE', $time . '%')))->where(fn($q) => $q->whereNotNull('outbound_vehicle_id')->orWhereNotNull('return_vehicle_id'))->select('outbound_vehicle_id', 'return_vehicle_id')->get()->flatMap(fn($b) => [$b->outbound_vehicle_id, $b->return_vehicle_id])->filter()->unique();
                                return Vehicle::whereIn('id', $vehicleIds)->get()->mapWithKeys(fn ($v) => [$v->id => ($v->driver_name ?? 'No Driver') . " ({$v->plate_number})"])->toArray();
                            })->searchable()->preload()->live(),
                            Forms\Components\Select::make('departure_time')->options(function (Forms\Get $get) {
                                $tourId = $get('tour_id'); $date = $get('booking_date'); $driverId = $get('driver_id');
                                $activeTimes = [];
                                if (!$date && !$driverId) {
                                    $activeTimesQuery = TourDeparture::where('is_active', true);
                                    if ($tourId) $activeTimesQuery->where('tour_id', $tourId);
                                    $activeTimes = $activeTimesQuery->pluck('departure_time')->map(fn($t) => Carbon::parse($t)->format('H:i'))->toArray();
                                }
                                $departureIds = Booking::query()->when($tourId, fn($q) => $q->where('tour_id', $tourId))->when($date, fn($q) => $q->whereDate('booking_date', $date))->when($driverId, fn($q) => $q->where(fn($sub) => $sub->where('outbound_vehicle_id', $driverId)->orWhere('return_vehicle_id', $driverId)))->whereNotNull('tour_departure_id')->distinct()->pluck('tour_departure_id');
                                $bookingTimes = TourDeparture::withTrashed()->whereIn('id', $departureIds)->pluck('departure_time')->map(fn($t) => Carbon::parse($t)->format('H:i'))->toArray();
                                $times = array_unique(array_merge($activeTimes, $bookingTimes)); sort($times);
                                $formatted = []; foreach($times as $time) { $formatted[$time] = Carbon::parse($time)->format('g:i A'); } return $formatted;
                            })->live(),
                        ])->columns(4),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['booking_date'], fn (Builder $q, $date) => $q->whereDate('booking_date', $date))->when($data['tour_id'], fn (Builder $q, $tour) => $q->where('tour_id', $tour))->when($data['driver_id'], fn (Builder $q, $driver) => $q->where(fn($sub) => $sub->where('outbound_vehicle_id', $driver)->orWhere('return_vehicle_id', $driver)))->when($data['departure_time'], fn (Builder $q, $time) => $q->whereHas('departure', fn($sub) => $sub->withTrashed()->where('departure_time', 'LIKE', $time . '%')));
                    }),

                Tables\Filters\SelectFilter::make('area_id')->options(fn () => Area::pluck('name_en', 'id'))->searchable()->preload(),
                Tables\Filters\SelectFilter::make('vehicle_type')->options(['microbus' => 'Shared Microbus', 'private' => 'Private Car',])->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) $query->whereHas('pricingTier', fn ($q) => $q->withTrashed()->where('vehicle_type', $data['value']));
                }),
                Tables\Filters\SelectFilter::make('trip_type')->options(['one_way' => 'One Way', 'round_trip' => 'Round Trip',]),
                Tables\Filters\SelectFilter::make('distribution_status')->options(['fully_assigned' => 'Fully Assigned', 'unassigned' => 'Needs Assignment',])->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'unassigned') {
                            $query->where(fn ($q) => $q->whereNull('outbound_vehicle_id')->orWhere(fn ($sub) => $sub->where('trip_type', 'round_trip')->whereNull('return_vehicle_id')));
                        } elseif ($data['value'] === 'fully_assigned') {
                            $query->whereNotNull('outbound_vehicle_id')->where(fn ($q) => $q->where('trip_type', 'one_way')->orWhereNotNull('return_vehicle_id'));
                        }
                }),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('whatsapp_client')
                        ->label('Message Client')
                        ->icon('heroicon-o-device-phone-mobile')
                        ->color('info')
                        ->visible(fn (Booking $record) => $record->client && $record->client->whatsapp_number)
                        ->action(function (Booking $record, Component $livewire) {
                            $brandName = Setting::first()?->brand_name ?? 'Our Company';
                            $phone = preg_replace('/[^0-9]/', '', $record->client->whatsapp_number);
                            $pickupTime = $record->departure?->departure_time ? Carbon::parse($record->departure->departure_time)->format('g:i A') : '09:00 AM';
                            $returnTime = $record->departure?->return_time ? Carbon::parse($record->departure->return_time)->format('g:i A') : null;
                            $cleanDate = Carbon::parse($record->booking_date)->format('M d, Y');
                            
                            $timingText = "🕒 *Pickup Time:* {$pickupTime}";
                            if ($record->trip_type === 'round_trip' && $returnTime) {
                                $timingText .= " | *Return Time:* {$returnTime}";
                            }

                            $receptionText = !empty($record->reception_number) ? $record->reception_number : 'Main Lobby / Reception';
                            
                            $tourName = $record->tour?->name_ru ? "{$record->tour->name_en} / {$record->tour->name_ru}" : ($record->tour?->name_en ?? 'Custom Tour');
                            $cash = ($record->cash_due_on_board > 0 && !$record->is_cash_on_board_paid) ? $record->cash_due_on_board : 0;
                            
                            $vehicleSection = "";
                            if ($record->outboundVehicle) {
                                $vType = $record->outboundVehicle->type === 'private_car' ? 'Private Car' : 'Shared Microbus';
                                $driverName = $record->outboundVehicle->driver_name ?? 'Assigned Driver';
                                $driverPhone = $record->outboundVehicle->driver_phone ? " (📱 {$record->outboundVehicle->driver_phone})" : '';
                                
                                $vehicleSection .= "🚐 *Outbound Vehicle:* {$vType} ({$record->outboundVehicle->plate_number})\n";
                                $vehicleSection .= "👤 *Outbound Driver:* {$driverName}{$driverPhone}\n";
                            } else {
                                $vehicleSection .= "🚐 *Vehicle:* (Will be assigned shortly)\n";
                            }

                            if ($record->trip_type === 'round_trip') {
                                if ($record->returnVehicle) {
                                    $retVType = $record->returnVehicle->type === 'private_car' ? 'Private Car' : 'Shared Microbus';
                                    $retDriverName = $record->returnVehicle->driver_name ?? 'Assigned Driver';
                                    $retDriverPhone = $record->returnVehicle->driver_phone ? " (📱 {$record->returnVehicle->driver_phone})" : '';
                                    
                                    $vehicleSection .= "\n🛬 *Return Vehicle:* {$retVType} ({$record->returnVehicle->plate_number})\n";
                                    $vehicleSection .= "👤 *Return Driver:* {$retDriverName}{$retDriverPhone}\n";
                                } elseif ($record->outboundVehicle && $record->tour?->is_bus_staying) {
                                    $vehicleSection .= "\n🛬 *Return Vehicle:* Same vehicle (stays on-site)\n";
                                }
                            }

                            $text = "Hello {$record->client->full_name}! 👋\n\n";
                            $text .= "Your upcoming adventure with *{$brandName}* is confirmed! Here are your complete details:\n\n";
                            $text .= "🚩 *Tour:* {$tourName}\n";
                            $text .= "📅 *Date:* {$cleanDate}\n";
                            $text .= "{$timingText}\n";
                            $text .= "📍 *Location:* {$record->pickup_name}\n";
                            if (!empty($record->room_number)) {
                                $text .= "🚪 *Room:* {$record->room_number}\n";
                            }
                            $text .= "🤝 *Meet At:* {$receptionText}\n";
                            $text .= "💺 *Passengers:* {$record->seats_count} Pax\n";
                            
                            if (!empty($vehicleSection)) {
                                $text .= "\n" . trim($vehicleSection) . "\n";
                            }

                            if ($cash > 0) {
                                $text .= "\n💵 *Remaining Balance:* $" . number_format($cash, 2) . " (Please prepare this in cash for the driver)\n";
                            }

                            $text .= "\n📌 *Important Instructions:*\n";
                            $text .= "• Please be waiting in the reception or lobby area *10 to 15 minutes* before your scheduled pickup time.\n";
                            $text .= "• Kindly note that the vehicle might arrive slightly late due to traffic or previous hotel pickups. Your patience is highly appreciated! 🙏\n\n";
                            $text .= "Thank you for choosing {$brandName}! 🌟";

                            $qs = http_build_query(['phone' => $phone, 'text' => $text], '', '&', PHP_QUERY_RFC3986);
                            $livewire->js('window.open("https://api.whatsapp.com/send?' . $qs . '", "_blank");');
                        }),
                ])->icon('heroicon-m-ellipsis-vertical')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('whatsapp_driver_manifest_bulk')
                        ->label('Send Daily Manifest')->icon('heroicon-s-paper-airplane')->color('primary')->action(function (Collection $records, Component $livewire) {
                            $filters = $livewire->getTableFilterState('advanced_schedule') ?? [];
                            if (empty($filters['booking_date']) || empty($filters['driver_id'])) { Notification::make()->danger()->title('Action Forbidden')->body('You must apply both a "Specific Date" and an "Assigned Driver" filter before sending the daily manifest.')->send(); return; }
                            $driverCounts = []; foreach ($records as $record) { $d = array_unique(array_filter([$record->outbound_vehicle_id, $record->return_vehicle_id])); foreach ($d as $id) { $driverCounts[$id] = ($driverCounts[$id] ?? 0) + 1; } }
                            if (empty($driverCounts)) { Notification::make()->danger()->title('No assigned drivers found in selection.')->send(); return; }
                            arsort($driverCounts); $targetDriverId = array_key_first($driverCounts); $driverVehicle = Vehicle::find($targetDriverId);
                            if (!$driverVehicle || !$driverVehicle->driver_phone) { Notification::make()->danger()->title('Missing Driver Phone')->body('No phone number is registered for the assigned driver.')->send(); return; }
                            $driverPhone = preg_replace('/[^0-9]/', '', $driverVehicle->driver_phone); $driverName = $driverVehicle->driver_name ?? 'Unknown Driver'; $driverPlate = $driverVehicle->plate_number ?? 'No Plate';
                            $dates = $records->pluck('booking_date')->map(fn($d) => Carbon::parse($d)->format('M d, Y'))->unique(); $cleanDate = $dates->count() === 1 ? $dates->first() : 'Multiple Dates';
                            $dailyTotalCash = 0; $dailyOutboundPax = 0; $dailyReturnPax = 0;
                            $driverRecords = $records->filter(fn ($r) => $r->outbound_vehicle_id === $targetDriverId || $r->return_vehicle_id === $targetDriverId);
                            $manifestVehicleType = !empty($driverVehicle->type) ? ($driverVehicle->type === 'private_car' ? 'Private Car' : 'Shared Microbus') : ($driverRecords->isNotEmpty() ? BookingResource::vehicleTypeLabel($driverRecords->first()) : null);
                            $text = BookingResource::dateBanner($cleanDate) . BookingResource::driverInfoBlock($driverName, $driverVehicle->driver_phone, $driverPlate, $manifestVehicleType) . "*ROUTE MANIFEST*\n════════════════════\n\n";
                            $groupedByTour = $driverRecords->groupBy('tour_id')->sortBy(function ($group) { return $group->first()->departure?->departure_time ?? '99:99'; });
                            foreach ($groupedByTour as $tourId => $tourGroup) {
                                $sampleRecord = $tourGroup->first(); $tour = $sampleRecord->tour;
                                $outboundRecords = $tourGroup->filter(fn ($r) => $r->outbound_vehicle_id === $targetDriverId); $returnRecords = $tourGroup->filter(fn ($r) => $r->return_vehicle_id === $targetDriverId);
                                $goTime = $sampleRecord->departure?->departure_time ? Carbon::parse($sampleRecord->departure->departure_time)->format('g:i A') : '09:00 AM'; $retTime = $sampleRecord->departure?->return_time ? Carbon::parse($sampleRecord->departure->return_time)->format('g:i A') : 'TBD';
                                $staysLabel = BookingResource::staysLabel($tour, $retTime); $tourName = $tour?->name_ru ? "{$tour->name_en} / {$tour->name_ru}" : ($tour?->name_en ?? 'Unknown');
                                $text .= "┌────────────────────┐\n│ 🚩 *{$tourName}*\n│ 🕐 _Out: {$goTime} | Ret: {$retTime}_\n│ 🅿️ *Stays:* {$staysLabel}\n└────────────────────┘\n";
                                $tourStops = 0; $tourPax = 0; $tourCash = 0;
                                if ($outboundRecords->isNotEmpty()) {
                                    $text .= "🛫 *[ GOING TO TOUR ]* — " . $outboundRecords->count() . " stop(s)\n⏰ *Pickup Time:* {$goTime}\n\n"; $stopNum = 1; $groupPax = 0; $groupCash = 0;
                                    foreach ($outboundRecords as $record) {
                                        $cash = ($record->cash_due_on_board > 0 && !$record->is_cash_on_board_paid) ? $record->cash_due_on_board : 0;
                                        $groupPax += $record->seats_count; $groupCash += $cash; $dailyOutboundPax += $record->seats_count; $dailyTotalCash += $cash; $tourStops++; $tourPax += $record->seats_count; $tourCash += $cash;
                                        $text .= BookingResource::clientStopLine($record, $stopNum); $text .= "_Outbound subtotal: {$groupPax} pax | $" . number_format($groupCash, 2) . "_\n\n";
                                    }
                                }
                                if ($returnRecords->isNotEmpty()) {
                                    $text .= "🛬 *[ RETURNING TO HOTEL ]* — " . $returnRecords->count() . " stop(s)\n⏰ *Return Time:* {$retTime}\n\n"; $stopNum = 1; $groupPax = 0;
                                    foreach ($returnRecords as $record) {
                                        $collectHere = $record->outbound_vehicle_id !== $targetDriverId; $cash = ($collectHere && $record->cash_due_on_board > 0 && !$record->is_cash_on_board_paid) ? $record->cash_due_on_board : 0;
                                        $groupPax += $record->seats_count; $dailyReturnPax += $record->seats_count; $dailyTotalCash += $cash; $tourStops++; $tourPax += $record->seats_count; $tourCash += $cash;
                                        $text .= BookingResource::clientStopLine($record, $stopNum, $collectHere); $text .= "_Return subtotal: {$groupPax} pax_\n\n";
                                    }
                                }
                                $text .= "*📌 TOUR TOTAL:* {$tourStops} booking(s) | {$tourPax} pax | $" . number_format($tourCash, 2) . "\n════════════════════\n\n";
                            }
                            $text .= "*📊 DAILY TOTALS*\n▸ *Pax (Going):* {$dailyOutboundPax}\n▸ *Pax (Return):* {$dailyReturnPax}\n▸ *TOTAL CASH TO COLLECT: $" . number_format($dailyTotalCash, 2) . "*\n\nDrive safely! 🛣";
                            $qs = http_build_query(['phone' => $driverPhone, 'text' => $text], '', '&', PHP_QUERY_RFC3986);
                            $livewire->js('window.open("https://api.whatsapp.com/send?' . $qs . '", "_blank");');
                        })->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('mark_cash_paid')->label('Mark Selected as Paid')->icon('heroicon-o-currency-dollar')->color('success')->action(fn (Collection $records) => $records->each->update(['is_cash_on_board_paid' => true]))->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('mark_cash_unpaid')->label('Mark Selected as Unpaid')->icon('heroicon-o-x-circle')->color('danger')->action(fn (Collection $records) => $records->each->update(['is_cash_on_board_paid' => false]))->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
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

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return !$record->is_distribution_ensured;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return !$record->is_distribution_ensured;
    }
}