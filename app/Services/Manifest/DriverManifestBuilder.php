<?php

namespace App\Services\Manifest;

use App\Models\Vehicle;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class DriverManifestBuilder
{
    public static function buildUrl(Vehicle $driverVehicle, string $targetDateIso): string
    {
        $driverPhone = preg_replace('/[^0-9]/', '', $driverVehicle->driver_phone);
        $driverName = explode(' ', $driverVehicle->driver_name)[0] ?? 'Driver'; 
        $displayDate = Carbon::parse($targetDateIso)->format('M d');

        // Create a temporary signed URL valid for 48 hours
        $manifestLink = URL::temporarySignedRoute(
            'driver.manifest',
            now()->addHours(48),
            [
                'vehicle' => $driverVehicle->id,
                'date' => $targetDateIso
            ]
        );

        $text = "Hello {$driverName},\n\n";
        $text .= "Here is your live route manifest for {$displayDate}:\n";
        $text .= $manifestLink . "\n\n";
        $text .= "Drive safely!";

        return "https://wa.me/{$driverPhone}?text=" . rawurlencode($text);
    }
}