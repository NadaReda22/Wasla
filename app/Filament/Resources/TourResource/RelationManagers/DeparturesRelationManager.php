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

class DeparturesRelationManager extends RelationManager
{
    protected static string $relationship = 'departures';
    protected static ?string $recordTitleAttribute = 'departure_time';
    protected static ?string $title = 'Departure Schedule';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('departure_date')
                ->label('Specific Date (Leave blank for Daily)'),
                
            Forms\Components\TimePicker::make('departure_time')
                ->required()
                ->displayFormat('H:i'),
                
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('departure_time')
            ->columns([
                Tables\Columns\TextColumn::make('departure_date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->default('Daily Recurring')
                    ->badge()
                    ->color(fn ($state) => $state === 'Daily Recurring' ? 'success' : 'gray'),
                    
                Tables\Columns\TextColumn::make('departure_time')->time('H:i'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                //
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