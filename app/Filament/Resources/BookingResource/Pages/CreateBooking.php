<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate the booking reference before saving to the database
        $data['booking_reference'] = 'BKG-' . strtoupper(Str::random(8));
        
        // Default payment status for manual admin bookings
        if (!isset($data['payment_status'])) {
            $data['payment_status'] = 'paid'; // Or 'pending' if you prefer
        }

        return $data;
    }
}