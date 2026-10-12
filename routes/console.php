<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use App\Models\Payment;
use App\Models\TourDeparture;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

//Get hotels from OpenStreetMap and save them to the database every day at 3:00 AM
Schedule::command('hotels:fetch-osm')->dailyAt('03:00');

// Release bookings whose deposit was never paid.
Schedule::call(function () {
    Payment::where('status', 'pending')
        ->where('created_at', '<', now()->subMinutes(30))
        ->pluck('id')
        ->each(function ($id) {
            DB::transaction(function () use ($id) {
                // Lock the row so a webhook that is paying it right now can't be overwritten
                $payment = Payment::whereKey($id)->lockForUpdate()->first();

                if (!$payment || $payment->status !== 'pending') {
                    return; // paid or already handled in the meantime
                }

                $payment->update(['status' => 'failed']);
                $payment->booking?->update(['payment_status' => 'failed']);
            });
        });
})->name('expire-unpaid-payments')->everyFiveMinutes()->withoutOverlapping();



// Automatically clean up passed departures every night at 2:00 AM
Schedule::call(function () {
    $pastDepartures = TourDeparture::whereNotNull('departure_date')->get()->filter(function ($dep) {
        $timeOnly = Carbon::parse($dep->departure_time)->format('H:i:s');
        $targetDateTime = Carbon::parse(Carbon::parse($dep->departure_date)->toDateString() . ' ' . $timeOnly);
        
        return $targetDateTime->isPast();
    });

    $count = 0;
    foreach ($pastDepartures as $dep) {
        $dep->delete(); // Safely soft-deletes the record
        $count++;
    }

    info("Cleaned up {$count} past tour departures.");
})->name('cleanup-past-departures')->dailyAt('02:00');