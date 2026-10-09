<?php

namespace App\Filament\Resources\TourResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class DeparturesRelationManager extends RelationManager
{
    protected static string $relationship = 'departures';
    protected static ?string $recordTitleAttribute = 'departure_time';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.sections.departure_schedule') ?? 'Departure Schedule';
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('departure_date')
                ->label(fn () => __('admin.fields.specific_date') ?? 'Specific Date')
                ->helperText(fn () => __('admin.fields.specific_date_help') ?? '(Leave blank to run every day at this time)'),

            Forms\Components\TimePicker::make('departure_time')
                ->label(fn () => __('admin.fields.departure_time') ?? 'Departure Time')
                ->required()
                ->displayFormat('h:i A'),

            Forms\Components\TimePicker::make('return_time')
                ->label(fn () => __('admin.fields.return_time') ?? 'Return Time')
                ->displayFormat('h:i A'),

            Forms\Components\Toggle::make('is_active')
                ->label(fn () => __('admin.fields.active') ?? 'Active')
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('departure_time')
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]))
            ->columns([
                Tables\Columns\TextColumn::make('departure_date')
                    ->label(fn () => __('admin.columns.date') ?? 'Date')
                    ->getStateUsing(fn (Model $record) => $record->departure_date
                        ? Carbon::parse($record->departure_date)->format('M d, Y')
                        : (__('admin.options.daily_recurring') ?? 'Daily Recurring'))
                    ->badge()
                    ->color(fn (Model $record) => $record->departure_date ? 'gray' : 'success'),

                Tables\Columns\TextColumn::make('departure_time')
                    ->label(fn () => __('admin.fields.departure_time') ?? 'Departure Time')
                    ->time('h:i A'),

                Tables\Columns\TextColumn::make('return_time')
                    ->label(fn () => __('admin.fields.return_time') ?? 'Return Time')
                    ->time('h:i A')
                    ->placeholder(fn () => __('admin.options.one_way') ?? 'One-way')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(fn () => __('admin.columns.active') ?? 'Active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['departure_date'] = $data['departure_date'] ?: null;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function (Model $record, array $data): Model {
                        $data['departure_date'] = $data['departure_date'] ?: null;

                        $hasBookings = \App\Models\Booking::where('tour_departure_id', $record->id)->exists();
                        if ($hasBookings) {
                            $record->delete();
                            Notification::make()->title(__('admin.notifications.schedule_updated_archived'))->success()->send();
                            return $record->tour->departures()->create($data);
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