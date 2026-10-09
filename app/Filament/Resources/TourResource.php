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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;

class TourResource extends Resource
{
    protected static ?string $model = Tour::class;
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.operations');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.tours');
    }

    public static function getModelLabel(): string
    {
        return __('admin.models.tour');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.tours');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(fn () => __('admin.sections.basic_info') ?? 'Basic Information')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name_en')
                                    ->label(fn () => __('admin.fields.name_en') ?? 'Tour Name (English)')
                                    ->required(),
                                    
                                Forms\Components\TextInput::make('name_ar')
                                    ->label(fn () => __('admin.fields.name_ar') ?? 'Tour Name (Arabic)'),

                                Forms\Components\TextInput::make('name_ru')
                                    ->label(fn () => __('admin.fields.name_ru') ?? 'Tour Name (Russian)'),
                                    
                                Forms\Components\TextInput::make('slug')
                                    ->label(fn () => __('admin.fields.slug') ?? 'Slug URL')
                                    ->required()
                                    ->unique(ignoreRecord: true),
                                    
                                Forms\Components\TextInput::make('location')
                                    ->label(fn () => __('admin.fields.location') ?? 'Location / Area')
                                    ->required(),
                                    
                                Forms\Components\TextInput::make('tag_name')
                                    ->label(fn () => __('admin.fields.tag_name') ?? 'Highlight Badge Text'),
                                    
                                Forms\Components\Select::make('tag_color_class')
                                    ->label(fn () => __('admin.fields.badge_color') ?? 'Badge Color')
                                    ->options([
                                        'bg-blue-500 text-white' => '🔵 Blue',
                                        'bg-red-500 text-white' => '🔴 Red',
                                        'bg-green-500 text-white' => '🟢 Green',
                                        'bg-yellow-500 text-black' => '🟡 Yellow',
                                        'bg-gray-800 text-white' => '⚫ Dark Navy',
                                    ])
                                    ->native(false),
                            ]),
                            
                        Forms\Components\FileUpload::make('image_url')
                            ->label(fn () => __('admin.fields.tour_image') ?? 'Tour Image')
                            ->directory('tours')
                            ->preserveFilenames()
                            ->required(),
                            
                        Forms\Components\RichEditor::make('description_en')
                            ->label(fn () => __('admin.fields.description_en') ?? 'Description (English)')
                            ->required()
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'strike',
                                'bulletList', 'orderedList', 'h2', 'h3',
                                'link', 'undo', 'redo',
                            ]),
                            
                        Forms\Components\RichEditor::make('description_ar')
                            ->label(fn () => __('admin.fields.description_ar') ?? 'Description (Arabic)')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'strike',
                                'bulletList', 'orderedList', 'h2', 'h3',
                                'link', 'undo', 'redo',
                            ]),

                        Forms\Components\RichEditor::make('description_ru')
                            ->label(fn () => __('admin.fields.description_ru') ?? 'Description (Russian)')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'strike',
                                'bulletList', 'orderedList', 'h2', 'h3',
                                'link', 'undo', 'redo',
                            ]),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label(fn () => __('admin.fields.is_active_website') ?? 'Active on Website')
                                    ->default(true),

                                Forms\Components\Toggle::make('is_bus_staying')
                                    ->label(fn () => __('admin.fields.is_bus_staying') ?? 'Bus Stays with Group')
                                    ->default(false),
                            ]),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make(fn () => __('admin.sections.tour_details') ?? 'Tour Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('name_en')->label(fn () => __('admin.fields.name_en') ?? 'Name (EN)'),
                        Infolists\Components\TextEntry::make('name_ar')->label(fn () => __('admin.fields.name_ar') ?? 'Name (AR)'),
                        Infolists\Components\TextEntry::make('name_ru')->label(fn () => __('admin.fields.name_ru') ?? 'Name (RU)'),
                        
                        Infolists\Components\ImageEntry::make('image_url')->label(fn () => __('admin.fields.image') ?? 'Image')->circular(),
                        Infolists\Components\IconEntry::make('is_active')->boolean()->label(fn () => __('admin.fields.active') ?? 'Active'),
                        Infolists\Components\IconEntry::make('is_bus_staying')->boolean()->label(fn () => __('admin.fields.bus_stays') ?? 'Bus Stays On-Site'),
                        
                        Infolists\Components\TextEntry::make('location')->label(fn () => __('admin.fields.location') ?? 'Location'),
                        
                        Infolists\Components\TextEntry::make('tag_name')
                            ->label(fn () => __('admin.fields.tag_name') ?? 'Tag Name')
                            ->badge(),
                            
                        Infolists\Components\TextEntry::make('tag_color_class')
                            ->label(fn () => __('admin.fields.badge_color') ?? 'Tag Color Class'),
                    ])->columns(3),

                Infolists\Components\Section::make(fn () => __('admin.sections.descriptions') ?? 'Descriptions')
                    ->schema([
                        Infolists\Components\TextEntry::make('description_en')
                            ->label(fn () => __('admin.fields.description_en') ?? 'Description (English)')
                            ->columnSpanFull(),
                            
                        Infolists\Components\TextEntry::make('description_ar')
                            ->label(fn () => __('admin.fields.description_ar') ?? 'Description (Arabic)')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('description_ru')
                            ->label(fn () => __('admin.fields.description_ru') ?? 'Description (Russian)')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')->label(fn () => __('admin.columns.image') ?? 'Image')->circular(),
                Tables\Columns\TextColumn::make('name_en')->label(fn () => __('admin.columns.name_en') ?? 'Name (EN)')->searchable()->sortable(),
                
                Tables\Columns\TextColumn::make('pricing_list')
                    ->label(fn () => __('admin.columns.pricing_tiers') ?? 'Pricing Tiers')
                    ->badge()
                    ->color('success')
                    ->getStateUsing(function (Tour $record) {
                        return $record->pricingTiers->map(fn ($tier) => $tier->is_base ? 'Base ' : $tier->offer_name);
                    }),

                Tables\Columns\IconColumn::make('is_bus_staying')
                    ->label(fn () => __('admin.columns.bus_stays') ?? 'Bus Stays')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-arrow-path')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(fn () => __('admin.columns.active') ?? 'Active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(fn () => __('admin.filters.active_status') ?? 'Active Status')
                    ->boolean()
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_bus_staying')
                    ->label(fn () => __('admin.filters.bus_mode') ?? 'Bus Mode')
                    ->boolean()
                    ->native(false),
                    
                Tables\Filters\SelectFilter::make('location')
                    ->label(fn () => __('admin.filters.location') ?? 'Location')
                    ->options(function () {
                        return \App\Models\Tour::withoutGlobalScopes([SoftDeletingScope::class])
                            ->whereNotNull('location')
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
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with(['pricingTiers']); // Eager load ONLY pricing tiers since departures column is removed
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