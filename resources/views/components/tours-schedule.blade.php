@props(['days'])

<section class="section-padding bg-sand-light" id="tours">
    <div class="container-max">
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-10">
            <div>
                <div class="inline-flex items-center gap-2 bg-coral/15 text-coral px-4 py-2 rounded-full text-sm font-semibold mb-4">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    Live Schedule
                </div>
                <h2 class="text-3xl sm:text-4xl font-display font-bold text-navy mb-2">All Tours &mdash; Book Any Day</h2>
                
                @if(count($days) > 0)
                <p class="text-navy/60 flex items-center gap-2" id="dynamic-date-label">
                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ collect($days)->first()['dateFull'] ?? '' }}</span>
                </p>
                @endif
            </div>
            <div class="card flex items-center gap-4 px-6 py-4 shrink-0">
                <svg class="w-7 h-7 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2" /></svg>
                <div>
                    <div class="text-2xl font-bold text-navy">Best Rates</div>
                    <div class="text-xs text-navy/50">{{ $settings?->announcement_card_text ?: 'Secure your seat with a tiny online deposit' }}</div>
                </div>
            </div>
        </div>

        @if(count($days) === 0)
            <div class="text-center py-12 bg-white rounded-xl border border-navy/10">
                <h3 class="text-xl font-bold text-navy">No scheduled tours found.</h3>
                <p class="text-navy/60 mt-2">Please check back later for upcoming departures.</p>
            </div>
        @else
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
                <div class="day-panel {{ $i === 0 ? '' : 'hidden' }}" data-day-panel="{{ $day['key'] }}" role="tabpanel">
                    <div class="relative">
                        <button type="button" class="carousel-arrow flex absolute left-1 md:left-2 top-1/2 -translate-y-1/2 z-10 w-8 h-8 md:w-11 md:h-11 rounded-full bg-white shadow-lg border border-navy/10 items-center justify-center text-navy hover:bg-navy hover:text-white transition-colors" data-carousel-prev aria-label="Previous tours">
                            <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                        <button type="button" class="carousel-arrow flex absolute right-1 md:right-2 top-1/2 -translate-y-1/2 z-10 w-8 h-8 md:w-11 md:h-11 rounded-full bg-white shadow-lg border border-navy/10 items-center justify-center text-navy hover:bg-navy hover:text-white transition-colors" data-carousel-next aria-label="Next tours">
                            <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>

                        <div class="tour-carousel-track flex gap-5 overflow-x-auto snap-x snap-mandatory scroll-smooth pb-4 px-12 md:px-16" data-carousel-track>
                        @foreach ($day['tours'] as $tour)
                            <div class="card overflow-hidden group w-[280px] sm:w-[320px] md:w-[360px] flex-shrink-0 snap-start flex flex-col shadow-sm">
                                {{-- Top Banner/Image --}}
                                <div class="relative h-36 overflow-hidden flex-shrink-0">
                                    <img src="{{ $tour['image'] }}" alt="{{ $tour['alt'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" />
                                    <div class="absolute inset-0 bg-gradient-to-t from-navy/80 via-navy/20 to-transparent"></div>
                                    
                                    <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold text-white shadow-sm" style="background-color: {{ $tour['tagColor'] }};">
                                        {{ $tour['tag'] }}
                                    </span>

                                    <div class="absolute bottom-2.5 left-2.5 right-2.5">
                                        <div class="text-white font-bold text-base leading-tight truncate">{{ $tour['name'] }}</div>
                                        <div class="text-white/70 text-[11px]">{{ $tour['arabicName'] }}</div>
                                    </div>
                                </div>

                                {{-- Card Body Content --}}
                                <div class="p-3.5 sm:p-4 flex flex-col flex-grow justify-between">
                           <div>
              {{-- ========================================== --}}
                {{-- 1. ENGLISH DESCRIPTION BLOCK                 --}}
                {{-- ========================================== --}}
                @if(!empty($tour['description']) || !empty($tour['description_en']))
                    <div class="relative mb-3 group/en cursor-pointer">
                        {{-- Text container: Clamped to 2 lines, expands fully on hover --}}
                        <div class="text-navy/70 text-xs leading-relaxed prose prose-sm max-w-none line-clamp-2 group-hover/en:line-clamp-none transition-all duration-300">
                            {!! $tour['description'] ?? $tour['description_en'] ?? '' !!}
                        </div>
                        
                        {{-- English Read More Hint --}}
                        <div class="text-[10px] font-bold text-[#1877C9] mt-1 group-hover/en:hidden">
                            Read more...
                        </div>
                    </div>
                @endif

              {{-- ========================================== --}}
                    {{-- 2. ARABIC DESCRIPTION BLOCK                  --}}
                    {{-- ========================================== --}}
                    @if(!empty($tour['description_ar']))
                        <div class="relative mb-3 group/ar cursor-pointer" dir="rtl">
                            {{-- Text container: Clamped to 2 lines, expands fully on hover --}}
                            <div class="text-navy/70 text-xs leading-relaxed prose prose-sm max-w-none line-clamp-2 group-hover/ar:line-clamp-none transition-all duration-300">
                                {!! $tour['description_ar'] !!}
                            </div>
                            
                            {{-- Arabic Read More Hint --}}
                            <div class="text-[10px] font-bold text-[#1877C9] mt-1 group-hover/ar:hidden">
                                اقرأ المزيد...
                            </div>
                        </div>
                    @endif
                                    {{-- Location and Seats Row --}}
                                        <div class="flex flex-wrap items-center justify-between text-xs text-navy/50 mb-3">
                                            <span class="flex items-center gap-1 text-[11px]">
                                                <svg class="w-3 h-3 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                                {{ $tour['from'] }}
                                            </span>
                                            
                                            <span class="flex items-center gap-1 font-bold text-coral bg-coral/10 px-2 py-0.5 rounded-md text-[11px]">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-2a4 4 0 100-8 4 4 0 000 8zm6 3v2m-3-1h6" /></svg>
                                                {{ $tour['seatsLeft'] }} Seats Left
                                            </span>
                                        </div>

                                        <hr class="border-navy/10 mb-3" />

                                        {{-- All Departures --}}
                                        <div class="mb-3">
                                            <div class="text-[11px] font-semibold text-navy/70 mb-2 flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-turquoise" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                All Scheduled Departures
                                            </div>
                                            
                                            <div class="flex flex-wrap gap-1.5">
                                                @if(empty($tour['allDepartures']))
                                                    <span class="text-[10px] text-navy/50 font-medium py-0.5">No active departures</span>
                                                @else
                                                    @foreach (array_slice($tour['allDepartures'], 0, 6) as $dep)
                                                        <span class="bg-primary/10 text-primary text-[10px] font-semibold px-2 py-1 rounded-md">
                                                            {{ $dep }}
                                                        </span>
                                                    @endforeach
                                                    @if(count($tour['allDepartures']) > 6)
                                                        <span class="bg-sand-light text-navy/50 text-[10px] font-semibold px-2 py-1 rounded-md">
                                                            +{{ count($tour['allDepartures']) - 6 }} more
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>

                                        <hr class="border-navy/10 mb-3" />

                                        {{-- MICROBUS OFFERS --}}
                                        @if(!empty($tour['microbusOffers']))
                                            <div class="bg-gradient-to-br from-gold/20 to-gold/5 border border-gold/40 rounded-lg p-2.5 mb-2.5 shadow-sm flex flex-col w-full">
                                                <div class="text-[10px] font-bold text-navy uppercase tracking-wider mb-2 flex items-center gap-1">
                                                    🚌 Shared Microbus
                                                </div>
                                                <div class="space-y-1.5 mt-auto">
                                                    @foreach ($tour['microbusOffers'] as $tier)
                                                        <div class="flex items-center justify-between text-xs">
                                                            <div class="text-navy font-medium truncate pr-1 text-[11px]">
                                                                {{ $tier['offerName'] }} <span class="text-navy/50 font-normal">({{ $tier['paxCount'] }} Pax • {{ $tier['tripLabel'] }})</span>
                                                            </div>
                                                            <div class="text-right flex items-center gap-1.5 flex-shrink-0">
                                                                @if(isset($tier['discountPercentage']) && $tier['discountPercentage'] > 0)
                                                                    <span class="text-[9px] text-coral font-bold bg-coral/15 px-1 py-0.5 rounded">
                                                                        -{{ $tier['discountPercentage'] }}%
                                                                    </span>
                                                                    @if(isset($tier['originalPrice']))
                                                                        <span class="text-navy/40 line-through text-[10px] font-semibold">${{ number_format($tier['originalPrice'], 0) }}</span>
                                                                    @endif
                                                                @endif
                                                                <span class="font-bold text-navy text-[13px]">${{ number_format($tier['totalPrice'], 0) }}</span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- PRIVATE CAR OFFERS --}}
                                        @if(!empty($tour['privateCarOffers']))
                                            <div class="bg-white border border-navy/10 rounded-lg p-2.5 mb-2.5">
                                                <div class="flex items-center justify-start gap-1 mb-2 text-navy">
                                                    <span class="text-sm">🚗</span>
                                                    <span class="text-[10px] font-bold uppercase tracking-wider">Private Car</span>
                                                </div>
                                                <div class="space-y-1.5 mt-auto">
                                                    @foreach($tour['privateCarOffers'] as $tier)
                                                        <div class="flex items-center justify-between text-xs">
                                                            <div class="text-navy font-medium truncate pr-1 text-[11px]">
                                                                {{ $tier['offerName'] }} <span class="text-navy/50 font-normal">({{ $tier['tripLabel'] }})</span>
                                                            </div>
                                                            <div class="text-right flex items-center gap-1.5 flex-shrink-0">
                                                                @if(isset($tier['discountPercentage']) && $tier['discountPercentage'] > 0)
                                                                    <span class="text-[9px] text-coral font-bold bg-coral/15 px-1 py-0.5 rounded">
                                                                        -{{ $tier['discountPercentage'] }}%
                                                                    </span>
                                                                    @if(isset($tier['originalPrice']))
                                                                        <span class="text-navy/40 line-through text-[10px] font-semibold">${{ number_format($tier['originalPrice'], 0) }}</span>
                                                                    @endif
                                                                @endif
                                                                <span class="font-bold text-navy text-[13px]">${{ number_format($tier['totalPrice'], 0) }}</span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="pt-1 mt-auto">
                                        <a
                                            href="{{ route('booking.create') }}?tour={{ $tour['id'] }}{{ $day['iso'] ? '&date='.$day['iso'] : '' }}"
                                            class="btn-primary w-full py-2.5 rounded-lg text-xs font-bold flex items-center justify-center gap-1.5 transition-all hover:shadow-md hover:shadow-primary/30"
                                        >
                                           Book
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
        @endif

        <div class="mt-8 card p-5 text-center">
            <p class="text-navy font-semibold text-sm mb-0.5">💳 Secure your private or shared ride with an easy online deposit</p>
            <p class="text-navy/60 text-xs">Pay the remaining cash balance safely on board with the driver.</p>
        </div>
    </div>
</section>

@once
    <style>
        .tour-carousel-track { scrollbar-width: none; -ms-overflow-style: none; }
        .tour-carousel-track::-webkit-scrollbar { display: none; }
    </style>
    <script>
        document.addEventListener('click', function (e) {
            
            var tabBtn = e.target.closest('[data-day-tab]');
            if (tabBtn) {
                e.preventDefault();
                e.stopPropagation();
                
                var target = tabBtn.getAttribute('data-day-tab');
                var scope = tabBtn.closest('section');
                if (!scope) return;

                scope.querySelectorAll('[data-day-tab]').forEach(function (b) {
                    var active = b === tabBtn;
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

                var dateLabel = scope.querySelector('#dynamic-date-label span');
                if (dateLabel) {
                    dateLabel.textContent = tabBtn.getAttribute('data-day-full');
                }
            }

            var prevBtn = e.target.closest('[data-carousel-prev]');
            if (prevBtn) {
                e.preventDefault();
                e.stopPropagation();
                var wrap = prevBtn.closest('.relative');
                var track = wrap ? wrap.querySelector('[data-carousel-track]') : null;
                var card = track ? track.querySelector(':scope > *') : null;
                if(track && card) {
                    track.scrollBy({ left: -(card.getBoundingClientRect().width + 20), behavior: 'smooth' });
                }
            }

            var nextBtn = e.target.closest('[data-carousel-next]');
            if (nextBtn) {
                e.preventDefault();
                e.stopPropagation();
                var wrap = nextBtn.closest('.relative');
                var track = wrap ? wrap.querySelector('[data-carousel-track]') : null;
                var card = track ? track.querySelector(':scope > *') : null;
                if(track && card) {
                    track.scrollBy({ left: (card.getBoundingClientRect().width + 20), behavior: 'smooth' });
                }
            }
        });
    </script>
@endonce