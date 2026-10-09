<?php
namespace App\Filament\Resources;

use App\Filament\Resources\AreaResource\Pages;
use App\Models\Area;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AreaResource extends Resource
{
    protected static ?string $model = Area::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.zones_and_pricing');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.areas');
    }

    public static function getModelLabel(): string
    {
        return __('admin.models.area');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.areas');
    }

    protected static function colorLabels(): array
    {
        return [
            'bg-primary'     => __('admin.colors.primary'),
            'bg-turquoise'   => __('admin.colors.turquoise'),
            'bg-navy'        => __('admin.colors.navy'),
            'bg-coral'       => __('admin.colors.coral'),
            'bg-gold'        => __('admin.colors.gold'),
            'bg-primary-400' => __('admin.colors.primary_400'),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(fn () => __('admin.sections.area_details'))
                    ->icon('heroicon-o-map')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name_en')
                                    ->label(fn () => __('admin.fields.name_en'))
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('name_ar')
                                    ->label(fn () => __('admin.fields.name_ar'))
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('latitude')
                                    ->label(fn () => __('admin.fields.latitude'))
                                    ->numeric()
                                    ->step(0.00000001)
                                    ->helperText(fn () => __('admin.fields.latitude_example')),
                                Forms\Components\TextInput::make('longitude')
                                    ->label(fn () => __('admin.fields.longitude'))
                                    ->numeric()
                                    ->step(0.00000001)
                                    ->helperText(fn () => __('admin.fields.longitude_example')),
                            ]),
                    ]),

                Forms\Components\Section::make(fn () => __('admin.sections.pricing_visibility'))
                    ->icon('heroicon-o-currency-dollar')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('color')
                                    ->label(fn () => __('admin.columns.map_pin'))
                                    ->options(fn () => self::colorLabels())
                                    ->placeholder(fn () => __('admin.colors.random_color'))
                                    ->helperText(fn () => __('admin.fields.color_help')),

                                // Read-only in the form. ->dehydrated() keeps saving the current value
                                Forms\Components\Toggle::make('is_in_free_zone')
                                    ->label(fn () => __('admin.columns.free_zone'))
                                    ->default(true)
                                    ->disabled()
                                    ->dehydrated()
                                    ->onColor('success')
                                    ->offColor('danger'),

                                Forms\Components\Toggle::make('is_active')
                                    ->label(fn () => __('admin.columns.active'))
                                    ->default(true)
                                    ->onColor('success')
                                    ->offColor('danger'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_en')
                    ->label(fn () => __('admin.columns.name_en'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('latitude')
                    ->label(fn () => __('admin.fields.latitude'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('longitude')
                    ->label(fn () => __('admin.fields.longitude'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('color')
                    ->label(fn () => __('admin.columns.map_pin'))
                    ->html()
                    ->formatStateUsing(function (?string $state): string {
                        if (!$state) {
                            return '<div class="flex items-center gap-2"><span style="background-color: #e5e7eb; width: 14px; height: 14px; border-radius: 50%; border: 1px solid #d1d5db;"></span><span class="text-sm text-gray-500">' . e(__('admin.colors.random')) . '</span></div>';
                        }

                        $hexColors = [
                            'bg-primary'     => '#1877c9',
                            'bg-turquoise'   => '#48c4d8',
                            'bg-navy'        => '#1b3a5a',
                            'bg-coral'       => '#ff6b6b',
                            'bg-gold'        => '#d4af37',
                            'bg-primary-400' => '#60a5fa',
                        ];

                        $hex = $hexColors[$state] ?? '#9ca3af';
                        $label = self::colorLabels()[$state] ?? $state;

                        return '<div class="flex items-center gap-2"><span style="background-color: ' . $hex . '; width: 14px; height: 14px; border-radius: 50%; box-shadow: 0 1px 2px rgba(0,0,0,0.1);"></span><span class="text-sm font-medium">' . e($label) . '</span></div>';
                    }),


                Tables\Columns\ToggleColumn::make('is_active')
                    ->label(fn () => __('admin.columns.active'))
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color('warning')
                    ->icon('heroicon-o-pencil-square'),
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash'),
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
            'index' => Pages\ListAreas::route('/'),
            'create' => Pages\CreateArea::route('/create'),
            'edit' => Pages\EditArea::route('/{record}/edit'),
        ];
    }
}