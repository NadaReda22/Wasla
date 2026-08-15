<?php

/**
 * Only the specific tour currently opened.
 */

namespace App\Filament\Resources\TourResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PricingTiersRelationManager extends RelationManager
{
    protected static string $relationship = 'pricingTiers';
    protected static ?string $recordTitleAttribute = 'offer_name';
    protected static ?string $title = 'Pricing Configurations';

    public function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Select::make('vehicle_type')
                ->options([
                    'microbus' => 'Shared Microbus',
                    'private' => 'Private Car',
                ])
                ->live()
                ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                    if ($get('is_base')) { $set('pax_count', $state === 'private' ? 3 : 1); }
                })
                ->required(),

            Forms\Components\Select::make('trip_type')
                ->options([
                    'one_way' => 'One Way',
                    'round_trip' => 'Round Trip',
                ])
                ->live()
                ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                    if ($get('is_base')) {
                        $set('offer_name', $state === 'round_trip' ? 'Standard Round Trip' : 'Standard One Way');
                    }
                })
                ->required(),

            Forms\Components\Toggle::make('is_base')
                ->label('Is Base Price?')
                ->live()
                ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                    if ($state) {
                        $trip = $get('trip_type') === 'round_trip' ? 'Round Trip' : 'One Way';
                        $set('offer_name', 'Standard ' . $trip);
                        $set('pax_count', $get('vehicle_type') === 'private' ? 3 : 1);
                    }
                })
                ->columnSpanFull(),

            Forms\Components\TextInput::make('offer_name')
                ->label('Offer / Base Name')
                ->readOnly(fn (Forms\Get $get) => $get('is_base'))
                ->dehydrated(true)//Don't leave this field out of that data. Include its current value specially for the base tier, which is auto-generated.
                ->required()
                ->helperText('e.g., Couple Round Trip, Family Pack'),

            Forms\Components\TextInput::make('pax_count')
                ->label('Number of Persons')
                ->numeric()
                ->minValue(1)
                ->readOnly(fn (Forms\Get $get) => $get('is_base'))
                ->dehydrated(true)//Don't leave this field out of that data. Include its current value specially for the base tier, which is auto-generated.
                ->required(),

            Forms\Components\TextInput::make('deposit_amount')
                ->label('Deposit Amount')
                ->numeric()
                ->prefix('$')
                ->required(),

            Forms\Components\TextInput::make('total_price')
                ->label('Total Price')
                ->numeric()
                ->prefix('$')
                ->required(),

            Forms\Components\Toggle::make('is_active')->default(true),
      
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('offer_name')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Price Type')
                    ->badge()
                    ->color(fn (Model $record) => $record->is_base ? 'info' : 'success')
                    ->getStateUsing(fn (Model $record) => $record->offer_name),

                Tables\Columns\TextColumn::make('vehicle_type')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('trip_type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'one_way' ? 'One Way' : 'Round Trip')
                    ->color(fn ($state) => $state === 'one_way' ? 'warning' : 'success'),

                Tables\Columns\TextColumn::make('pax_count')->label('Pax'),
                Tables\Columns\TextColumn::make('deposit_amount')->money('USD'),
                Tables\Columns\TextColumn::make('total_price')->money('USD'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_base')->label('Base vs Offer'),
                Tables\Filters\SelectFilter::make('vehicle_type')->options(['microbus' => 'Microbus', 'private' => 'Private Car']),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}