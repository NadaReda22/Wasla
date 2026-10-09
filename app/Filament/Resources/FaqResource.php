<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages;
use App\Models\Faq;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.faqs');
    }

    public static function getModelLabel(): string
    {
        return __('admin.models.faq');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.faqs');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(fn () => __('admin.sections.faq_en'))
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('question_en')
                            ->label(fn () => __('admin.fields.question_en'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('answer_en')
                            ->label(fn () => __('admin.fields.answer_en'))
                            ->required()
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'link',
                                'bulletList', 'orderedList',
                            ]),
                    ]),

                Forms\Components\Section::make(fn () => __('admin.sections.faq_ru'))
                    ->icon('heroicon-o-language')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\TextInput::make('question_ru')
                            ->label(fn () => __('admin.fields.question_ru'))
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('answer_ru')
                            ->label(fn () => __('admin.fields.answer_ru'))
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'link',
                                'bulletList', 'orderedList',
                            ]),
                    ]),

                Forms\Components\Section::make(fn () => __('admin.groups.settings'))
                    ->icon('heroicon-o-cog')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('sort_order')
                                    ->label(fn () => __('admin.fields.sort_order'))
                                    ->numeric()
                                    ->default(0)
                                    ->helperText(fn () => __('admin.fields.sort_order_help')),
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
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(fn () => __('admin.columns.order'))
                    ->sortable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('question_en')
                    ->label(fn () => __('admin.columns.question'))
                    ->searchable()
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(fn () => __('admin.columns.active'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(fn () => __('admin.columns.last_updated'))
                    ->formatStateUsing(fn ($state) => $state?->translatedFormat('d M Y'))
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
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageFaqs::route('/'),
        ];
    }
}