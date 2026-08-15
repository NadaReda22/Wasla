{{--
    About section — image mosaic + values bento grid
    resources/views/components/about-section.blade.php

    Usage:
        <x-about-section />
--}}
@php
    $sharmImages = [
        [
            'id' => 'sharm-1',
            'src' => 'images/sharm-showcase/sharm1.png',
            'alt' => 'Naama Bay Sharm El Sheikh crystal clear turquoise water and white sandy beach with palm trees',
        ],
        [
            'id' => 'sharm-2',
            'src' => 'images/sharm-showcase/sharm2.png',
            'alt' => 'Sharm El Sheikh Red Sea coastline with colorful coral reef visible through clear water',
        ],
        [
            'id' => 'sharm-3',
            'src' => 'images/sharm-showcase/sharm3.png',
            'alt' => 'Dahab Blue Hole diving site with turquoise water and desert mountains in background',
        ],
        [
            'id' => 'sharm-4',
            'src' => 'images/sharm-showcase/sharm4.png',
            'alt' => 'Ras Mohamed National Park underwater coral garden with tropical fish in Red Sea Egypt',
        ],
    ];

    $values = [
        [
            'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            'title' => 'Our Mission',
            'text' => 'Make tourist transport in Sharm El Sheikh affordable, reliable, and stress-free for every visitor — regardless of nationality or budget.',
        ],
        [
            'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
            'title' => 'Our Story',
            'text' => 'Founded in 2024 by local transport veterans tired of seeing tourists overpay for taxis. We partnered with licensed bus operators to build a shared-ride network that benefits everyone.',
        ],
        [
            'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-2a4 4 0 100-8 4 4 0 000 8zm6 3v2m-3-1h6',
            'title' => 'Who We Are',
            'text' => 'A team based in Sharm El Sheikh — drivers, coordinators, and tech staff — all passionate about showing tourists the best of Sinai.',
        ],
        [
            'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
            'title' => 'Our Growth',
            'text' => 'From a handful of buses in 2024 to a growing fleet today. Over 10,000 tourists served, with a route network still expanding.',
        ],
    ];
@endphp

<section class="section-padding bg-white overflow-hidden" id="about">
    <div class="container-max">
        <div class="text-center mb-16">
            <div class="inline-flex items-center gap-2 bg-turquoise/15 text-turquoise-dark px-4 py-2 rounded-full text-sm font-semibold mb-4">
                🌊 About Wasla
            </div>
            <h2 class="text-3xl sm:text-4xl font-display font-bold text-navy mb-4">Born in Sharm, Built for Tourists</h2>
            <p class="text-navy/60 text-lg max-w-2xl mx-auto">
                We're not a taxi app. We're a dedicated tourist transport startup solving the #1 complaint of Sharm visitors: overpriced, unreliable rides.
            </p>
        </div>

        {{-- Image mosaic --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-16 rounded-3xl overflow-hidden">
            @foreach ($sharmImages as $i => $img)
                <div
                    class="relative overflow-hidden group {{ $i === 0 ? 'row-span-2 md:row-span-1' : '' }}"
                    style="aspect-ratio: {{ $i === 0 ? '1/1.2' : '1/1' }};"
                >
                    <img
                        src="{{ $img['src'] }}"
                        alt="{{ $img['alt'] }}"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                        loading="lazy"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-navy/40 to-transparent"></div>
                </div>
            @endforeach
        </div>

        {{-- Values bento grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-16">
            @foreach ($values as $i => $v)
                <div class="rounded-3xl p-8 border {{ $i % 2 === 0 ? 'bg-sand border-sand' : 'bg-navy border-navy text-white' }}">
                    <svg
                        class="w-8 h-8 mb-4 {{ $i % 2 === 0 ? 'text-primary' : 'text-gold' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $v['icon'] }}" />
                    </svg>
                    <h3 class="text-xl font-display font-bold mb-3 {{ $i % 2 === 0 ? 'text-navy' : 'text-white' }}">
                        {{ $v['title'] }}
                    </h3>
                    <p class="text-sm leading-relaxed {{ $i % 2 === 0 ? 'text-navy/60' : 'text-white/70' }}">
                        {{ $v['text'] }}
                    </p>
                </div>
            @endforeach
        </div>

        <div class="text-center">
            <a href="{{ route('booking.create') }}" class="btn-coral px-10 py-4 text-base rounded-2xl inline-flex">
                Book Your First Ride — $4 Total, $2 Deposit
            </a>
            <p class="text-navy/50 text-sm mt-3">No hidden fees. Cancel free up to 12 hours before.</p>
        </div>
    </div>
</section>