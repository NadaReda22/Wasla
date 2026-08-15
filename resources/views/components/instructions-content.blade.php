@php
    $steps = [
        ['number' => '01', 'title' => 'Read All Policies First', 'desc' => 'Before booking, read our cancellation and payment policies. Free cancellation up to 2 hours before departure.', 'icon' => '📋'],
        ['number' => '02', 'title' => 'Choose Your Route & Time', 'desc' => 'Select your destination and preferred departure time. Check seat availability — popular routes fill fast.', 'icon' => '🗺️'],
        ['number' => '03', 'title' => 'Enter Your Hotel Name', 'desc' => "We need your exact hotel name for pickup. We serve 80+ hotels. If your hotel isn't listed, contact us on WhatsApp.", 'icon' => '🏨'],
        ['number' => '04', 'title' => 'Pay $1 Deposit Online', 'desc' => 'Secure your seat with a $1 online deposit via Stripe. The remaining $3 is paid on board to the driver.', 'icon' => '💳'],
        ['number' => '05', 'title' => 'Receive WhatsApp Confirmation', 'desc' => "You'll get an instant WhatsApp message with your booking details, pickup time, and driver's contact.", 'icon' => '📱'],
        ['number' => '06', 'title' => 'Be Ready 5 Minutes Early', 'desc' => 'Buses depart on schedule. Be at your pickup point 5 minutes before departure. Driver will wait max 5 minutes.', 'icon' => '⏰'],
    ];

    $doList = [
        'Bring your booking confirmation (screenshot or WhatsApp message)',
        'Have the remaining $3 ready in cash (EGP, USD, or EUR)',
        'Arrive at pickup point 5 minutes before departure',
        'Keep your WhatsApp notifications on for driver updates',
        'Bring water — Sharm can be very hot, especially in summer',
        'Confirm your return trip time with the driver if booking round-trip',
    ];

    $dontList = [
        "Don't be late — buses depart on schedule and won't wait more than 5 minutes",
        "Don't bring more than 1 large bag + 1 carry-on per person",
        "Don't smoke or consume alcohol on the bus",
        "Don't book without reading the cancellation policy",
        "Don't share your booking confirmation with others — it's personal",
        "Don't negotiate prices — all fares are fixed and transparent",
    ];

    $importantNotes = [
        [
            'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h16a3 3 0 003-3V8a3 3 0 00-3-3H5a3 3 0 00-3 3v8a3 3 0 003 3z',
            'title' => 'Russian Tourists',
            'text' => 'If you\'re from Russia, Ukraine, or Belarus, you can book without an online deposit. Select "Cash on Board" and pay the full $4 to the driver.',
        ],
        [
            'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            'title' => 'Departure Times',
            'text' => 'All times shown are local Sharm El Sheikh time (UTC+3). Buses depart exactly on schedule — no delays for late passengers.',
        ],
        [
            'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z',
            'title' => 'Pickup Locations',
            'text' => 'Your exact pickup point is confirmed via WhatsApp after booking. For hotels not in our list, we arrange a nearby meeting point.',
        ],
        [
            'icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
            'title' => 'Need Help?',
            'text' => 'WhatsApp us at +20 123 456 7890 before booking if you have any questions. We reply within 10 minutes during operating hours (6AM–10PM).',
        ],
    ];
@endphp

{{-- Steps --}}
<div class="mb-16">
    <h2 class="text-2xl sm:text-3xl font-display font-bold text-navy mb-10 text-center">How Booking Works — Step by Step</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach ($steps as $step)
            <div class="bg-sand rounded-3xl p-7 border border-sand-dark relative overflow-hidden">
                <div class="absolute top-4 right-4 text-4xl font-bold text-navy/10 font-tabular select-none">{{ $step['number'] }}</div>
                <div class="text-3xl mb-4">{{ $step['icon'] }}</div>
                <h3 class="font-bold text-navy text-base mb-2">{{ $step['title'] }}</h3>
                <p class="text-navy/60 text-sm leading-relaxed">{{ $step['desc'] }}</p>
            </div>
        @endforeach
    </div>
</div>

{{-- Do / Don't --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
    <div class="bg-green-50 rounded-3xl p-8 border border-green-200">
        <div class="flex items-center gap-3 mb-6">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <h3 class="font-bold text-navy text-xl">Do ✅</h3>
        </div>
        <ul class="space-y-3">
            @foreach ($doList as $item)
                <li class="flex items-start gap-3 text-sm text-navy leading-relaxed">
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ $item }}
                </li>
            @endforeach
        </ul>
    </div>
    <div class="bg-red-50 rounded-3xl p-8 border border-red-200">
        <div class="flex items-center gap-3 mb-6">
            <svg class="w-6 h-6 text-coral" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            <h3 class="font-bold text-navy text-xl">Don't ❌</h3>
        </div>
        <ul class="space-y-3">
            @foreach ($dontList as $item)
                <li class="flex items-start gap-3 text-sm text-navy leading-relaxed">
                    <svg class="w-4 h-4 text-coral flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    {{ $item }}
                </li>
            @endforeach
        </ul>
    </div>
</div>

{{-- Important notes --}}
<div class="bg-amber-50 border-2 border-amber-300 rounded-3xl p-8">
    <h3 class="font-bold text-amber-800 text-xl mb-6 flex items-center gap-2">
        <svg class="w-5.5 h-5.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        Important Notes for All Tourists
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach ($importantNotes as $note)
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-amber-200 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-4.5 h-4.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $note['icon'] }}" /></svg>
                </div>
                <div>
                    <div class="font-bold text-amber-800 text-sm mb-1">{{ $note['title'] }}</div>
                    <p class="text-amber-700 text-xs leading-relaxed">{{ $note['text'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>