@extends('layouts.app')

@section('content')
<div class="min-h-screen pt-20 pb-16">
    <div class="container-max px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-navy/60 hover:text-primary mb-6 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
            </svg>
            Back to Tours
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="rounded-2xl overflow-hidden shadow-lg">
                @if($trip->image_url)
                    <img src="{{ $trip->image_url }}" alt="{{ $trip->title }}" class="w-full h-80 object-cover" />
                @else
                    <div class="w-full h-80 gradient-sea flex items-center justify-center">
                        <svg class="w-20 h-20 text-white/50" fill="currentColor" viewBox="0 0 24 24"><path d="M4 16c0 .88.39 1.67 1 2.22V20a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-2c0-.55.45-1 1-1h1zm16 0c.55 0 1 .45 1 1v2a1 1 0 0 1-1 1h-1a1 1 0 0 1-1-1v-1.78c.61-.55 1-1.34 1-2.22h1zM12 2C7.58 2 4 5.58 4 10v6h16v-6c0-4.42-3.58-8-8-8z"/></svg>
                    </div>
                @endif
            </div>

            <div>
                <span class="badge-gold mb-3">{{ $trip->bus_type ?? 'AC Bus' }}</span>
                <h1 class="text-3xl font-display font-bold text-navy mb-3">{{ $trip->title }}</h1>
                <p class="text-navy/60 leading-relaxed mb-6">{{ $trip->description }}</p>

                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="card p-4 text-center">
                        <p class="text-xs text-navy/50 mb-1">Duration</p>
                        <p class="font-bold text-navy text-lg">{{ $trip->duration_minutes }} min</p>
                    </div>
                    <div class="card p-4 text-center">
                        <p class="text-xs text-navy/50 mb-1">Capacity</p>
                        <p class="font-bold text-navy text-lg">{{ $trip->capacity }} seats</p>
                    </div>
                    <div class="card p-4 text-center">
                        <p class="text-xs text-navy/50 mb-1">Flat Rate</p>
                        <p class="font-bold text-primary text-lg">${{ $trip->flat_price ?? 4 }}</p>
                    </div>
                </div>

                <div class="bg-sand-light rounded-xl p-4 mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-navy/60 text-sm">Deposit (online)</span>
                        <span class="font-bold text-coral">${{ $trip->deposit_amount ?? 1 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-navy/60 text-sm">Cash on bus</span>
                        <span class="font-bold text-turquoise">${{ number_format(($trip->flat_price ?? 4) - ($trip->deposit_amount ?? 1), 2) }}</span>
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="font-bold text-navy mb-3">Departure Times</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach(is_array($trip->departure_times) ? $trip->departure_times : explode(',', $trip->departure_times) as $time)
                            <span class="badge-turquoise">{{ trim($time) }}</span>
                        @endforeach
                    </div>
                </div>

                <a href="{{ route('booking.create', ['tripId' => $trip->id]) }}" class="btn-coral w-full text-lg py-4">
                    Book This Trip — $1 Deposit
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            @if($trip->bus_image_external)
                <div class="rounded-2xl overflow-hidden shadow-lg">
                    <img src="{{ $trip->bus_image_external }}" alt="Bus Exterior" class="w-full h-64 object-cover" />
                </div>
            @endif
            @if($trip->bus_image_internal)
                <div class="rounded-2xl overflow-hidden shadow-lg">
                    <img src="{{ $trip->bus_image_internal }}" alt="Bus Interior" class="w-full h-64 object-cover" />
                </div>
            @endif
        </div>

        <div class="mt-8">
            <h3 class="font-bold text-navy mb-4">Location on Map</h3>
            <div class="card p-2">
                <x-static-map 
                    :lat="$trip->destination_lat ?? 27.9158" 
                    :lng="$trip->destination_lng ?? 34.3299" 
                    :label="$trip->destination" 
                    width="1200" 
                    height="300" 
                />
            </div>
        </div>
    </div>
</div>
@endsection