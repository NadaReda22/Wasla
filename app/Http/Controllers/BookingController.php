<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Tour;
use App\Models\TourDeparture;
use App\Models\TourPricingTier;
use App\Models\Area;
use App\Models\Hotel;
use App\Models\Vehicle;
use App\Services\KashierPaymentService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function index()
    {
        return view('pages.booking');
    }

    public function create(Request $request)
    {
        $routes = Cache::remember('frontend_booking_routes', 60, function () {
            $tours = Tour::where('is_active', true)->with([
                'pricingTiers' => function ($q) { $q->where('is_active', true); },
                'departures' => function ($q) { $q->where('is_active', true)->orderBy('departure_time'); }
            ])->get();

            return $tours->map(function ($tour) {
                $schedule = [];
                $today = Carbon::today();

                if ($tour->departures->isEmpty()) {
                    for ($i = 0; $i <= 4; $i++) {
                        $targetDate = $today->copy()->addDays($i);
                        $targetDateTime = Carbon::parse($targetDate->toDateString() . ' 09:00:00');
                        
                        if ($targetDateTime->isFuture()) {
                            $schedule[$targetDate->toDateString()] = ['09:00 AM'];
                        }
                    }
                } else {
                    foreach ($tour->departures as $dep) {
                        $depTime = Carbon::parse($dep->departure_time)->format('h:i A');

                        if ($dep->return_time) {
                            $retTime = Carbon::parse($dep->return_time)->format('h:i A');
                            $formattedTime = "{$depTime} (Ret: {$retTime})";
                        } else {
                            $formattedTime = $depTime;
                        }

                        $timeOnly = Carbon::parse($dep->departure_time)->format('H:i:s');

                        if (is_null($dep->departure_date)) {
                            for ($i = 0; $i <= 4; $i++) {
                                $targetDate = $today->copy()->addDays($i);
                                $targetDateTime = Carbon::parse($targetDate->toDateString() . ' ' . $timeOnly);
                                
                                if ($targetDateTime->isFuture()) {
                                    $schedule[$targetDate->toDateString()][] = $formattedTime;
                                }
                            }
                        } else {
                            $targetDate = Carbon::parse($dep->departure_date);
                            $targetDateTime = Carbon::parse($targetDate->toDateString() . ' ' . $timeOnly);

                            if ($targetDateTime->isFuture()) {
                                $schedule[$targetDate->toDateString()][] = $formattedTime;
                            }
                        }
                    }

                    foreach ($schedule as $date => $times) {
                        $schedule[$date] = collect($times)->unique()->sort()->values()->toArray();
                    }
                    ksort($schedule);
                }

                $tiers = $tour->pricingTiers->map(function ($t) use ($tour) {
                    $baseTier = $tour->pricingTiers->where('is_base', true)->where('vehicle_type', $t->vehicle_type)->where('trip_type', $t->trip_type)->first();

                    if ($t->vehicle_type === 'private') {
                        $originalPrice = $baseTier ? $baseTier->total_price : $t->total_price;
                        $originalDeposit = $baseTier ? $baseTier->deposit_amount : $t->deposit_amount;
                    } else {
                        $originalPrice = $baseTier ? $baseTier->total_price * $t->pax_count : $t->total_price;
                        $originalDeposit = $baseTier ? $baseTier->deposit_amount * $t->pax_count : $t->deposit_amount;
                    }

                    $discount = 0;
                    if (!$t->is_base && $originalPrice > $t->total_price) {
                        $discount = round((($originalPrice - $t->total_price) / $originalPrice) * 100);
                    }

                    return [
                        'id' => $t->id,
                        'name' => $t->is_base ? 'Standard Rate' : $t->offer_name,
                        'is_base' => $t->is_base,
                        'type' => $t->trip_type,
                        'vehicle' => $t->vehicle_type,
                        'pax' => $t->pax_count,
                        'price' => (float) $t->total_price,
                        'deposit' => (float) $t->deposit_amount,
                        'original_price' => (float) $originalPrice,
                        'original_deposit' => (float) $originalDeposit,
                        'discount' => $discount,
                    ];
                })->values()->toArray();

                return [
                    'id' => $tour->slug,
                    'name' => $tour->name_en,     
                    'name_ar' => $tour->name_ar,    
                    'is_bus_staying' => (bool) $tour->is_bus_staying,
                    'schedule' => $schedule,
                    'tiers' => $tiers,
                ];
            })
            ->filter(function ($tourData) {
                return !empty($tourData['schedule']) && !empty($tourData['tiers']);
            })->values()->toArray();
        });

        $areas = Cache::remember('frontend_booking_areas', 60, function () {
            return Area::where('is_active', true)
                ->get(['id', 'name_en', 'name_ar', 'color', 'latitude', 'longitude']);
        });

        $limits = Cache::remember('frontend_vehicle_limits', 60, function () {
            $maxPrivate = Vehicle::where('is_active', true)->where('type', 'private_car')->max('capacity');
            $maxMicrobus = Vehicle::where('is_active', true)->where('type', 'microbus')->max('capacity');

            return [
                'private' => $maxPrivate ?: 4,  
                'microbus' => $maxMicrobus ?: 14 
            ];
        });

        return view('pages.booking', compact('routes', 'areas', 'limits'));
    }

    public function store(Request $request, KashierPaymentService $kashierService)
    {
        $validated = $request->validate([
            'routeId'         => 'required|string',
            'departureDate'   => 'required|date|after_or_equal:today',
            'departureTime'   => 'required|string',
            'passengers'      => 'required|integer|min:1',
            'leadName'        => 'required|string|max:255',
            'email'           => 'nullable|email|max:255',
            'whatsapp'        => 'required|string|max:50',
            'nationality'     => 'required|string|size:2',
            'roomNumber'      => 'required|string|max:50', 
            'receptionNumber' => 'required|string|max:50', 
            'hotel'           => 'required|string',
            'tripType'        => 'required|in:one-way,round-trip',
            'vehicleType'     => 'required|in:microbus,private',
            'area_id'         => 'required|exists:areas,id',
            'pickupLat'       => 'nullable|numeric',
            'pickupLng'       => 'nullable|numeric',
            'pickupAddress'   => 'nullable|string',
            'paymentMethod'   => 'required|in:kashier',
        ]);

        [$booking, $payment, $depositDue] = DB::transaction(function () use ($validated) {

            $email = $validated['email'] ?? null;
            $whatsapp = $validated['whatsapp'];

            $client = Client::where('whatsapp_number', $whatsapp)
                ->when($email, function ($query) use ($email) {
                    $query->orWhere('email', $email);
                })->first();

            if ($client) {
                $client->update([
                    'whatsapp_number' => $whatsapp,
                    'email'           => $email,
                    'full_name'       => $validated['leadName'],
                    'nationality'     => $validated['nationality'],
                ]);
            } else {
                $client = Client::create([
                    'whatsapp_number' => $whatsapp,
                    'email'           => $email,
                    'full_name'       => $validated['leadName'],
                    'nationality'     => $validated['nationality'],
                ]);
            }

            $tour = Tour::where('slug', $validated['routeId'])->orWhere('id', $validated['routeId'])->first();
            if (!$tour) {
                abort(422, 'This tour is no longer available.');
            }

            $rawTimeStr = trim(explode('(', $validated['departureTime'])[0]);
            $dbTimeSearch = Carbon::parse($rawTimeStr)->format('H:i');

            $requestedDateTime = Carbon::parse($validated['departureDate'] . ' ' . $dbTimeSearch);
            if ($requestedDateTime->isPast()) {
                abort(422, 'The selected departure time has already passed.');
            }

            $departure = TourDeparture::where('tour_id', $tour->id)
                ->where('is_active', true)
                ->where('departure_time', 'LIKE', $dbTimeSearch . '%')
                ->where(function ($query) use ($validated) {
                    $query->whereDate('departure_date', $validated['departureDate'])
                          ->orWhereNull('departure_date');
                })
                ->orderByRaw('departure_date IS NULL')
                ->first();

            $hasAnyDeparture = TourDeparture::where('tour_id', $tour->id)->where('is_active', true)->exists();

            if (!$departure && !$hasAnyDeparture) {
                if ($dbTimeSearch !== '09:00') {
                    abort(422, 'This tour is available daily at 09:00 AM.');
                }
                $departureId = null;
            } elseif (!$departure) {
                abort(422, "The selected departure time ({$rawTimeStr}) is not available for this date.");
            } else {
                $departureId = $departure->id;
            }

            $vehicleTypeMap = $validated['vehicleType'];

            $maxFleetCapacity = Vehicle::where('is_active', true)
                ->when($vehicleTypeMap === 'private', fn($q) => $q->where('type', 'private_car'))
                ->when($vehicleTypeMap === 'microbus', fn($q) => $q->where('type', 'microbus'))
                ->max('capacity');

            if (!$maxFleetCapacity) {
                $maxFleetCapacity = $vehicleTypeMap === 'private' ? 4 : 14;
            }

            if ($validated['passengers'] > $maxFleetCapacity) {
                if ($vehicleTypeMap === 'private') {
                    abort(422, "A private car can accommodate a maximum of {$maxFleetCapacity} passengers.");
                } else {
                    abort(422, "Our shared microbuses can accommodate a maximum of {$maxFleetCapacity} passengers per booking.");
                }
            }

            $tripTypeMap = $validated['tripType'] === 'round-trip' ? 'round_trip' : 'one_way';

            $tiers = TourPricingTier::where('tour_id', $tour->id)
                ->where('is_active', true)
                ->where('trip_type', $tripTypeMap)
                ->where('vehicle_type', $vehicleTypeMap)
                ->orderBy('pax_count', 'desc')
                ->get();

            $baseTier = $tiers->where('is_base', true)->first();
            $offers = $tiers->where('is_base', false);

            if (!$baseTier && $offers->isEmpty()) {
                abort(422, 'This tour cannot be booked because no pricing is configured for the selected vehicle and trip type.');
            }

            $totalPrice = 0;
            $depositDue = 0;
            $remainingPax = $validated['passengers'];
            $primaryTierId = null;

            if ($vehicleTypeMap === 'private') {
                $carTier = $offers->first() ?? $baseTier;
                if (!$carTier) abort(422, 'This tour cannot be booked because no private-car pricing is configured.');
                $capacity = $carTier->pax_count;
                if ($capacity <= 0) abort(422, 'Invalid passenger capacity.');

                $carsNeeded = (int) ceil($remainingPax / $capacity);
                $totalPrice = $carsNeeded * $carTier->total_price;
                $depositDue = $carsNeeded * $carTier->deposit_amount;
                
                $primaryTierId = $carTier->id;
                $remainingPax = 0;

            } else {
                foreach ($offers as $offer) {
                    if ($remainingPax <= 0) break;
                    if ($offer->pax_count <= 0) continue;

                    $multiplier = (int) floor($remainingPax / $offer->pax_count);
                    if ($multiplier <= 0) continue;

                    if (!$primaryTierId) $primaryTierId = $offer->id;

                    $totalPrice += $offer->total_price * $multiplier;
                    $depositDue += $offer->deposit_amount * $multiplier;
                    $remainingPax -= $offer->pax_count * $multiplier;
                }

                if ($remainingPax > 0) {
                    if ($baseTier) {
                        if (!$primaryTierId) $primaryTierId = $baseTier->id;
                        $totalPrice += $remainingPax * $baseTier->total_price;
                        $depositDue += $remainingPax * $baseTier->deposit_amount;
                        $remainingPax = 0;
                    } else {
                        abort(422, 'Available offers do not cover everyone and no base price is configured.');
                    }
                }
            }

            if ($remainingPax > 0) abort(422, 'Configurations do not cover all passengers.');

            $resolvedAreaId = $validated['area_id'];
            
            $cashDue = $totalPrice - $depositDue;
            $needsPayment = $depositDue > 0;

            $booking = Booking::create([
                'booking_reference'    => 'BKG-' . strtoupper(Str::random(8)),
                'client_id'            => $client->id,
                'tour_id'              => $tour->id,
                'tour_departure_id'    => $departureId,
                'tour_pricing_tier_id' => $primaryTierId,
                'area_id'              => $resolvedAreaId,
                'room_number'          => $validated['roomNumber'],
                'reception_number'     => $validated['receptionNumber'],
                'trip_type'            => $tripTypeMap,
                'booking_date'         => $validated['departureDate'],
                'seats_count'          => $validated['passengers'],
                'total_price'          => $totalPrice,
                'deposit_paid'         => 0, 
                'cash_due_on_board'    => $cashDue,
                'payment_status'       => $needsPayment ? 'pending' : 'paid',
                'pickup_name'          => $validated['hotel'],
                'pickup_lat'           => $validated['pickupLat'] ?? null,
                'pickup_lng'           => $validated['pickupLng'] ?? null,
                'pickup_address'       => $validated['pickupAddress'] ?? null,
                'is_cash_on_board_paid'=> false,
                'outbound_vehicle_id'  => null,
                'return_vehicle_id'    => null,
            ]);

            $payment = null;
            if ($needsPayment) {
                $payment = $booking->payment()->create([
                    'kashier_order_id' => 'KSH-' . strtoupper(Str::random(10)) . '-' . $booking->id,
                    'amount'           => $depositDue,
                    'currency'         => config('services.kashier.currency', 'USD'),
                    'status'           => 'pending',
                ]);
            }

            return [$booking, $payment, $depositDue];
        });

        if (!$payment) {
            return response()->json([
                'success'     => true,
                'payment_url' => null,
                'reference'   => $booking->booking_reference,
            ]);
        }

        try {
            $paymentUrl = $kashierService->createPaymentLink($booking, $depositDue, $payment->kashier_order_id);
        } catch (\Throwable $e) {
            Log::error('Kashier Link Generation Failed: ' . $e->getMessage(), ['booking_id' => $booking->id]);

            $payment->update(['status' => 'failed']);
            $booking->update(['payment_status' => 'failed']);

            abort(500, 'Unable to connect to the payment gateway. Please try again later.');
        }

        return response()->json([
            'success'     => true,
            'payment_url' => $paymentUrl,
            'reference'   => $booking->booking_reference,
        ]);
    }

    public function getActiveHotels()
    {
        $hotels = Cache::remember('api_active_hotels', 60, function () {
            return Hotel::where('is_active', true)
                ->whereNotNull('area_id')
                ->with('area:id,name_en,name_ar,is_in_free_zone')
                ->get(['id', 'name_en', 'name_ar', 'keywords', 'latitude', 'longitude', 'area_id', 'extra_transfer_fee']);
        });

        return response()->json($hotels);
    }
}