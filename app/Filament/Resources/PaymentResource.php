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
                Forms\Components\Section::make(__('admin.payments.transaction_details'))->schema([
                    Forms\Components\TextInput::make('kashier_order_id')->label(__('admin.payments.order_id')),
                    Forms\Components\TextInput::make('amount')
                        ->label(__('admin.payments.amount'))
                        ->numeric()
                        ->prefix('$'),
                    Forms\Components\TextInput::make('currency')->label(__('admin.payments.currency')), 
                    Forms\Components\TextInput::make('status')->label(__('admin.payments.status')),
                ])->columns(2)->disabled(),

                Forms\Components\Section::make(__('admin.payments.booking_client_info'))->schema([
                    Forms\Components\TextInput::make('v_booking_ref')
                        ->label(__('admin.payments.booking_ref'))
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->booking_reference),
                        
                    Forms\Components\TextInput::make('v_client_id')
                        ->label(__('admin.payments.client_id'))
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->client_id),
                        
                    Forms\Components\TextInput::make('v_client_name')
                        ->label(__('admin.payments.client_name'))
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->client?->full_name),
                        
                    Forms\Components\TextInput::make('v_whatsapp')
                        ->label(__('admin.payments.whatsapp'))
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->client?->whatsapp_number),
                        
                    Forms\Components\TextInput::make('v_email')
                        ->label(__('admin.payments.email'))
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->client?->email),
                        
                    Forms\Components\TextInput::make('v_nationality')
                        ->label(__('admin.payments.nationality'))
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->client?->nationality),
                        
                    Forms\Components\TextInput::make('v_tour')
                        ->label(__('admin.payments.tour'))
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->tour?->name_en),
                        
                    Forms\Components\TextInput::make('v_travel_date')
                        ->label(__('admin.payments.tour_date'))
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->booking_date ? \Carbon\Carbon::parse($record->booking->booking_date)->format('M d, Y') : null),
                        
                    Forms\Components\TextInput::make('v_passengers')
                        ->label(__('admin.payments.passengers'))
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->seats_count),
                        
                    Forms\Components\TextInput::make('v_pickup')
                        ->label(__('admin.payments.pickup'))
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->pickup_name),
                        
                    Forms\Components\TextInput::make('v_room')
                        ->label(__('admin.payments.room'))
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->room_number),
                        
                    Forms\Components\TextInput::make('v_reception')
                        ->label(__('admin.payments.reception'))
                        ->formatStateUsing(fn (?Payment $record) => $record?->booking?->reception_number),
                ])->columns(3)->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kashier_order_id')
                    ->label(__('admin.payments.order_id'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('booking.booking_reference')
                    ->label(__('admin.payments.booking_ref'))
                    ->searchable()
                    ->sortable()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('booking.client.id')
                    ->label(__('admin.payments.client_id'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('booking.client.full_name')
                    ->label(__('admin.payments.client_name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('admin.payments.amount'))
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.payments.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __('admin.payments.statuses.' . $state))
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.payments.date'))
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('admin.payments.status'))
                    ->options([
                        'paid' => __('admin.payments.statuses.paid'),
                        'pending' => __('admin.payments.statuses.pending'),
                        'failed' => __('admin.payments.statuses.failed'),
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