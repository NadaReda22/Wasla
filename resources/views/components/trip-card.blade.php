@props(['trip'])

<div class="card overflow-hidden group hover:-translate-y-1">
    <div class="relative h-48 overflow-hidden">
        @if ($trip->image_url)
            <img
                src="{{ $trip->image_url }}"
                alt="{{ $trip->title }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                loading="lazy"
            />
        @else
            <div class="w-full h-full gradient-sea flex items-center justify-center">
                <svg class="w-16 h-16 text-white/50" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4 16c0 .88.39 1.67 1 2.22V20a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-2c0-.55.45-1 1-1h1zm16 0c.55 0 1 .45 1 1v2a1 1 0 0 1-1 1h-1a1 1 0 0 1-1-1v-1.78c.61-.55 1-1.34 1-2.22h1zM12 2C7.58 2 4 5.58 4 10v6h16v-6c0-4.42-3.58-8-8-8z" />
                </svg>
            </div>
        @endif
        <div class="absolute top-3 left-3">
            <span class="badge-gold shadow-md">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L2 7l10 5 10-5-10-5z" />
                </svg>
                {{ $trip->bus_type ?? 'AC Bus' }}
            </span>
        </div>
        <div class="absolute top-3 right-3">
            <span class="badge-coral shadow-md text-xs">
                $1 Deposit
            </span>
        </div>
        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-navy/90 to-transparent p-4">
            <div class="flex items-center gap-2 text-white/90 text-sm">
                <svg class="w-4 h-4 text-gold" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z" />
                </svg>
                {{ $trip->origin }} → {{ $trip->destination }}
            </div>
        </div>
    </div>

    <div class="p-5">
        <h3 class="font-bold text-lg text-navy mb-2 line-clamp-1">{{ $trip->title }}</h3>
        <p class="text-navy/60 text-sm mb-4 line-clamp-2 leading-relaxed">{{ $trip->description }}</p>

        <div class="flex items-center gap-4 mb-4 text-sm text-navy/70">
            <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-turquoise" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ $trip->duration_minutes }} min
            </div>
            <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-turquoise" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                {{ $trip->capacity }} seats
            </div>
        </div>

        <div class="flex items-end justify-between pt-4 border-t border-gray-100">
            <div>
                <span class="text-navy/50 text-xs">Flat rate</span>
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-primary">${{ $trip->flat_price ?? 4 }}</span>
                    <span class="text-navy/50 text-sm">/ seat</span>
                </div>
                <p class="text-xs text-coral font-semibold mt-0.5">$1 deposit online</p>
            </div>
            <a
                href="{{ url("/booking/{$trip->id}") }}"
                class="btn-primary text-sm px-5 py-2.5"
            >
                Book
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </div>
</div>