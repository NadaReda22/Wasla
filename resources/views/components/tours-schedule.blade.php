{{--
    Tours Schedule component
    resources/views/components/tours-schedule.blade.php

    Usage:
        <x-tours-schedule />

    Once real trip / seat data exists, pass it in as $scheduleSeed — an array
    shaped like:
        [
            'YYYY-MM-DD' => [
                ['id' => 'naama', 'seatsLeft' => 6, 'departures' => ['07:00', ...]],
                ...
            ],
        ]
    Any day/tour not present in $scheduleSeed falls back to the demo data below,
    so you can wire up real trips gradually.
--}}
@props(['scheduleSeed' => []])

@php
    use Carbon\Carbon;

    // How many days ahead to show, including today.
    $daysAhead = 5;

    // Base tour catalog — the routes that run every day.
    $baseTours = [
        [
            'id' => 'naama',
            'name' => 'Naama Bay Express',
            'arabicName' => 'نعمة باي',
            'image' => 'https://img.rocket.new/generatedImages/rocket_gen_img_127d34916-1783615096003.png',
            'alt' => 'Naama Bay beach in Sharm El Sheikh with turquoise water and hotels',
            'from' => 'City Center / Hotels',
            'duration' => '15 min',
            'tag' => 'Most Popular',
            'tagClass' => 'bg-primary text-white',
            'departures' => ['07:00','08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00'],
            'baseSeats' => 8,
            'description' => 'Direct transfer to the heart of Naama Bay — restaurants, beach clubs, diving centers, and nightlife.',
        ],
        [
            'id' => 'ras-mohamed',
            'name' => 'Ras Mohamed National Park',
            'arabicName' => 'رأس محمد',
            'image' => 'https://img.rocket.new/generatedImages/rocket_gen_img_11dd9a23d-1772248668505.png',
            'alt' => 'Ras Mohamed National Park with crystal clear water and coral reef',
            'from' => 'Naama Bay / City Center',
            'duration' => '45 min',
            'tag' => '🐠 Snorkeling',
            'tagClass' => 'bg-turquoise text-white',
            'departures' => ['07:00','09:00','13:00'],
            'baseSeats' => 3,
            'description' => "Visit Egypt's most famous marine park. World-class snorkeling and diving at Shark Bay and Yolanda Reef.",
        ],
        [
            'id' => 'airport',
            'name' => 'Sharm Airport Transfer',
            'arabicName' => 'مطار شرم الشيخ',
            'image' => 'https://images.unsplash.com/photo-1713877364816-cbf575c29c0b',
            'alt' => 'Sharm El Sheikh International Airport terminal',
            'from' => 'Any Hotel in Sharm',
            'duration' => '20 min',
            'tag' => '✈️ Airport',
            'tagClass' => 'bg-navy text-white',
            'departures' => ['05:00','06:30','08:00','10:00','12:00','14:00','16:00','18:00','20:00','22:00'],
            'baseSeats' => 12,
            'description' => 'Reliable hotel-to-airport transfer. Driver waits with your name sign. Never miss a flight.',
        ],
        [
            'id' => 'dahab',
            'name' => 'Dahab Day Trip',
            'arabicName' => 'دهب',
            'image' => 'https://img.rocket.new/generatedImages/rocket_gen_img_172cb48a8-1772195543639.png',
            'alt' => 'Dahab Blue Hole diving site with turquoise water',
            'from' => 'Sharm City Center',
            'duration' => '1.5 hrs',
            'tag' => '🌊 Day Trip',
            'tagClass' => 'bg-gold text-navy',
            'departures' => ['08:00','11:00','15:00'],
            'baseSeats' => 6,
            'description' => 'Escape to the laid-back Bedouin town of Dahab. Famous for the Blue Hole, windsurfing, and fresh seafood.',
        ],
        [
            'id' => 'old-market',
            'name' => 'Old Market Shopping',
            'arabicName' => 'السوق القديم',
            'image' => 'https://img.rocket.new/generatedImages/rocket_gen_img_153aef18b-1773104215310.png',
            'alt' => 'Sharm El Sheikh Old Market bazaar with spices and souvenirs',
            'from' => 'Naama Bay / Hotels',
            'duration' => '25 min',
            'tag' => '🛍️ Shopping',
            'tagClass' => 'bg-coral text-white',
            'departures' => ['09:00','11:00','17:00','19:00'],
            'baseSeats' => 14,
            'description' => 'Explore the authentic Old Market — spices, souvenirs, jewelry, and traditional Egyptian crafts at local prices.',
        ],
        [
            'id' => 'taba',
            'name' => 'Taba Heights',
            'arabicName' => 'طابا هايتس',
            'image' => 'https://img.rocket.new/generatedImages/rocket_gen_img_168e14898-1783615096104.png',
            'alt' => 'Taba Heights resort area with Red Sea and mountains',
            'from' => 'Sharm City Center',
            'duration' => '3 hrs',
            'tag' => '🏔️ Border Trip',
            'tagClass' => 'bg-turquoise text-white',
            'departures' => ['07:30','10:00'],
            'baseSeats' => 14,
            'description' => 'Full-day trip to Taba Heights resort area. Stunning views of 4 countries — Egypt, Israel, Jordan, and Saudi Arabia.',
        ],
    ];

    // Build the day list (Today, Tomorrow, then weekday names) and attach
    // per-day tour data — pulling from $scheduleSeed when it's been supplied,
    // otherwise falling back to a small deterministic variation so the demo
    // doesn't look frozen day to day.
    $days = collect(range(0, $daysAhead - 1))->map(function ($i) use ($baseTours, $scheduleSeed) {
        $date = Carbon::today()->addDays($i);
        $iso = $date->toDateString();

        $tours = collect($baseTours)->map(function ($tour, $idx) use ($i, $iso, $scheduleSeed) {
            $seed = $scheduleSeed[$iso][$tour['id']] ?? null;

            $seatsLeft = $seed['seatsLeft']
                ?? max(2, $tour['baseSeats'] - (($i + $idx) % 4) * 2);

            $departures = $seed['departures'] ?? $tour['departures'];

            return array_merge($tour, [
                'seatsLeft' => $seatsLeft,
                'departures' => $departures,
                'tripsToday' => count($departures),
            ]);
        });

        return [
            'key' => 'day-' . $i,
            'iso' => $iso,
            'label' => $i === 0 ? 'Today' : ($i === 1 ? 'Tomorrow' : $date->format('D, M j')),
            'dateFull' => $date->format('l, F j, Y'),
            'tours' => $tours,
        ];
    });
@endphp

<section class="section-padding bg-sand-light" id="tours">
    <div class="container-max">
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-10">
            <div>
                <div class="inline-flex items-center gap-2 bg-coral/15 text-coral px-4 py-2 rounded-full text-sm font-semibold mb-4">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    Live Schedule
                </div>
                <h2 class="text-3xl sm:text-4xl font-display font-bold text-navy mb-2">All Tours &mdash; Book Any Day</h2>
                <p class="text-navy/60 flex items-center gap-2" data-day-date>
                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ $days->first()['dateFull'] }}
                </p>
            </div>
            <div class="card flex items-center gap-4 px-6 py-4 shrink-0">
                <svg class="w-7 h-7 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2" /></svg>
                <div>
                    <div class="text-2xl font-bold text-navy">$4 <span class="text-sm font-normal text-navy/50">total</span></div>
                    <div class="text-xs text-navy/50">Only <span class="text-coral font-semibold">$1 deposit</span> online</div>
                </div>
            </div>
        </div>

        {{-- Day selector --}}
        <div class="flex gap-2 overflow-x-auto pb-2 mb-8 -mx-1 px-1" role="tablist" aria-label="Choose a day">
            @foreach ($days as $i => $day)
                <button
                    type="button"
                    class="day-tab-btn flex-shrink-0 px-5 py-2.5 rounded-full text-sm font-semibold border transition-colors {{ $i === 0 ? 'bg-navy text-white border-navy' : 'bg-white text-navy/70 border-navy/10 hover:border-navy/30' }}"
                    data-day-tab="{{ $day['key'] }}"
                    data-day-full="{{ $day['dateFull'] }}"
                    role="tab"
                    aria-selected="{{ $i === 0 ? 'true' : 'false' }}"
                >
                    {{ $day['label'] }}
                </button>
            @endforeach
        </div>

        {{-- Day panels --}}
        @foreach ($days as $i => $day)
            <div
                class="day-panel {{ $i === 0 ? '' : 'hidden' }}"
                data-day-panel="{{ $day['key'] }}"
                role="tabpanel"
            >
                <div class="relative">
                    <button
                        type="button"
                        class="carousel-arrow flex absolute left-1 md:left-2 top-1/2 -translate-y-1/2 z-10 w-8 h-8 md:w-11 md:h-11 rounded-full bg-white shadow-lg border border-navy/10 items-center justify-center text-navy hover:bg-navy hover:text-white transition-colors"
                        data-carousel-prev
                        aria-label="Previous tours"
                    >
                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                    <button
                        type="button"
                        class="carousel-arrow flex absolute right-1 md:right-2 top-1/2 -translate-y-1/2 z-10 w-8 h-8 md:w-11 md:h-11 rounded-full bg-white shadow-lg border border-navy/10 items-center justify-center text-navy hover:bg-navy hover:text-white transition-colors"
                        data-carousel-next
                        aria-label="Next tours"
                    >
                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>

                    <div
                        class="tour-carousel-track flex gap-6 overflow-x-auto snap-x snap-mandatory scroll-smooth pb-4 px-12 md:px-16"
                        data-carousel-track
                    >
                    @foreach ($day['tours'] as $tour)
                        <div class="card overflow-hidden group w-[320px] sm:w-[380px] md:w-[420px] flex-shrink-0 snap-start">
                            <div class="relative h-56 overflow-hidden">
                                <img
                                    src="{{ $tour['image'] }}"
                                    alt="{{ $tour['alt'] }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                    loading="lazy"
                                />
                                <div class="absolute inset-0 bg-gradient-to-t from-navy/70 via-navy/10 to-transparent"></div>
                                <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-xs font-bold {{ $tour['tagClass'] }}">
                                    {{ $tour['tag'] }}
                                </span>
                                @if ($tour['seatsLeft'] <= 4)
                                    <span class="absolute top-3 right-3 bg-coral text-white px-3 py-1 rounded-full text-xs font-bold animate-pulse">
                                        Only {{ $tour['seatsLeft'] }} left!
                                    </span>
                                @endif
                                <div class="absolute bottom-3 left-3">
                                    <div class="text-white font-bold text-lg leading-tight">{{ $tour['name'] }}</div>
                                    <div class="text-white/70 text-xs">{{ $tour['arabicName'] }}</div>
                                </div>
                            </div>

                            <div class="p-5">
                                <p class="text-navy/60 text-xs leading-relaxed mb-4">{{ $tour['description'] }}</p>

                                <div class="flex flex-wrap items-center gap-4 text-xs text-navy/50 mb-4">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        {{ $tour['from'] }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-turquoise" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        {{ $tour['duration'] }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-navy" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-2a4 4 0 100-8 4 4 0 000 8zm6 3v2m-3-1h6" /></svg>
                                        {{ $tour['seatsLeft'] }} seats
                                    </span>
                                </div>

                                <div class="mb-4">
                                    <div class="text-xs font-semibold text-navy/60 mb-2 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        Departures &middot; <span class="text-primary">{{ $tour['tripsToday'] }} {{ $i === 0 ? 'today' : 'that day' }}</span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach (array_slice($tour['departures'], 0, 8) as $dep)
                                            <span class="bg-primary/10 text-primary text-xs font-semibold px-2 py-0.5 rounded-lg">
                                                {{ $dep }}
                                            </span>
                                        @endforeach
                                        @if (count($tour['departures']) > 8)
                                            <span class="bg-sand-light text-navy/50 text-xs font-semibold px-2 py-0.5 rounded-lg">
                                                +{{ count($tour['departures']) - 8 }} more
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-4 border-t border-navy/10">
                                    <div>
                                        <div class="text-xs text-navy/50">Total price</div>
                                        <div class="text-xl font-bold text-navy">
                                            $4 <span class="text-xs font-normal text-navy/50">/ person</span>
                                        </div>
                                        <div class="text-xs text-coral font-semibold">$1 deposit now</div>
                                    </div>
                                    <a
                                        href="{{ route('booking.create') }}?tour={{ $tour['id'] }}&date={{ $day['iso'] }}"
                                        class="btn-primary px-5 py-2.5 rounded-xl text-sm flex items-center gap-1.5"
                                    >
                                        Book Now
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
        @endforeach

        <div class="mt-10 card p-6 text-center">
            <p class="text-navy font-semibold mb-1">💳 Pay just $1 deposit online — pay the remaining $3 on board</p>
            <p class="text-navy/60 text-sm">Secure payment via Fawaterak &middot; Cash option available for all nationalities, including Russian tourists</p>
        </div>
    </div>
</section>

@once
    <style>
        .tour-carousel-track { scrollbar-width: none; -ms-overflow-style: none; }
        .tour-carousel-track::-webkit-scrollbar { display: none; }
    </style>
    <script>
        (function () {
            function initTourCarousels() {
                document.querySelectorAll('[data-carousel-track]').forEach(function (track) {
                    var wrap = track.closest('.relative');
                    if (!wrap) return;

                    var prevBtn = wrap.querySelector('[data-carousel-prev]');
                    var nextBtn = wrap.querySelector('[data-carousel-next]');
                    if (!prevBtn || !nextBtn) return;

                    function scrollStep() {
                        var card = track.querySelector(':scope > *');
                        var gap = 24; // matches gap-6
                        return card ? card.getBoundingClientRect().width + gap : 300;
                    }

                    prevBtn.addEventListener('click', function () {
                        track.scrollBy({ left: -scrollStep(), behavior: 'smooth' });
                    });
                    nextBtn.addEventListener('click', function () {
                        track.scrollBy({ left: scrollStep(), behavior: 'smooth' });
                    });
                });
            }

            function initTourDayTabs() {
                document.querySelectorAll('[data-day-tab]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var target = btn.getAttribute('data-day-tab');
                        var scope = btn.closest('section');
                        if (!scope) return;

                        scope.querySelectorAll('[data-day-tab]').forEach(function (b) {
                            var active = b === btn;
                            b.classList.toggle('bg-navy', active);
                            b.classList.toggle('text-white', active);
                            b.classList.toggle('border-navy', active);
                            b.classList.toggle('bg-white', !active);
                            b.classList.toggle('text-navy/70', !active);
                            b.classList.toggle('border-navy/10', !active);
                            b.setAttribute('aria-selected', active ? 'true' : 'false');
                        });

                        scope.querySelectorAll('[data-day-panel]').forEach(function (panel) {
                            panel.classList.toggle('hidden', panel.getAttribute('data-day-panel') !== target);
                        });

                        var dateLabel = scope.querySelector('[data-day-date]');
                        if (dateLabel) {
                            dateLabel.lastChild.textContent = ' ' + btn.getAttribute('data-day-full');
                        }
                    });
                });
            }

            function initAll() {
                initTourDayTabs();
                initTourCarousels();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initAll);
            } else {
                initAll();
            }
        })();
    </script>
@endonce