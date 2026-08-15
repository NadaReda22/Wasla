<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\Faq; 
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Area;
use App\Models\Setting;

class HomeController extends Controller
{
    public function index()
    {
        // 1. Fetch active tours and relationships
        $tours = Tour::where('is_active', true)
            ->with(['pricingTiers' => function($q) {
                // Eager load and sort so base prices and pax counts are ordered nicely
                $q->where('is_active', true)
                  ->orderBy('is_base', 'desc')
                  ->orderBy('pax_count', 'asc');
            }, 'departures' => function($q) {
                $q->where('is_active', true)
                  ->orderBy('departure_time');
            }])
            ->get();

        $daysAhead = 5;
        
        // 2. Generate Daily Tabs (Today + 4 days)
        $days = collect(range(0, $daysAhead - 1))->map(function ($i) use ($tours) {
            $date = Carbon::today()->addDays($i);
            $iso = $date->toDateString();

            $dayTours = $tours->map(function ($tour, $idx) use ($i, $iso) {
                
                $hasDepartureToday = $tour->departures->contains(function ($dep) use ($iso) {
                    if (is_null($dep->departure_date)) return true; // Daily tour
                    return Carbon::parse($dep->departure_date)->toDateString() === $iso;
                });

                if (!$hasDepartureToday) {
                    return null;
                }

                return $this->formatTourData($tour, $i + $idx);
            })->filter()->values(); 

            return [
                'key' => 'day-' . $i,
                'iso' => $iso,
                'label' => $i === 0 ? 'Today' : ($i === 1 ? 'Tomorrow' : $date->format('D, M j')),
                'dateFull' => $date->format('l, F j, Y'),
                'tours' => $dayTours,
            ];
        })->filter(function($day) {
            return count($day['tours']) > 0;
        });

        // 3. Generate "All" Tab
        $allTours = $tours->map(function ($tour, $idx) {
            return $this->formatTourData($tour, $idx);
        })->filter()->values();

        $allTab = [
            'key' => 'day-all',
            'iso' => '', 
            'label' => 'All Tours',
            'dateFull' => 'All Available Tours',
            'tours' => $allTours,
        ];

        // 4. Prepend the "All" tab
        $days->prepend($allTab);
        $days = $days->values(); 

        // 5. Fetch Top 4 FAQs for the landing page
        $faqs = Faq::where('is_active', true)
                    ->orderBy('sort_order', 'asc')
                    ->take(4)
                    ->get();
        // 6. Fetch Active Coverage Areas
        $areas = Area::where('is_active', true)->get();            


        // 7. OPTIMIZATION: Only fetch areas if the map is NOT hidden in settings
        $settings = Setting::first();
        $areas = $settings?->hide_coverage_area ? collect() : Area::where('is_active', true)->get();

          return view('pages.landing', compact('days', 'faqs', 'areas', 'settings'));
    }

    /**
     * Helper method to map the tour data
     */
    private function formatTourData($tour, $seedOffset)
    {
        $allDepartures = $tour->departures->map(function($dep) {
            $time = Carbon::parse($dep->departure_time)->format('H:i');
            if (is_null($dep->departure_date)) return 'Daily ' . $time;
            return Carbon::parse($dep->departure_date)->format('M d') . ' ' . $time;
        })->unique()->values()->toArray();

        // === DYNAMIC PRICING MATRIX LOGIC ===
        $microbusOffers = [];
        $privateCarOffers = [];
        $allTiers = $tour->pricingTiers; //handles all pricing tiers, including base and non-base, for both vehicle types and trip types for each tour. 

        /**
         * Logic for determining the authoritative base price and calculating original prices and discounts:
         * To forbid multiple base prices for the same vehicle/trip combination, we identify the first base price encountered as the authoritative one. Any other base prices for the same combination are ignored in calculations.
         */

        // 1. STRICT RESTRICTION: Identify exactly ONE authoritative base price per vehicle/trip combo.
        $authoritativeBases = [];
        foreach ($allTiers->where('is_base', true) as $base) {
            $comboKey = $base->vehicle_type . '_' . $base->trip_type;
            if (!isset($authoritativeBases[$comboKey])) {
                $authoritativeBases[$comboKey] = $base;
            }
        }

        foreach ($allTiers as $tier) {
            $comboKey = $tier->vehicle_type . '_' . $tier->trip_type;
            
            // 2. RESTRICTION ENFORCEMENT: If this is a base price, but NOT the authoritative one, SKIP IT.
            if ($tier->is_base && (!isset($authoritativeBases[$comboKey]) || $authoritativeBases[$comboKey]->id !== $tier->id)) {
                continue;
            }

            // 3. Get the authoritative base tier for calculations
            $baseTier = $authoritativeBases[$comboKey] ?? null;

            // 4. Calculate Original Price mathematically
            $originalPrice = $tier->total_price; 
            
            if ($baseTier) {
                if ($tier->vehicle_type === 'microbus') {
                    // Microbus is PER PERSON (Multiply by pax count)
                    $originalPrice = $baseTier->total_price * $tier->pax_count;
                } else {
                    // Private car is PER VEHICLE (Do NOT multiply by pax count)
                    $originalPrice = $baseTier->total_price;
                }
            }

            // 5. Calculate Discount Percentage
            $discountPercentage = 0;
            if (!$tier->is_base && $originalPrice > $tier->total_price) {
                $discountPercentage = (int) round((($originalPrice - $tier->total_price) / $originalPrice) * 100);
            }

            // 6. Format the Name
            $tripLabel = $tier->trip_type === 'round_trip' ? 'Round Trip' : 'One Way';
            $offerName = $tier->is_base ? "Standard Rate" : $tier->offer_name;

            $offerData = [
                'offerName'          => $offerName,
                'paxCount'           => $tier->pax_count,
                'tripLabel'          => $tripLabel,
                'totalPrice'         => $tier->total_price,
                'originalPrice'      => $originalPrice,
                'discountPercentage' => $discountPercentage,
            ];

            // 7. Funnel
            if ($tier->vehicle_type === 'microbus') {
                $microbusOffers[] = $offerData;
            } elseif ($tier->vehicle_type === 'private') {
                $privateCarOffers[] = $offerData;
            }
        }
        // ====================================

        $imageUrl = $tour->image_url;
        if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
            $imageUrl = asset('storage/' . $imageUrl);
        } elseif (!$imageUrl) {
            $imageUrl = asset('images/default-tour.jpg');
        }

        $baseSeats = $tour->base_seats ?? 12;
        $seatsLeft = max(2, $baseSeats - ($seedOffset % 4) * 2);

        return [
            'id' => $tour->slug,
            'name' => $tour->name_en,
            'arabicName' => $tour->name_ar,
            'image' => $imageUrl,
            'alt' => $tour->name_en,
            'from' => $tour->location ?? 'City Center',
            'tag' => $tour->tag_name ?? 'Tour',
            'tagColor' => $tour->tag_color_class ?? '#003B5C', 
            'allDepartures' => $allDepartures,
            'baseSeats' => $baseSeats,
            'seatsLeft' => $seatsLeft,
            'description_en' => $tour->description_en,
            'description_ar' => $tour->description_ar,
            'microbusOffers' => $microbusOffers,
            'privateCarOffers' => $privateCarOffers,
        ];
    }

    // Fetch ALL active FAQs for the dedicated page
    public function faq()
    {
        
        $faqs = \App\Models\Faq::where('is_active', true)
                    ->orderBy('sort_order', 'asc')
                    ->get();
                    
        return view('pages.faq', compact('faqs'));
    }
}