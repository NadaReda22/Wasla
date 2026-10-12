<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DriverManifestController extends Controller
{
    public function show(Request $request, Vehicle $vehicle, $date)
    {
        // Fetch bookings for this driver on this date, 
        // ensuring they are paid and the leg assigned to this driver is locked.
        $bookings = Booking::with(['client', 'tour', 'departure', 'area'])
            ->whereDate('booking_date', $date)
            ->where('payment_status', 'paid') 
            ->where(function ($q) use ($vehicle) {
                // Return booking IF it's assigned to this driver for outbound AND outbound is locked
                $q->where(function ($subQ) use ($vehicle) {
                    $subQ->where('outbound_vehicle_id', $vehicle->id)
                         ->where('is_outbound_ensured', true);
                })
                // OR IF it's assigned to this driver for return AND return is locked
                ->orWhere(function ($subQ) use ($vehicle) {
                    $subQ->where('return_vehicle_id', $vehicle->id)
                         ->where('is_return_ensured', true);
                });
            })
            ->get();

        if ($bookings->isEmpty()) {
            abort(404, 'No valid (paid and ensured) bookings found for this route today.');
        }

        // Group them exactly as we did in the text manifest
        $groupedByTour = $bookings->groupBy('tour_id')->sortBy(function ($group) { 
            return $group->first()->departure?->departure_time ?? '99:99'; 
        });

        $displayDate = Carbon::parse($date)->format('l, M d, Y');

        return view('manifest.driver', compact('vehicle', 'groupedByTour', 'displayDate', 'date'));
    }
}