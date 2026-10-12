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
        $data['booking_reference'] = 'BKG-' . strtoupper(Str::random(8));
        $data['payment_status'] = 'paid';
        $data['deposit_paid'] = 0;

        return $data;
    }
}