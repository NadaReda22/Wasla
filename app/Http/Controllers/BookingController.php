<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Hotel;
use App\Models\Tour;
use App\Models\TourDeparture;
use App\Models\TourPricingTier;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class BookingController extends Controller
{
    public function index()
    {
        return view('pages.booking');
    }

    public function create(Request $request)
    {
        // Cache the entire booking matrix for 12 hours.
        $routes = Cache::remember('frontend_booking_routes', 60, function () {
            $tours = Tour::where('is_active', true)->with([
                'pricingTiers' => function ($q) { $q->where('is_active', true); },
                'departures' => function ($q) { $q->where('is_active', true)->orderBy('departure_time'); }
            ])->get();

            return $tours->map(function ($tour) {
                // =====================================================
                // 1. Generate Schedule - Upcoming 5 Days
                // =====================================================
                $schedule = [];
                $today = Carbon::today();

                /* If the tour has no departure records, consider it available every day at 09:00. */
                if ($tour->departures->isEmpty()) {
                    for ($i = 0; $i < 5; $i++) {
                        $date = $today->copy()->addDays($i)->toDateString();
                        $schedule[$date] = ['09:00'];
                    }
                } else {
                    /* Use the configured departure schedule. */
                    for ($i = 0; $i < 5; $i++) {
                        $date = $today->copy()->addDays($i)->toDateString();
                        $times = [];
                        foreach ($tour->departures as $dep) {
                            /* NULL departure_date means this departure happens every day. Otherwise it only happens on its configured date. */
                            if (is_null($dep->departure_date) || Carbon::parse($dep->departure_date)->toDateString() === $date) {
                                $times[] = $dep->departure_time instanceof Carbon ? $dep->departure_time->format('H:i') : substr((string) $dep->departure_time, 0, 5);
                            }
                        }
                        if (!empty($times)) {
                            $schedule[$date] = collect($times)->unique()->sort()->values()->toArray();
                        }
                    }
                }

                // =====================================================
                // 2. Map Pricing Tiers
                // =====================================================
                $tiers = $tour->pricingTiers->map(function ($t) use ($tour) {
                    /* Find the base price for the same vehicle and trip type. FIXED BUG: Removed the `where('pax_count', 1)` restriction so it can successfully locate the base tier for Private Cars. */
                    $baseTier = $tour->pricingTiers->where('is_base', true)->where('vehicle_type', $t->vehicle_type)->where('trip_type', $t->trip_type)->first();

                    /* Calculate the original price used to display an offer discount. For microbus this represents: base price × number of passengers. For private car, the tier price itself represents the whole car, so we should NOT multiply it by pax_count. */
                    if ($t->vehicle_type === 'private') {
                        // Offer discounts for private cars are calculated against the base car price
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

                // =====================================================
                // 3. Return Tour Data
                // =====================================================
                return [
                    'id' => $tour->slug,
                    'name' => $tour->name_en . ($tour->name_ar ? ' / ' . $tour->name_ar : ''),
                    'schedule' => $schedule,
                    'tiers' => $tiers,
                ];
            })->values()->toArray();
        });

        return view('pages.booking', compact('routes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'routeId' => 'required|string',
            'departureDate' => 'required|date',
            'departureTime' => 'required|string',
            'passengers' => 'required|integer|min:1',
            'leadName' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'whatsapp' => 'required|string|max:50',
            'nationality' => 'required|string|max:2',
            'roomNumber' => 'nullable|string|max:50',
            'hotel' => 'required|string',
            'notes' => 'nullable|string',
            'tripType' => 'required|in:one-way,round-trip',
            'vehicleType' => 'required|in:microbus,private',
            'extraWhatsApp' => 'nullable|array',
        ]);

        return DB::transaction(function () use ($validated) {
            // =========================================================
            // 1. Find / Create Client & Hotel
            // =========================================================
            $client = Client::firstOrCreate(['whatsapp' => $validated['whatsapp']], ['name' => $validated['leadName'], 'nationality' => $validated['nationality']]);
            $hotel = Hotel::firstOrCreate(['name' => $validated['hotel']]);

            // =========================================================
            // 3. Find Tour
            // =========================================================
            $tour = Tour::where('slug', $validated['routeId'])->orWhere('id', $validated['routeId'])->firstOrFail();

            // =========================================================
            // 4. Validate Departure
            // =========================================================
            /* There are three possible cases: 1. Specific departure: departure_date = selected date 2. Recurring departure: departure_date = NULL 3. No departure records: daily at 09:00 */
            $departure = TourDeparture::where('tour_id', $tour->id)->where('is_active', true)->whereTime('departure_time', $validated['departureTime'])
                ->where(function ($query) use ($validated) {
                    $query->whereDate('departure_date', $validated['departureDate'])->orWhereNull('departure_date');
                })->first();

            // Does this tour have any configured departures?
            $hasAnyDeparture = TourDeparture::where('tour_id', $tour->id)->where('is_active', true)->exists();

            /* No departure records at all: The frontend considers this tour: Every day at 09:00. */
            if (!$departure && !$hasAnyDeparture) {
                if ($validated['departureTime'] !== '09:00') abort(422, 'This tour is available daily at 09:00.');
                /* There is no actual TourDeparture row, so we store NULL. */
                $departureId = null;
            } elseif (!$departure) {
                /* The tour has a schedule, but the selected date/time isn't valid. */
                abort(422, 'The selected departure is not available for this date and time.');
            } else {
                $departureId = $departure->id;
            }

            // =========================================================
            // 5. Convert Frontend Values & Get Pricing Tiers
            // =========================================================
            $tripTypeMap = $validated['tripType'] === 'round-trip' ? 'round_trip' : 'one_way';
            $vehicleTypeMap = $validated['vehicleType'];

            $tiers = TourPricingTier::where('tour_id', $tour->id)->where('is_active', true)->where('trip_type', $tripTypeMap)->where('vehicle_type', $vehicleTypeMap)->orderBy('pax_count', 'desc')->get();

            /* Base price lookup. FIXED BUG: Removed the `where('pax_count', 1)` restriction so it can successfully locate the base tier for Private Cars. */
            $baseTier = $tiers->where('is_base', true)->first();
            /* Offers only. */
            $offers = $tiers->where('is_base', false);

            // =========================================================
            // 7. No Pricing Configuration
            // =========================================================
            if (!$baseTier && $offers->isEmpty()) {
                abort(422, 'This tour cannot be booked because no pricing is configured for the selected vehicle and trip type.');
            }

            // =========================================================
            // 8. Initialize Pricing
            // =========================================================
            $totalPrice = 0;
            $depositDue = 0;
            $remainingPax = $validated['passengers'];
            $primaryTierId = null;

            // =========================================================
            // 9. PRIVATE CAR PRICING
            // =========================================================
            if ($vehicleTypeMap === 'private') {
                /* PRIVATE CAR MODEL ================= One tier = ONE complete car. pax_count = how many people that car can carry. Because tiers are ordered by pax_count DESC, the first offer has the largest capacity. If there is an offer, use it. Otherwise use the base. */
                $carTier = $offers->first() ?? $baseTier;

                if (!$carTier) abort(422, 'This tour cannot be booked because no private-car pricing is configured.');

                /* A private-car tier represents one complete car. */
                $capacity = $carTier->pax_count;

                if ($capacity <= 0) abort(422, 'The selected private-car pricing configuration has an invalid passenger capacity.');

                /* Calculate how many cars are required. Price is PER CAR. NOT: price × passengers but: price × cars. */
                $carsNeeded = (int) ceil($remainingPax / $capacity);
                $totalPrice = $carsNeeded * $carTier->total_price;
                $depositDue = $carsNeeded * $carTier->deposit_amount;
                
                /* Store the tier used to calculate the booking. */
                $primaryTierId = $carTier->id;
                
                /* All passengers have been covered by the required cars. */
                $remainingPax = 0;

            // =========================================================
            // 10. MICROBUS PRICING
            // =========================================================
            } else {
                /* MICROBUS MODEL =============== Offers represent groups of passengers. Example: 4 pax offer, 2 pax offer, 1 pax base. For 7 passengers: 4 + 2 + 1. The largest offer is attempted first. */
                foreach ($offers as $offer) {
                    if ($remainingPax <= 0) break;
                    /* Invalid offer. */
                    if ($offer->pax_count <= 0) continue;

                    /* How many times can we use this offer? */
                    $multiplier = (int) floor($remainingPax / $offer->pax_count);

                    if ($multiplier <= 0) continue;

                    /* Remember the first tier used. */
                    if (!$primaryTierId) $primaryTierId = $offer->id;

                    /* Add offer price/deposit and remove the passengers covered by this offer. */
                    $totalPrice += $offer->total_price * $multiplier;
                    $depositDue += $offer->deposit_amount * $multiplier;
                    $remainingPax -= $offer->pax_count * $multiplier;
                }

                // =====================================================
                // 11. MICROBUS REMAINING PASSENGERS
                // =====================================================
                if ($remainingPax > 0) {
                    /* Base price can cover individual passengers. */
                    if ($baseTier) {
                        if (!$primaryTierId) $primaryTierId = $baseTier->id;

                        $totalPrice += $remainingPax * $baseTier->total_price;
                        $depositDue += $remainingPax * $baseTier->deposit_amount;
                        $remainingPax = 0;
                    } else {
                        /* IMPORTANT: There is no base price. Therefore we cannot price the remaining passengers. We DO NOT use a fallback price. */
                        abort(422, 'This tour cannot be booked for the selected number of passengers because the available offers do not cover everyone and no base price is configured.');
                    }
                }
            }

            // =========================================================
            // 12. Final Safety Check
            // =========================================================
            if ($remainingPax > 0) abort(422, 'This booking cannot be completed because the available pricing configurations do not cover all passengers.');

            // =========================================================
            // 13. Calculate Cash Due & Create Booking
            // =========================================================
            $cashDue = $totalPrice - $depositDue;

            $booking = Booking::create([
                'booking_reference' => 'BKG-' . strtoupper(Str::random(8)),
                'client_id' => $client->id,
                'tour_id' => $tour->id,
                /* NULL if the tour uses the automatic 09:00 schedule. */
                'tour_departure_id' => $departureId,
                /* The main tier used by the pricing calculation. */
                'tour_pricing_tier_id' => $primaryTierId,
                'hotel_id' => $hotel->id,
                'room_number' => $validated['roomNumber'],
                'direction' => 'outbound',
                'booking_date' => $validated['departureDate'],
                'special_requests' => $validated['notes'],
                'total_price' => $totalPrice,
                'deposit_paid' => 0,
                'cash_due_on_board' => $cashDue,
                'payment_status' => 'pending',
            ]);

            // =========================================================
            // 15. Return Payment URL
            // =========================================================
            return response()->json([
                'success' => true,
                'reference' => $booking->booking_reference,
                'redirect_url' => route('payment.checkout', ['reference' => $booking->booking_reference]),
            ]);
        });
    }
}