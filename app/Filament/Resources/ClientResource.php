<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Booking;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

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

    /** True when any booking still belongs to one of these clients. */
    private static function hasBookings(array $clientIds): bool
    {
        return Booking::query()->whereIn('client_id', $clientIds)->exists();
    }

    private static function notifyDeleteBlocked(): void
    {
        Notification::make()
            ->danger()
            ->title(__('admin.client.delete_blocked_title'))
            ->body(__('admin.client.delete_blocked_body'))
            ->send();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(fn () => __('admin.sections.client_info'))
                    ->schema([
                        Forms\Components\TextInput::make('full_name')
                            ->label(fn () => __('admin.columns.client_name'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('whatsapp_number')
                            ->label(fn () => __('admin.columns.whatsapp'))
                            ->required()
                            ->maxLength(50),

                        Forms\Components\TextInput::make('email')
                            ->label(fn () => __('admin.booking.email_address'))
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('nationality')
                            ->label(fn () => __('admin.columns.nationality'))
                            ->maxLength(2)
                            ->helperText(fn () => __('admin.client.nationality_help')),
                    ])->columns(2)
            ]);
    }

    /** The "show" page: client details + all booking references of this client. */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make(fn () => __('admin.sections.client_info'))
                    ->schema([
                        Infolists\Components\TextEntry::make('full_name')
                            ->label(fn () => __('admin.columns.client_name'))
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('whatsapp_number')
                            ->label(fn () => __('admin.columns.whatsapp'))
                            ->icon('heroicon-m-chat-bubble-oval-left-ellipsis')
                            ->color('success')
                            ->url(fn (Client $record): string => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $record->whatsapp_number))
                            ->openUrlInNewTab(),

                        Infolists\Components\TextEntry::make('email')
                            ->label(fn () => __('admin.booking.email_address'))
                            ->icon('heroicon-m-envelope')
                            ->copyable()
                            ->placeholder('—'),

                        Infolists\Components\TextEntry::make('nationality')
                            ->label(fn () => __('admin.columns.nationality'))
                            ->badge()
                            ->color('info')
                            ->placeholder('—'),
                    ])->columns(2),

                Infolists\Components\Section::make(fn () => __('admin.client.booking_references'))
                    ->schema([
                        Infolists\Components\TextEntry::make('bookings.booking_reference')
                            ->hiddenLabel()
                            ->badge()
                            ->color('primary')
                            ->copyable()
                            ->placeholder(fn () => __('admin.client.no_bookings')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label(fn () => __('admin.columns.client_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('whatsapp_number')
                    ->label(fn () => __('admin.columns.whatsapp'))
                    ->searchable()
                    ->icon('heroicon-m-chat-bubble-oval-left-ellipsis')
                    ->color('success')
                    ->url(fn (Client $record): string => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $record->whatsapp_number))
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('email')
                    ->label(fn () => __('admin.booking.email_address'))
                    ->icon('heroicon-m-envelope')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('nationality')
                    ->label(fn () => __('admin.columns.nationality'))
                    ->searchable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('bookings_count')
                    ->counts('bookings')
                    ->label(fn () => __('admin.columns.total_bookings'))
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(fn () => __('admin.columns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Client $record): string => static::getUrl('view', ['record' => $record])),

                Tables\Actions\EditAction::make()
                    ->label(fn () => __('admin.actions.edit')),

                Tables\Actions\DeleteAction::make()
                    ->label(fn () => __('admin.actions.delete'))
                    ->before(function (Client $record, Tables\Actions\DeleteAction $action) {
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
            'index' => Pages\ListClients::route('/'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}