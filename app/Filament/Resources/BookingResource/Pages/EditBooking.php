<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use App\Models\Booking;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Only show the Delete button if NO part of the booking is locked
            Actions\DeleteAction::make()
                ->hidden(fn (Booking $record) => $record->hasLockedLeg()),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return parent::handleRecordUpdate($record, $data);
        } catch (\DomainException $e) {
            Notification::make()
                ->danger()
                ->title(__('admin.booking.notifications.locked_title') ?? 'Booking is locked')
                ->body('Cannot modify these fields because the booking is partially or fully locked.')
                ->send();

            $this->halt();
        }
    }
}