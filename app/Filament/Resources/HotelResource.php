<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HotelResource\Pages;
use App\Models\Hotel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HotelResource extends Resource
{
    protected static ?string $model = Hotel::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.zones_and_pricing');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.hotels');
    }

    public static function getModelLabel(): string
    {
        return __('admin.models.hotel');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.hotels');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(fn () => __('admin.models.hotel'))
                    ->schema([
                        Forms\Components\TextInput::make('name_en')
                            ->label(fn () => __('admin.fields.name_en'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('name_ar')
                            ->label(fn () => __('admin.fields.name_ar'))
                            ->maxLength(255),

                        Forms\Components\Select::make('area_id')
                            ->label(fn () => __('admin.fields.area'))
                            ->relationship('area', 'name_en')
                            ->searchable()
                            ->preload()
                            ->helperText(fn () => __('admin.fields.area_help')),

                        Forms\Components\Textarea::make('keywords')
                            ->label(fn () => __('admin.fields.keywords'))
                            ->helperText(fn () => __('admin.fields.keywords_help'))
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make(fn () => __('admin.columns.location'))
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label(fn () => __('admin.fields.latitude'))
                            ->numeric()
                            ->step(0.00000001)
                            ->required(),

                        Forms\Components\TextInput::make('longitude')
                            ->label(fn () => __('admin.fields.longitude'))
                            ->numeric()
                            ->step(0.00000001)
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make(fn () => __('admin.sections.financials'))
                    ->schema([
                        // Read-only in the form. ->dehydrated() keeps the current value (or the 0.00 default on create)
                        // being saved with the record, instead of being skipped because the field is disabled.
                        Forms\Components\TextInput::make('extra_transfer_fee')
                            ->label(fn () => __('admin.fields.extra_transfer_fee'))
                            ->numeric()
                            ->default(0.00)
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated()
                            ->helperText(fn () => __('admin.fields.extra_fee_help')),

                        Forms\Components\Toggle::make('is_active')
                            ->label(fn () => __('admin.fields.is_active'))
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_en')
                    ->label(fn () => __('admin.fields.name_en'))
                    ->searchable(['name_en', 'name_ar', 'keywords'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('name_ar')
                    ->label(fn () => __('admin.fields.name_ar'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('area.name_en')
                    ->label(fn () => __('admin.fields.area'))
                    ->badge()
                    ->searchable()
                    ->sortable(),

                // Tables\Columns\TextColumn::make('extra_transfer_fee')
                //     ->label(fn () => __('admin.columns.extra_fee'))
                //     ->money('USD')
                //     ->sortable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label(fn () => __('admin.columns.active')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('area_id')
                    ->label(fn () => __('admin.fields.area'))
                    ->relationship('area', 'name_en'),

                Tables\Filters\Filter::make('unassigned')
                    ->label(fn () => __('admin.filters.unassigned_hotels'))
                    ->query(fn ($query) => $query->whereNull('area_id'))
                    ->toggle(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('toggle_area_hotels')
                    ->label(fn () => __('admin.actions.toggle_area'))
                    ->icon('heroicon-o-bolt')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('area_id')
                            ->label(fn () => __('admin.fields.area'))
                            ->options(\App\Models\Area::pluck('name_en', 'id'))
                            ->required()
                            ->searchable(),
                        Forms\Components\Toggle::make('is_active')
                            ->label(fn () => __('admin.fields.is_active'))
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger'),
                    ])
                    ->action(function (array $data): void {
                        Hotel::where('area_id', $data['area_id'])
                            ->update(['is_active' => $data['is_active']]);
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn () => __('admin.actions.toggle_area'))
                    ->modalDescription(fn () => __('admin.fields.hotel_area_warning')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([

                    Tables\Actions\BulkAction::make('assign_area')
                        ->label(fn () => __('admin.actions.assign_area'))
                        ->icon('heroicon-o-map-pin')
                        ->form([
                            Forms\Components\Select::make('area_id')
                                ->label(fn () => __('admin.fields.area'))
                                ->relationship('area', 'name_en')
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data): void {
                            $records->each(function ($record) use ($data) {
                                $record->update(['area_id' => $data['area_id']]);
                            });
                        }),

                    Tables\Actions\BulkAction::make('update_status')
                        ->label(fn () => __('admin.actions.update_status'))
                        ->icon('heroicon-o-power')
                        ->color('warning')
                        ->form([
                            Forms\Components\Toggle::make('is_active')
                                ->label(fn () => __('admin.columns.active'))
                                ->default(true)
                                ->onColor('success')
                                ->offColor('danger'),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data): void {
                            $records->each(function ($record) use ($data) {
                                $record->update(['is_active' => $data['is_active']]);
                            });
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHotels::route('/'),
            'create' => Pages\CreateHotel::route('/create'),
            'edit' => Pages\EditHotel::route('/{record}/edit'),
        ];
    }
}