<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\Faq; 
use App\Models\Area;
use App\Models\Setting;
use App\Models\Vehicle;  
use App\Models\Booking;  
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('pages.landing', $this->buildHomeData());
    }

    public function instructions()
    {
        return view('pages.instructions', $this->buildHomeData());
    }

    public function faq()
    {
        $faqs = Faq::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();
                    
        return view('pages.faq', compact('faqs'));
    }

    private function buildHomeData(): array
    {
        $tours = Tour::where('is_active', true)
            ->with([
                'pricingTiers' => function ($q) {
                    $q->where('is_active', true)
                      ->orderBy('is_base', 'desc')
                      ->orderBy('pax_count', 'asc');
                },
                'departures' => function ($q) {
                    $q->where('is_active', true)
                      ->orderBy('departure_time');
                }
            ])
            ->get();

        $totalFleetCapacity = (int) Vehicle::sum('capacity');
        $daysAhead = 5;
        $today = Carbon::today();
        $endDate = $today->copy()->addDays($daysAhead);

        $bookedSeatsByDate = Booking::whereBetween('booking_date', [$today->toDateString(), $endDate->toDateString()])
            ->whereIn('payment_status', ['paid', 'PAID'])
            ->groupBy('booking_date')
            ->selectRaw('DATE(booking_date) as date, SUM(seats_count) as total_booked')
            ->pluck('total_booked', 'date');

        $days = collect(range(0, $daysAhead - 1))->map(function ($i) use ($tours, $totalFleetCapacity, $bookedSeatsByDate) {
            $date = Carbon::today()->addDays($i);
            $iso = $date->toDateString();

            $bookedCount = (int) $bookedSeatsByDate->get($iso, 0);
            $seatsLeft = max(0, $totalFleetCapacity - $bookedCount);

            $dayTours = $tours->map(function ($tour) use ($iso, $totalFleetCapacity, $seatsLeft) {
                $hasDepartureToday = false;
                
                if ($tour->departures->isEmpty()) {
                    $hasDepartureToday = Carbon::parse($iso . ' 09:00:00')->isFuture();
                } else {
                    $hasDepartureToday = $tour->departures->contains(function ($dep) use ($iso) {
                        $timeOnly = Carbon::parse($dep->departure_time)->format('H:i:s');
                        if (Carbon::parse($iso . ' ' . $timeOnly)->isPast()) {
                            return false;
                        }
                        return is_null($dep->departure_date) || Carbon::parse($dep->departure_date)->toDateString() === $iso;
                    });
                }

                return $hasDepartureToday ? $this->formatTourData($tour, $totalFleetCapacity, $seatsLeft) : null;
            })->filter()->values(); 

            return [
                'key'      => 'day-' . $i,
                'iso'      => $iso,
                'label'    => $i === 0 ? __('home.today') : ($i === 1 ? __('home.tomorrow') : $date->translatedFormat('D, M j')),
                'dateFull' => $date->translatedFormat('l, F j, Y'),
                'tours'    => $dayTours,
            ];
        })->filter(fn ($day) => count($day['tours']) > 0);

        $todayIso = $today->toDateString();
        $todaySeatsLeft = max(0, $totalFleetCapacity - (int) $bookedSeatsByDate->get($todayIso, 0));

        $allTours = $tours->map(function ($tour) use ($totalFleetCapacity, $todaySeatsLeft) {
            return $this->formatTourData($tour, $totalFleetCapacity, $todaySeatsLeft);
        })->filter()->values();

        $days->prepend([
            'key'      => 'day-all',
            'iso'      => '', 
            'label'    => __('home.all_tours_tab'),
            'dateFull' => __('home.all_available_tours'),
            'tours'    => $allTours,
        ]);

        $faqs = Faq::where('is_active', true)->orderBy('sort_order', 'asc')->take(4)->get();
        $settings = Setting::first();
        $areas = $settings?->hide_coverage_area ? collect() : Area::where('is_active', true)->get();

        return ['days' => $days->values(), 'faqs' => $faqs, 'areas' => $areas, 'settings' => $settings];
    }

    private function formatTourData($tour, int $totalFleetCapacity, int $totalSeatsLeft): ?array
    {
        $validDepartures = $tour->departures->filter(function ($dep) {
            if (is_null($dep->departure_date)) return true; 
            
            $timeOnly = Carbon::parse($dep->departure_time)->format('H:i:s');
            return Carbon::parse(Carbon::parse($dep->departure_date)->toDateString() . ' ' . $timeOnly)->isFuture(); 
        });

        if ($validDepartures->isEmpty()) {
            $allDepartures = [__('home.daily') . ' 09:00'];
        } else {
            $allDepartures = $validDepartures->map(function ($dep) {
                $depTime = Carbon::parse($dep->departure_time)->format('H:i');
                $retTimeStr = $dep->return_time ? " (Ret: " . Carbon::parse($dep->return_time)->format('H:i') . ")" : '';

                if (is_null($dep->departure_date)) {
                    return __('home.daily') . ' ' . $depTime . $retTimeStr;
                }
                return Carbon::parse($dep->departure_date)->translatedFormat('M d') . ' ' . $depTime . $retTimeStr;
            })->unique()->values()->toArray();
        }

        $microbusOffers = [];
        $privateCarOffers = [];
        $allTiers = $tour->pricingTiers;
        $authoritativeBases = [];

        foreach ($allTiers->where('is_base', true) as $base) {
            $comboKey = $base->vehicle_type . '_' . $base->trip_type;
            $authoritativeBases[$comboKey] ??= $base;
        }

        foreach ($allTiers as $tier) {
            $comboKey = $tier->vehicle_type . '_' . $tier->trip_type;
            
            if ($tier->is_base && ($authoritativeBases[$comboKey]->id ?? null) !== $tier->id) {
                continue;
            }

            $baseTier = $authoritativeBases[$comboKey] ?? null;
            $originalPrice = $tier->total_price; 
            
            if ($baseTier) {
                $originalPrice = $tier->vehicle_type === 'microbus'
                    ? $baseTier->total_price * $tier->pax_count
                    : $baseTier->total_price;
            }

            $discountPercentage = (!$tier->is_base && $originalPrice > $tier->total_price)
                ? (int) round((($originalPrice - $tier->total_price) / $originalPrice) * 100)
                : 0;

            $offerData = [
                'offerName'          => $tier->is_base ? __('home.standard_rate') : $tier->offer_name,
                'paxCount'           => $tier->pax_count,
                'tripLabel'          => $tier->trip_type === 'round_trip' ? 'Round Trip' : 'One Way',
                'totalPrice'         => $tier->total_price,
                'originalPrice'      => $originalPrice,
                'discountPercentage' => $discountPercentage,
            ];

            if ($tier->vehicle_type === 'microbus') {
                $microbusOffers[] = $offerData;
            } elseif ($tier->vehicle_type === 'private') {
                $privateCarOffers[] = $offerData;
            }
        }

        if (empty($allDepartures) || (empty($microbusOffers) && empty($privateCarOffers))) {
            return null;
        }

        $imageUrl = $tour->image_url;
        if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
            $imageUrl = asset('storage/' . $imageUrl);
        } elseif (!$imageUrl) {
            $imageUrl = asset('images/default-tour.jpg');
        }

        return [
            'id'                 => $tour->slug,
            'name'               => $tour->name_en,
            'arabicName'         => $tour->name_ar,
            'is_bus_staying'     => (bool) $tour->is_bus_staying, 
            'image'              => $imageUrl,
            'alt'                => $tour->name_en,
            'from'               => $tour->location ?? __('home.city_center'),
            'tag'                => $tour->tag_name ?? __('home.tour'),
            'tagColor'           => $tour->tag_color_class ?? '#003B5C', 
            'allDepartures'      => $allDepartures,
            'baseSeats'          => $totalFleetCapacity,
            'seatsLeft'          => $totalSeatsLeft,
            'description_en'     => $tour->description_en,
            'description_ar'     => $tour->description_ar,
            'description_ru'     => $tour->description_ru,
            'microbusOffers'     => $microbusOffers,
            'privateCarOffers'   => $privateCarOffers,
        ];
    }
}