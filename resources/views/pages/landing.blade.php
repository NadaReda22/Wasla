@extends('layouts.app')

@section('content')

@php
    $minCost = (float) ($settings?->minimum_tour_cost_dollar ?? 4.00);
    $depCost = (float) ($settings?->deposit_amount_dollar ?? 2.00);
    $remCost = max(0.0, $minCost - $depCost);
    $brandName = $settings?->brand_name ?? 'Wasla'; // Used for dynamic FAQ subtitle

    // Dynamic Settings Translation Check for Offer Bar
    $offerBarText = app()->getLocale() === 'ru' && !empty($settings?->offer_bar_text_ru) 
        ? $settings->offer_bar_text_ru 
        : ($settings?->offer_bar_text ?: __('home.offer_bar_fallback'));
@endphp

<div>
    <section class="relative min-h-[75vh] lg:min-h-[90vh] flex items-center overflow-hidden gradient-navy py-16 lg:py-0">
    <div class="absolute inset-0">
        <img src="{{ !empty($settings?->landing_background) ? asset('storage/' . $settings->landing_background) : '/images/landing-background.png' }}" 
            alt="Sharm El Sheikh" 
            class="w-full h-full object-cover opacity-70" />
    </div>
    <div class="absolute inset-0 bg-gradient-to-b from-navy/70 via-navy/50 to-navy"></div>

    <div class="container-max px-4 sm:px-6 lg:px-8 relative z-10 pt-24 md:pt-20">
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-1.5 md:gap-2 badge-gold mb-4 md:mb-6 animate-fade-in text-xs md:text-sm px-2.5 py-1 md:px-3 md:py-1.5">
                <span class="w-1.5 h-1.5 md:w-2 md:h-2 bg-gold rounded-full animate-pulse"></span>
                {{ __('home.hero_badge') }}
            </div>

            <h1 class="text-3xl md:text-5xl lg:text-6xl font-display font-bold text-white whitespace-normal md:whitespace-nowrap leading-tight mb-4 md:mb-6 animate-slide-up">
                {{ __('home.hero_title') }}
                <span class="block text-gold mt-1 md:mt-2">{{ __('home.hero_price_line', ['price' => '$' . number_format($minCost, 2)]) }}</span>
            </h1>

            <p class="text-base md:text-xl text-white/80 mb-6 md:mb-8 leading-relaxed max-w-2xl animate-slide-up">
                {{ __('home.hero_desc', ['deposit' => '$' . number_format($depCost, 2)]) }}
            </p>
            
            <div class="flex flex-wrap gap-3 md:gap-4 animate-slide-up">
                <a href="{{ route('booking.create') }}" class="btn-gold text-base md:text-lg px-5 py-3 md:px-8 md:py-4 flex items-center">
                    <svg class="w-4 h-4 md:w-5 md:h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ __('home.book_seat') }}
                </a>
                <a href="#tours" class="btn-outline text-base md:text-lg px-5 py-3 md:px-8 md:py-4 border-white/30 text-white hover:bg-white hover:text-navy">
                    {{ __('home.browse_tours') }}
                </a>
            </div>

            <div class="grid grid-cols-3 gap-2 md:gap-6 mt-8 md:mt-12 max-w-lg">
                <div>
                    <div class="text-xl md:text-3xl font-bold text-gold">{{ count($areas) }}+</div>
                    <div class="text-[#195E9D] font-semibold text-xs md:text-sm">{{ __('home.destinations') }}</div>
                </div>
                <div>
                    <div class="text-xl md:text-3xl font-bold text-gold">${{ number_format($minCost, 2) }}</div>
                    <div class="text-[#195E9D] font-semibold text-xs md:text-sm">{{ __('home.flat_rate') }}</div>
                </div>
                <div>
                    <div class="text-xl md:text-3xl font-bold text-gold">${{ number_format($depCost, 2) }}</div>
                    <div class="text-[#195E9D] font-semibold text-xs md:text-sm">{{ __('home.deposit_only') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="absolute bottom-0 left-0 right-0 h-16 md:h-24 bg-gradient-to-t from-white to-transparent"></div>
</section>

    {{-- Dynamic Offer Bar --}}
    <div class="bg-coral text-white py-3 overflow-hidden">
        <div class="container-max px-4 flex items-center justify-center gap-3 text-center">
            <svg class="w-5 h-5 flex-shrink-0 animate-pulse" fill="currentColor" viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
            <p class="font-semibold text-sm sm:text-base">
                {{ $offerBarText }}
            </p>
        </div>
    </div>

    <!-- Tours Schedule Component -->
    <x-tours-schedule :days="$days" />

   <x-fleet-showcase />

    <x-about-section />

    <section class="section-padding bg-sand-light">
        <div class="container-max">
            <div class="text-center mb-12">
                <span class="badge-gold mb-3">{{ __('home.how_it_works') }}</span>
                <h2 class="text-3xl sm:text-4xl font-display font-bold text-navy">{{ __('home.book_in_3_steps') }}</h2>
            </div>

            @php
                $steps = [
                    ['num' => '1', 'title' => __('home.step1_title'), 'desc' => __('home.step1_desc', ['price' => '$' . number_format($minCost, 2)]), 'icon' => 'M3 10h18M3 6h18M3 14h18M3 18h18'],
                    ['num' => '2', 'title' => __('home.step2_title', ['deposit' => '$' . number_format($depCost, 2)]), 'desc' => __('home.step2_desc', ['deposit' => '$' . number_format($depCost, 2)]), 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h16a3 3 0 003-3V8a3 3 0 00-3-3H5a3 3 0 00-3 3v8a3 3 0 003 3z'],
                    ['num' => '3', 'title' => __('home.step3_title'), 'desc' => __('home.step3_desc', ['remaining' => '$' . number_format($remCost, 2)]), 'icon' => 'M5 13l4 4L19 7'],
                ];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($steps as $step)
                    <div class="relative text-center group">
                        <div class="w-20 h-20 mx-auto rounded-full gradient-sea flex items-center justify-center mb-6 group-hover:scale-110 transition-transform shadow-lg shadow-primary/30">
                            <span class="text-3xl font-bold text-white">{{ $step['num'] }}</span>
                        </div>
                        <h3 class="font-bold text-xl text-navy mb-3">{{ $step['title'] }}</h3>
                        <p class="text-navy/60 leading-relaxed max-w-xs mx-auto">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @if(!($settings?->hide_coverage_area))
        <x-coverage-map :areas="$areas" />
    @endif

    <section class="section-padding bg-white">
        <div class="container-max">
            <div class="text-center mb-10">
                <span class="badge-gold mb-3">{{ __('home.faq_badge') }}</span>
                <h2 class="text-3xl sm:text-4xl font-display font-bold text-navy">{{ __('home.faq_heading') }}</h2>
                <p class="text-navy/60 mt-3 max-w-2xl mx-auto">{{ __('home.faq_subheading', ['brand' => $brandName]) }}</p> {{-- DYNAMIC BRAND HERE --}}
            </div>

            <div class="max-w-3xl mx-auto space-y-4">
                @forelse($faqs as $faq)
                    <details class="card p-5 group cursor-pointer">
                        <summary class="font-semibold text-navy flex items-center justify-between list-none marker:hidden">
                            {{ $faq->{'question_' . app()->getLocale()} ?? $faq->question_en }}
                            <svg class="w-5 h-5 text-primary transition-transform group-open:rotate-180 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <div class="text-navy/60 mt-3 leading-relaxed prose prose-sm max-w-none">
                            {!! $faq->{'answer_' . app()->getLocale()} ?? $faq->answer_en !!}
                        </div>
                    </details>
                @empty
                    <div class="text-center text-navy/50 py-8">
                        {{ __('home.faq_empty') }}
                    </div>
                @endforelse
            </div>

            <div class="text-center mt-8">
                <a href="{{ route('faq') }}" class="btn-outline">
                    {{ __('home.see_all_faqs') }}
                    <svg class="w-4 h-4 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </a>
            </div>
        </div>
    </section>

    <x-policies-section />

    <section class="section-padding gradient-sea relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-gold rounded-full blur-3xl"></div>
        </div>
        <div class="container-max relative z-10 text-center">
            <h2 class="text-3xl sm:text-5xl font-display font-bold text-white mb-6 text-balance">{{ __('home.cta_heading') }}</h2>
            <p class="text-white/80 text-lg mb-8 max-w-2xl mx-auto">{{ __('home.cta_desc') }}</p>
            <a href="{{ route('booking.create') }}" class="btn-gold text-lg px-10 py-4">
                {{ __('home.start_booking') }}
                <svg class="w-5 h-5 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            </a>
        </div>
    </section>
</div>
@endsection