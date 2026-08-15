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
    protected static ?string $navigationGroup = 'Zones & Pricing';
    protected static ?int $navigationSort = 3;
    protected static ?string $label = 'Coverage Areas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Area Details')
                    ->icon('heroicon-o-map')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name_en')
                                    ->label('Name (English)')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('name_ar')
                                    ->label('Name (Arabic)')
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('latitude')
                                    ->numeric()
                                    ->step(0.00000001)
                                    ->helperText('e.g., 27.9158'),
                                Forms\Components\TextInput::make('longitude')
                                    ->numeric()
                                    ->step(0.00000001)
                                    ->helperText('e.g., 34.3298'),
                            ]),
                    ]),

                Forms\Components\Section::make('Pricing & Visibility')
                    ->icon('heroicon-o-currency-dollar')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('color')
                                    ->label('Map Pin Color')
                                    ->options([
                                        'bg-primary' => 'Primary (Blue)',
                                        'bg-turquoise' => 'Turquoise',
                                        'bg-navy' => 'Navy',
                                        'bg-coral' => 'Coral',
                                        'bg-gold' => 'Gold',
                                        'bg-primary-400' => 'Light Blue',
                                    ])
                                    ->placeholder('Random Color')
                                    ->helperText('Leave empty for a random color.'),
                                    
                                Forms\Components\Toggle::make('is_in_free_zone')
                                    ->label('Free Pickup Zone')
                                    ->default(true)
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->helperText('Enable if pickups here are included in the base ticket. If disabled, customers staying in this area will be subject to an extra out-of-zone transfer fee.'),
                                    
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
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
                    ->label('Area')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('latitude')
                    ->label('Latitude')
                    ->searchable()
                    ->sortable()
                    ->toggleable(), // Allows admin to hide it if the table gets too crowded
                    
                Tables\Columns\TextColumn::make('longitude')
                    ->label('Longitude')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('color')
                    ->label('Pin Color')
                    ->html()
                    ->formatStateUsing(function (?string $state): string {
                        if (!$state) {
                            return '<div class="flex items-center gap-2"><span style="background-color: #e5e7eb; width: 14px; height: 14px; border-radius: 50%; border: 1px solid #d1d5db;"></span><span class="text-sm text-gray-500">Random</span></div>';
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
                        $label = ucfirst(str_replace(['bg-', '-400'], ['', ' (Light)'], $state));
                        
                        return '<div class="flex items-center gap-2"><span style="background-color: ' . $hex . '; width: 14px; height: 14px; border-radius: 50%; box-shadow: 0 1px 2px rgba(0,0,0,0.1);"></span><span class="text-sm font-medium">' . $label . '</span></div>';
                    }),

                Tables\Columns\IconColumn::make('is_in_free_zone')
                    ->label('Free Zone')
                    ->boolean()
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
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