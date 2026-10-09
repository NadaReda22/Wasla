<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.finance');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.payments') ?? 'Payments';
    }

    public static function getModelLabel(): string
    {
        return __('admin.models.payment') ?? 'Payment';
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.payments') ?? 'Payments';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transaction Details')->schema([
                    Forms\Components\TextInput::make('kashier_order_id')->label('Order ID'),
                    Forms\Components\TextInput::make('amount')
                        ->numeric()
                        ->prefix('$'),
                    Forms\Components\TextInput::make('currency'), 
                    Forms\Components\TextInput::make('status'),
                ])->columns(2)->disabled(),

                Forms\Components\Section::make('Booking & Client Information')->schema([
                    Forms\Components\TextInput::make('v_booking_ref')
                        ->label('Booking Ref')
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->booking_reference),
                        
                    Forms\Components\TextInput::make('v_client_id')
                        ->label('Client ID')
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->client_id),
                        
                    Forms\Components\TextInput::make('v_client_name')
                        ->label('Client Name')
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->client?->full_name),
                        
                    Forms\Components\TextInput::make('v_whatsapp')
                        ->label('WhatsApp')
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->client?->whatsapp_number),
                        
                    Forms\Components\TextInput::make('v_email')
                        ->label('Email')
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->client?->email),
                        
                    Forms\Components\TextInput::make('v_nationality')
                        ->label('Nationality')
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->client?->nationality),
                        
                    Forms\Components\TextInput::make('v_tour')
                        ->label('Tour')
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->tour?->name_en),
                        
                    Forms\Components\TextInput::make('v_travel_date')
                        ->label('Tour Date')
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->booking_date ? \Carbon\Carbon::parse($record->booking->booking_date)->format('M d, Y') : null),
                        
                    Forms\Components\TextInput::make('v_passengers')
                        ->label('Passengers')
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->seats_count),
                        
                    Forms\Components\TextInput::make('v_pickup')
                        ->label('Hotel / Pickup')
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->pickup_name),
                        
                    Forms\Components\TextInput::make('v_room')
                        ->label('Room')
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->room_number),
                        
                    Forms\Components\TextInput::make('v_reception')
                        ->label('Reception')
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->reception_number),
                ])->columns(3)->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kashier_order_id')
                    ->label('Order ID')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('booking.booking_reference')
                    ->label('Booking Ref')
                    ->searchable()
                    ->sortable()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('booking.client.id')
                    ->label('Client ID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('booking.client.full_name')
                    ->label('Client Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'paid' => 'Paid',
                        'pending' => 'Pending',
                        'failed' => 'Failed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
        ];
    }
}