<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Models\Booking;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.operations');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.vehicles');
    }

    public static function getModelLabel(): string
    {
        return __('admin.models.vehicle');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.vehicles');
    }

    private static function vehicleTypes(): array
    {
        return [
            'microbus'    => __('admin.options.shared_microbus'),
            'private_car' => __('admin.options.private_car'),
        ];
    }

    /** True when any booking (past or future) still points at one of these vehicles. */
    private static function hasBookings(array $vehicleIds): bool
    {
        return Booking::query()
            ->where(fn ($q) => $q
                ->whereIn('outbound_vehicle_id', $vehicleIds)
                ->orWhereIn('return_vehicle_id', $vehicleIds))
            ->exists();
    }

    private static function notifyDeleteBlocked(): void
    {
        Notification::make()
            ->danger()
            ->title(__('admin.vehicle.delete_blocked_title'))
            ->body(__('admin.vehicle.delete_blocked_body'))
            ->send();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(fn () => __('admin.sections.vehicle_details'))
                    ->schema([
                        Forms\Components\TextInput::make('plate_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->label(fn () => __('admin.fields.plate_number'))
                            ->placeholder(fn () => __('admin.vehicle.placeholder_plate')),

                        Forms\Components\Select::make('type')
                            ->label(fn () => __('admin.fields.type'))
                            ->options(self::vehicleTypes())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if ($state === 'private_car') {
                                    $set('capacity', 3);
                                } elseif ($state === 'microbus') {
                                    $set('capacity', 14);
                                }
                            }),

                        Forms\Components\TextInput::make('capacity')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->label(fn () => __('admin.fields.capacity')),

                        Forms\Components\Toggle::make('is_active')
                            ->label(fn () => __('admin.fields.is_active'))
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make(fn () => __('admin.sections.driver_info'))
                    ->schema([
                        Forms\Components\TextInput::make('driver_name')
                            ->label(fn () => __('admin.fields.driver_name'))
                            ->placeholder(fn () => __('admin.vehicle.placeholder_driver_name'))
                            ->maxLength(255),

                        Forms\Components\TextInput::make('driver_phone')
                            ->label(fn () => __('admin.fields.driver_phone'))
                            ->placeholder(fn () => __('admin.vehicle.placeholder_driver_phone'))
                            ->tel()
                            ->maxLength(50),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plate_number')
                    ->label(fn () => __('admin.columns.plate_number'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->label(fn () => __('admin.columns.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'microbus' => 'info',
                        'private_car' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => self::vehicleTypes()[$state] ?? $state),

                Tables\Columns\TextColumn::make('driver_name')
                    ->label(fn () => __('admin.columns.driver_name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('driver_phone')
                    ->label(fn () => __('admin.columns.driver_phone'))
                    ->icon('heroicon-m-chat-bubble-oval-left-ellipsis')
                    ->color('success')
                    ->url(fn (Vehicle $record): ?string => $record->driver_phone ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $record->driver_phone) : null)
                    ->openUrlInNewTab()
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label(fn () => __('admin.columns.active')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(fn () => __('admin.filters.type'))
                    ->options(self::vehicleTypes()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(fn () => __('admin.actions.edit')),

                Tables\Actions\DeleteAction::make()
                    ->label(fn () => __('admin.actions.delete'))
                    ->before(function (Vehicle $record, Tables\Actions\DeleteAction $action) {
                        if (self::hasBookings([$record->id])) {
                            self::notifyDeleteBlocked();
                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Collection $records, Tables\Actions\DeleteBulkAction $action) {
                            if (self::hasBookings($records->pluck('id')->all())) {
                                self::notifyDeleteBlocked();
                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}