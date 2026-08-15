<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TourResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use App\Filament\Resources\TourResource\RelationManagers;
use App\Models\Tour;

class TourResource extends Resource
{
    protected static ?string $model = Tour::class;
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 4;
    protected static ?string $label = 'Tours';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name_en')
                                    ->label('Tour Name (English)')
                                    ->placeholder('e.g., Ras Mohammed Snorkeling')
                                    ->required(),
                                    
                                Forms\Components\TextInput::make('name_ar')
                                    ->label('Tour Name (Arabic)')
                                    ->placeholder('e.g., رحلة غطس في رأس محمد'),
                                    
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->helperText('This determines the web address URL.'),
                                    
                                Forms\Components\TextInput::make('location')
                                    ->label('Location / Area')
                                    ->placeholder('e.g., Sharm El Sheikh')
                                    ->required(),
                                    
                                Forms\Components\TextInput::make('tag_name')
                                    ->label('Highlight Badge Text')
                                    ->placeholder('e.g., Best Seller, 20% Off')
                                    ->helperText('Leave blank if you do not want a badge.'),
                                    
                                Forms\Components\Select::make('tag_color_class')
                                    ->label('Badge Color')
                                    ->options([
                                        'bg-blue-500 text-white' => '🔵 Blue',
                                        'bg-red-500 text-white' => '🔴 Red',
                                        'bg-green-500 text-white' => '🟢 Green',
                                        'bg-yellow-500 text-black' => '🟡 Yellow',
                                        'bg-gray-800 text-white' => '⚫ Dark Navy',
                                    ])
                                    ->native(false) //don't use the browser's native <select> dropdown; use Filament's custom Select component instead
                                    ->helperText('Select the color for the highlight badge on the website.')
                            ]),
                            
                        Forms\Components\FileUpload::make('image_url')
                            ->label('Tour Image')
                            ->directory('tours')
                            ->preserveFilenames()//Keep real file names instead of generating random ones
                            ->required(),
                            
                        Forms\Components\RichEditor::make('description_en')
                            ->label('Description (English)')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                                'link',
                                'undo',
                                'redo',
                            ]),
                            
                        Forms\Components\RichEditor::make('description_ar')
                            ->label('Description (Arabic)')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                                'link',
                                'undo',
                                'redo',
                            ]),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active on Website')
                            ->default(true),
                    ]),
            ]);
    }

    /**
     * display one record's details
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Tour Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('name_en')->label('Name (EN)'),
                        Infolists\Components\TextEntry::make('name_ar')->label('Name (AR)'),
                        
                        Infolists\Components\ImageEntry::make('image_url')->label('Image')->circular(),
                        Infolists\Components\IconEntry::make('is_active')->boolean()->label('Active'),
                        
                        Infolists\Components\TextEntry::make('location')->label('Location'),
                        
                        Infolists\Components\TextEntry::make('tag_name')
                            ->label('Tag Name')
                            ->badge(),
                            
                        Infolists\Components\TextEntry::make('tag_color_class')
                            ->label('Tag Color Class'),
                    ])->columns(2),

                Infolists\Components\Section::make('Descriptions')
                    ->schema([
                        Infolists\Components\TextEntry::make('description_en')
                            ->label('Description (English)')
                            ->columnSpanFull(),
                            
                        Infolists\Components\TextEntry::make('description_ar')
                            ->label('Description (Arabic)')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * Display the table of records
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')->label('Image')->circular(),
                Tables\Columns\TextColumn::make('name_en')->label('Name (EN)')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('location')->label('Location')->searchable()->sortable(),
                
                Tables\Columns\TextColumn::make('pricing_list')
                    ->label('Pricing Tiers')
                    ->badge()//display the values as badges rather than normal text.
                    ->color('success')//With success color for all badges.
                    ->getStateUsing(function (Tour $record) {
                        return $record->pricingTiers->map(fn ($tier) => $tier->is_base ? 'Base ' : $tier->offer_name);
                    }),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->boolean()
                    ->trueLabel('Active Only')
                    ->falseLabel('Paused Only')
                    ->native(false),
                    
                Tables\Filters\SelectFilter::make('location')
                    ->label('Location')
                    ->options(function () {
                        return \App\Models\Tour::whereNotNull('location')
                            ->where('location', '!=', '')
                            ->distinct()
                            ->pluck('location', 'location')
                            ->toArray();
                    })
                    ->searchable()
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('pricingTiers');
    }

    public static function getRelations(): array 
    { 
        return [
            RelationManagers\PricingTiersRelationManager::class,
            RelationManagers\DeparturesRelationManager::class,
        ]; 
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTours::route('/'),
            'create' => Pages\CreateTour::route('/create'),
            'view' => Pages\ViewTour::route('/{record}'),
            'edit' => Pages\EditTour::route('/{record}/edit'),
        ];
    }
}