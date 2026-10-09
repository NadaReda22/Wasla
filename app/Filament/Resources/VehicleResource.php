<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(fn () => __('admin.sections.vehicle_details') ?? 'Vehicle Details')
                    ->schema([
                        Forms\Components\TextInput::make('plate_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->label(fn () => __('admin.fields.plate_number') ?? 'Plate Number')
                            ->placeholder('e.g. ABC 1234'),

                        Forms\Components\Select::make('type')
                            ->label(fn () => __('admin.fields.type') ?? 'Type')
                            ->options([
                                'microbus' => 'Shared Microbus',
                                'private_car' => 'Private Car',
                            ])
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
                            ->label(fn () => __('admin.fields.capacity') ?? 'Passenger Capacity'),

                        Forms\Components\Toggle::make('is_active')
                            ->label(fn () => __('admin.fields.is_active') ?? 'Vehicle is Active')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make(fn () => __('admin.sections.driver_info') ?? 'Driver Information')
                    ->schema([
                        Forms\Components\TextInput::make('driver_name')
                            ->label(fn () => __('admin.fields.driver_name') ?? 'Driver Name')
                            ->placeholder('e.g. Ahmed Ali')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('driver_phone')
                            ->label(fn () => __('admin.fields.driver_phone') ?? 'WhatsApp Number')
                            ->placeholder('e.g. 201000000000')
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
                    ->label(fn () => __('admin.columns.plate_number') ?? 'Plate Number')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->label(fn () => __('admin.columns.type') ?? 'Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'microbus' => 'info',
                        'private_car' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),

                Tables\Columns\TextColumn::make('driver_name')
                    ->label(fn () => __('admin.columns.driver_name') ?? 'Driver')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('driver_phone')
                    ->label(fn () => __('admin.columns.driver_phone') ?? 'WhatsApp')
                    ->icon('heroicon-m-chat-bubble-oval-left-ellipsis')
                    ->color('success') 
                    ->url(fn (Vehicle $record): ?string => $record->driver_phone ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $record->driver_phone) : null)
                    ->openUrlInNewTab()
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label(fn () => __('admin.columns.active') ?? 'Active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(fn () => __('admin.filters.type') ?? 'Type')
                    ->options([
                        'microbus' => 'Microbus',
                        'private_car' => 'Private Car',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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