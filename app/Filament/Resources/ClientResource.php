<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.communication');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.clients');
    }

    public static function getModelLabel(): string
    {
        return __('admin.models.client');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.clients');
    }

    // Disable the creation of new clients manually from the admin panel
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(fn () => __('admin.sections.client_info') ?? 'Client Information')
                    ->schema([
                        Forms\Components\TextInput::make('full_name') 
                            ->label(fn () => __('admin.columns.client_name') ?? 'Full Name')
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('whatsapp_number') 
                            ->label(fn () => __('admin.columns.whatsapp') ?? 'WhatsApp Number')
                            ->required()
                            ->maxLength(50),
                            
                        Forms\Components\TextInput::make('nationality')
                            ->label(fn () => __('admin.columns.nationality') ?? 'Nationality')
                            ->maxLength(2)
                            ->helperText('2-letter country code (e.g., EG, GB, RU)'),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name') 
                    ->label(fn () => __('admin.columns.client_name') ?? 'Client Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
               Tables\Columns\TextColumn::make('whatsapp_number') 
                    ->label(fn () => __('admin.columns.whatsapp') ?? 'WhatsApp')
                    ->searchable()
                    ->icon('heroicon-m-chat-bubble-oval-left-ellipsis')
                    ->color('success')
                    ->url(fn (Client $record): string => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $record->whatsapp_number))
                    ->openUrlInNewTab(),
                    
                Tables\Columns\TextColumn::make('nationality')
                    ->label(fn () => __('admin.columns.nationality') ?? 'Nationality')
                    ->searchable()
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('bookings_count')
                    ->counts('bookings')
                    ->label(fn () => __('admin.columns.total_bookings') ?? 'Total Bookings')
                    ->badge(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label(fn () => __('admin.columns.last_updated') ?? 'Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListClients::route('/'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}