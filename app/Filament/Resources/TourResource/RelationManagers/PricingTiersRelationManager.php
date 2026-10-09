<?php

namespace App\Filament\Resources\TourResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class PricingTiersRelationManager extends RelationManager
{
    protected static string $relationship = 'pricingTiers';
    protected static ?string $recordTitleAttribute = 'offer_name';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.sections.pricing_configurations') ?? 'Pricing Configurations';
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('vehicle_type')
                ->label(fn () => __('admin.fields.type') ?? 'Vehicle Type')
                ->options([
                    'microbus' => __('admin.options.shared_microbus') ?? 'Shared Microbus', 
                    'private' => __('admin.options.private_car') ?? 'Private Car'
                ])
                ->live()
                ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                    if ($get('is_base')) { $set('pax_count', $state === 'private' ? 3 : 1); }
                })->required(),

            Forms\Components\Select::make('trip_type')
                ->label(fn () => __('admin.columns.trip_type') ?? 'Trip Type')
                ->options([
                    'one_way' => __('admin.options.one_way') ?? 'One Way', 
                    'round_trip' => __('admin.options.round_trip') ?? 'Round Trip'
                ])
                ->live()
                ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                    if ($get('is_base')) {
                        $set('offer_name', $state === 'round_trip' ? __('admin.options.standard_round_trip') : __('admin.options.standard_one_way'));
                    }
                })->required(),

            Forms\Components\Toggle::make('is_base')
                ->label(fn () => __('admin.fields.is_base_price') ?? 'Is Base Price?')
                ->live()
                ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                    if ($state) {
                        $trip = $get('trip_type') === 'round_trip' ? __('admin.options.standard_round_trip') : __('admin.options.standard_one_way');
                        $set('offer_name', $trip);
                        $set('pax_count', $get('vehicle_type') === 'private' ? 3 : 1);
                    }
                })->columnSpanFull(),

            Forms\Components\TextInput::make('offer_name')
                ->label(fn () => __('admin.fields.offer_name') ?? 'Offer Name')
                ->readOnly(fn (Forms\Get $get) => $get('is_base'))
                ->dehydrated(true)->required(),

            Forms\Components\TextInput::make('pax_count')
                ->label(fn () => __('admin.fields.pax_count') ?? 'Pax Count')
                ->numeric()->minValue(1)
                ->readOnly(fn (Forms\Get $get) => $get('is_base'))
                ->dehydrated(true)->required(),

            Forms\Components\TextInput::make('deposit_amount')
                ->label(fn () => __('admin.fields.deposit_amount') ?? 'Deposit Amount')
                ->numeric()->prefix('$')->required(),
                
            Forms\Components\TextInput::make('total_price')
                ->label(fn () => __('admin.fields.total_price') ?? 'Total Price')
                ->numeric()->prefix('$')->required(),
                
            Forms\Components\Toggle::make('is_active')
                ->label(fn () => __('admin.fields.active') ?? 'Active')
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('offer_name')
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]))
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label(fn () => __('admin.columns.price_type') ?? 'Price Type')
                    ->badge()
                    ->color(fn (Model $record) => $record->is_base ? 'info' : 'success')
                    ->getStateUsing(fn (Model $record) => $record->offer_name),

                Tables\Columns\TextColumn::make('vehicle_type')
                    ->label(fn () => __('admin.columns.vehicle_type') ?? 'Vehicle Type')
                    ->badge()->color('gray')
                    ->formatStateUsing(fn ($state) => $state === 'private' ? __('admin.options.private_car') : __('admin.options.shared_microbus')),

                Tables\Columns\TextColumn::make('trip_type')
                    ->label(fn () => __('admin.columns.trip_type') ?? 'Trip Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'one_way' ? __('admin.options.one_way') : __('admin.options.round_trip'))
                    ->color(fn ($state) => $state === 'one_way' ? 'warning' : 'success'),

                Tables\Columns\TextColumn::make('pax_count')->label(fn () => __('admin.columns.pax') ?? 'Pax'),
                Tables\Columns\TextColumn::make('deposit_amount')->label(fn () => __('admin.fields.deposit_amount') ?? 'Deposit')->money('USD'),
                Tables\Columns\TextColumn::make('total_price')->label(fn () => __('admin.fields.total_price') ?? 'Total')->money('USD'),
                Tables\Columns\IconColumn::make('is_active')->label(fn () => __('admin.columns.active') ?? 'Active')->boolean(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([ Tables\Actions\CreateAction::make() ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function (Model $record, array $data): Model {
                        $hasBookings = \App\Models\Booking::where('tour_pricing_tier_id', $record->id)->exists();
                        if ($hasBookings) {
                            $record->delete(); 
                            Notification::make()->title(__('admin.notifications.price_updated_archived'))->success()->send();
                            return $record->tour->pricingTiers()->create($data); 
                        }
                        $record->update($data);
                        return $record;
                    }),

                Tables\Actions\DeleteAction::make()
                    ->successNotificationTitle(fn () => __('admin.notifications.deleted_successfully')),
                    
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->successNotificationTitle(fn () => __('admin.notifications.deleted_successfully')),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}