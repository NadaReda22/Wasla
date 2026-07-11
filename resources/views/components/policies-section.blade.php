{{--
    Policies & Terms section
    resources/views/components/policies-section.blade.php

    Usage:
        <x-policies-section />
--}}
@php
    $policies = [
        [
            'id' => 'policy-booking',
            'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'title' => 'Booking Policy',
            'color' => 'text-primary',
            'bg' => 'bg-primary/8',
            'items' => [
                'All bookings require a $1 deposit paid online at time of reservation.',
                'Remaining $3 balance is paid on board to the driver in cash (EGP, USD, or EUR accepted).',
                'Booking confirmation is sent instantly via WhatsApp to the number provided.',
                'Your seat is reserved for 15 minutes after booking — confirm via WhatsApp to hold longer.',
                'Group bookings (10+ passengers) must be made 24 hours in advance.',
            ],
        ],
        [
            'id' => 'policy-cancel',
            'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
            'title' => 'Cancellation & Refund Policy',
            'color' => 'text-turquoise',
            'bg' => 'bg-turquoise/8',
            'items' => [
                'Free cancellation up to 2 hours before scheduled departure — full $1 deposit refunded.',
                'Cancellations within 2 hours of departure: deposit is non-refundable.',
                'No-shows without prior cancellation: deposit forfeited, no refund.',
                'Refunds are processed within 5–7 business days to the original payment method.',
                'Cash bookings can be cancelled at any time with no charge.',
            ],
        ],
        [
            'id' => 'policy-conduct',
            'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
            'title' => 'Passenger Conduct',
            'color' => 'text-gold',
            'bg' => 'bg-gold/8',
            'items' => [
                'Be at the pickup point 5 minutes before departure. Buses depart on schedule.',
                'No smoking, alcohol, or food on board. Water bottles are permitted.',
                'Luggage limited to 1 large bag + 1 carry-on per passenger.',
                'Children under 12 must be accompanied by an adult.',
                'Wasla reserves the right to refuse service for disruptive behavior.',
            ],
        ],
        [
            'id' => 'policy-payment',
            'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h16a3 3 0 003-3V8a3 3 0 00-3-3H5a3 3 0 00-3 3v8a3 3 0 003 3z',
            'title' => 'Payment Policy',
            'color' => 'text-coral',
            'bg' => 'bg-coral/8',
            'items' => [
                'Online deposit ($1) accepted via Visa, Mastercard, and Stripe.',
                "Cash payment accepted in EGP, USD, EUR, and Russian Rubles (at driver's exchange rate).",
                'Russian, Ukrainian, and Belarusian tourists may use cash-only booking with no deposit required.',
                'Prices are fixed — no negotiation or surge pricing.',
                'Receipts available on request via WhatsApp.',
            ],
        ],
        [
            'id' => 'policy-delay',
            'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            'title' => 'Delays & Missed Buses',
            'color' => 'text-navy',
            'bg' => 'bg-navy/8',
            'items' => [
                'In case of traffic delay, Wasla will notify all passengers via WhatsApp.',
                "If you miss your bus, contact us immediately — we'll transfer you to the next available departure.",
                'Force majeure events (weather, road closures): full refund or free rebooking offered.',
                'Buses wait maximum 5 minutes past scheduled departure for late passengers.',
            ],
        ],
        [
            'id' => 'policy-privacy',
            'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
            'title' => 'Privacy Policy',
            'color' => 'text-primary-700',
            'bg' => 'bg-primary-700/8',
            'items' => [
                'We collect only the information needed to process your booking (name, WhatsApp, hotel).',
                'Your data is never sold to third parties.',
                'WhatsApp messages are used only for booking confirmation and trip updates.',
                'You may request deletion of your data at any time by contacting info@wasla.com.',
                'We comply with Egyptian data protection regulations.',
            ],
        ],
    ];
@endphp

<section class="section-padding bg-white" id="policies">
    <div class="container-max">
        <div class="text-center mb-14">
            <div class="inline-flex items-center gap-2 bg-navy/10 text-navy px-4 py-2 rounded-full text-sm font-semibold mb-4">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                Policies & Terms
            </div>
            <h2 class="text-3xl sm:text-4xl font-display font-bold text-navy mb-4">Clear, Fair Policies</h2>
            <p class="text-navy/60 text-lg max-w-xl mx-auto">
                No fine print. Everything you need to know about booking, payments, and your rights as a passenger.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach ($policies as $policy)
                <div class="card p-7">
                    <div class="w-12 h-12 {{ $policy['bg'] }} rounded-2xl flex items-center justify-center mb-5">
                        <svg class="w-5 h-5 {{ $policy['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $policy['icon'] }}" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-navy text-lg mb-4">{{ $policy['title'] }}</h3>
                    <ul class="space-y-2.5">
                        @foreach ($policy['items'] as $item)
                            <li class="flex items-start gap-2.5 text-sm text-navy/60 leading-relaxed">
                                <span class="w-1.5 h-1.5 rounded-full mt-2 flex-shrink-0 {{ str_replace('text-', 'bg-', $policy['color']) }}"></span>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</section>