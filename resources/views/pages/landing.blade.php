@extends('layouts.app')
@section('content')
<div>
    <section class="relative min-h-[90vh] flex items-center overflow-hidden gradient-navy">
        <div class="absolute inset-0">
            <img src="/images/bg2.png" alt="Sharm El Sheikh" class="w-full h-full object-cover opacity-70" />
        </div>
        <div class="absolute inset-0 bg-gradient-to-b from-navy/70 via-navy/50 to-navy"></div>

        <div class="container-max px-4 sm:px-6 lg:px-8 relative z-10 pt-20">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 badge-gold mb-6 animate-fade-in">
                    <span class="w-2 h-2 bg-gold rounded-full animate-pulse"></span>
                    Daily Tourist Transport in Sharm El Sheikh
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-display font-bold text-white leading-tight mb-6 animate-slide-up text-balance">
                    Comfortable Bus Rides
                    <span class="block text-gold mt-2">for just $4 per route</span>
                </h1>
                <p class="text-lg sm:text-xl text-white/80 mb-8 leading-relaxed max-w-2xl animate-slide-up">
                    Air-conditioned buses that take you between your hotel, the airport, and
                    tourist attractions across Sharm El Sheikh. Book your seat in minutes with
                    just a $1 deposit.
                </p>
                <div class="flex flex-wrap gap-4 animate-slide-up">
                    <a href="{{ route('booking.create') }}" class="btn-gold text-lg px-8 py-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Book Your Seat
                    </a>
                    <a href="#tours" class="btn-outline text-lg px-8 py-4 border-white/30 text-white hover:bg-white hover:text-navy">
                        Browse Tours
                    </a>
                </div>

                <div class="grid grid-cols-3 gap-6 mt-12 max-w-lg">
                    <div>
                        <div class="text-3xl font-bold text-gold">6+</div>
                        <div class="text-white/60 text-sm">Destinations</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gold">$4</div>
                        <div class="text-white/60 text-sm">Flat Rate</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gold">$1</div>
                        <div class="text-white/60 text-sm">Deposit Only</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-white to-transparent"></div>
    </section>

    <div class="bg-coral text-white py-3 overflow-hidden">
        <div class="container-max px-4 flex items-center justify-center gap-3 text-center">
            <svg class="w-5 h-5 flex-shrink-0 animate-pulse" fill="currentColor" viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
            <p class="font-semibold text-sm sm:text-base">
                Book the round trip offer and save 25% — for a limited time!
            </p>
        </div>
    </div>

    {{-- Tours schedule — Today + upcoming days, moved to its own file at
         resources/views/components/tours-schedule.blade.php
         Once real trip data exists, pass it in as $scheduleSeed, e.g.:
         <x-tours-schedule :schedule-seed="$scheduleSeed" /> --}}
    <x-tours-schedule />

    {{-- Fleet showcase — moved to its own file at
         resources/views/components/sections/bus-showcase.blade.php
         Laravel auto-registers anything under resources/views/components/
         as a component, so this one line renders the whole section. --}}
    <x-bus-showcase />

    {{-- About — image mosaic + values bento grid, moved to its own file at
         resources/views/components/about-section.blade.php --}}
    <x-about-section />

    <section class="section-padding bg-sand-light">
        <div class="container-max">
            <div class="text-center mb-12">
                <span class="badge-gold mb-3">How It Works</span>
                <h2 class="text-3xl sm:text-4xl font-display font-bold text-navy">Book in 3 Easy Steps</h2>
            </div>

            @php
                $steps = [
                    ['num' => '1', 'title' => 'Choose Your Tour', 'desc' => 'Browse available tours and pick your destination. All routes are $4 flat.', 'icon' => 'M3 10h18M3 6h18M3 14h18M3 18h18'],
                    ['num' => '2', 'title' => 'Pay $1 Deposit', 'desc' => 'Fill in your details and pay just $1 deposit online to secure your seat.', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h16a3 3 0 003-3V8a3 3 0 00-3-3H5a3 3 0 00-3 3v8a3 3 0 003 3z'],
                    ['num' => '3', 'title' => 'Enjoy the Ride', 'desc' => 'Pay the remaining $3 in cash on the bus. We will pick you up on time.', 'icon' => 'M5 13l4 4L19 7'],
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

    {{-- Coverage area — destination list + stylized map mockup, moved to its own file at
         resources/views/components/coverage-map.blade.php
         Swap the mockup for the real <x-static-map /> embed whenever you're ready to go live. --}}
    <x-coverage-map />

    <section class="section-padding bg-white">
        <div class="container-max">
            <div class="text-center mb-10">
                <span class="badge-gold mb-3">FAQ</span>
                <h2 class="text-3xl sm:text-4xl font-display font-bold text-navy">Frequently Asked Questions</h2>
            </div>

            @php
                $faqs = [
                    ['q' => 'How much does a ride cost?', 'a' => 'Every route is a flat $4. You pay just $1 as a deposit online, and the remaining $3 in cash on the bus.'],
                    ['q' => 'How do I pay the deposit?', 'a' => 'You can pay the $1 deposit online using our secure payment gateway. For countries with banking restrictions (like Russia), you can pay the full $4 in cash on the bus.'],
                    ['q' => 'How early should I book?', 'a' => 'We recommend booking at least 24 hours in advance to guarantee your seat, especially during peak tourist season.'],
                    ['q' => 'Can I cancel my booking?', 'a' => 'Yes, you can cancel for free up to 12 hours before your trip. Contact us via WhatsApp to cancel.'],
                ];
            @endphp

            <div class="max-w-3xl mx-auto space-y-4">
                @foreach($faqs as $faq)
                    <details class="card p-5 group cursor-pointer">
                        <summary class="font-semibold text-navy flex items-center justify-between list-none marker:hidden">
                            {{ $faq['q'] }}
                            <svg class="w-5 h-5 text-primary transition-transform group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="text-navy/60 mt-3 leading-relaxed">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>

            <div class="text-center mt-8">
                <a href="{{ route('faq') }}" class="btn-outline">
                    See All FAQs
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- Policies & Terms — booking, cancellation, conduct, payment, delays, privacy,
         moved to its own file at resources/views/components/policies-section.blade.php --}}
    <x-policies-section />

    <section class="section-padding gradient-sea relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-gold rounded-full blur-3xl"></div>
        </div>
        <div class="container-max relative z-10 text-center">
            <h2 class="text-3xl sm:text-5xl font-display font-bold text-white mb-6 text-balance">Ready for your next trip?</h2>
            <p class="text-white/80 text-lg mb-8 max-w-2xl mx-auto">Join thousands of tourists enjoying comfortable, affordable transport in Sharm El Sheikh</p>
            <a href="{{ route('booking.create') }}" class="btn-gold text-lg px-10 py-4">
                Start Booking Now
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            </a>
        </div>
    </section>
</div>
@endsection