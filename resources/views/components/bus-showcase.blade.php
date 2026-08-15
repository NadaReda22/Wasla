{{--
    Bus fleet showcase with filterable carousel.
    Blade has no client-side state, so the filter tabs / active image / prev-next
    buttons are handled with Alpine.js (x-data). Install it if you haven't:
        npm install alpinejs
    and in resources/js/app.js:
        import Alpine from 'alpinejs';
        window.Alpine = Alpine;
        Alpine.start();
--}}
@php
    $busImages = $busImages ?? [
        ['id' => 'bus-ext-1', 'src' => 'images/bus-showcase/vehicle1.png', 'alt' => 'Wasla modern white tourist coach bus exterior parked at Naama Bay Sharm El-Sheikh', 'label' => 'Exterior — Main Fleet Coach', 'type' => 'exterior'],
        ['id' => 'bus-ext-2', 'src' => 'images/bus-showcase/vehicle2.png', 'alt' => 'Wasla luxury tourist bus exterior front view with blue branding in Sharm El-Sheikh', 'label' => 'Exterior — Airport Transfer Bus', 'type' => 'exterior'],
        ['id' => 'bus-int-1', 'src' => 'images/bus-showcase/vehicle3.png', 'alt' => 'Interior of Wasla air-conditioned tourist bus with comfortable reclining seats and USB charging ports', 'label' => 'Interior — Reclining Seats & A/C', 'type' => 'interior'],
        ['id' => 'bus-int-2', 'src' => 'images/bus-showcase/vehicle6.png', 'alt' => 'Wasla bus interior showing clean aisle, overhead luggage racks and panoramic windows', 'label' => 'Interior — Panoramic Windows', 'type' => 'interior'],
        ['id' => 'bus-int-3', 'src' => 'images/bus-showcase/vehicle5.png', 'alt' => 'Modern tourist minibus interior with leather seats and air conditioning vents', 'label' => 'Interior — Premium Minibus', 'type' => 'interior'],
        ['id' => 'bus-ext-3', 'src' => 'images/bus-showcase/vehicle4.png', 'alt' => 'Tourist bus driving along Red Sea coastal road in Sharm El-Sheikh Egypt', 'label' => 'Exterior — Coastal Route', 'type' => 'exterior'],
        ['id' => 'bus-int-4', 'src' => 'images/bus-showcase/vehicle7.png', 'alt' => 'Modern tourist minibus interior with leather seats and air conditioning vents', 'label' => 'Interior — Premium Minibus', 'type' => 'interior'],
    ]; 

    $features = [
        ['icon' => 'wind', 'label' => 'Full A/C', 'desc' => 'Arctic-cold air conditioning on every bus'],
        ['icon' => 'usb', 'label' => 'USB Charging', 'desc' => 'Charge your devices on every seat'],
        ['icon' => 'wifi', 'label' => 'Free Wi-Fi', 'desc' => 'Stay connected throughout your journey'],
        ['icon' => 'star', 'label' => 'Rated 4.9★', 'desc' => 'Top-rated fleet by 8,200+ tourists'],
    ];
@endphp

<section class="py-24 bg-navy overflow-hidden" id="buses"
    x-data="{
        images: {{ Illuminate\Support\Js::from($busImages) }},
        filter: 'all',
        active: 0,
        get filtered() {
            return this.filter === 'all' ? this.images : this.images.filter(b => b.type === this.filter);
        },
        get current() {
            return this.filtered[this.active % this.filtered.length];
        },
        setFilter(f) { this.filter = f; this.active = 0; },
        prev() { this.active = (this.active - 1 + this.filtered.length) % this.filtered.length; },
        next() { this.active = (this.active + 1) % this.filtered.length; },
    }">
    <div class="max-w-screen-2xl mx-auto px-6 lg:px-10 xl:px-16">

        {{-- Header --}}
        <div class="text-center mb-14">
            <div class="inline-flex items-center gap-2 bg-gold/20 text-gold px-4 py-2 rounded-full text-sm font-semibold mb-4">
                🚌 Our Fleet
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-4">
                Comfortable Buses, Every Ride
            </h2>
            <p class="text-blue-200 text-lg max-w-xl mx-auto">
                Modern, air-conditioned coaches and minibuses — inspected daily, driven by licensed professionals.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            {{-- Carousel --}}
            <div class="relative">
                {{-- Filter tabs --}}
                <div class="flex gap-2 mb-4">
                    @foreach (['all', 'exterior', 'interior'] as $f)
                        <button
                            @click="setFilter('{{ $f }}')"
                            :class="filter === '{{ $f }}' ? 'bg-gold text-navy' : 'bg-white/10 text-blue-200 hover:bg-white/20'"
                            class="px-4 py-1.5 rounded-full text-xs font-semibold transition-all">
                            {{ ucfirst($f) }}
                        </button>
                    @endforeach
                </div>

                {{-- Main image --}}
                <div class="relative rounded-3xl overflow-hidden aspect-[4/3] bg-navy/50 shadow-2xl">
                    <img :src="current.src" :alt="current.alt"
                         class="w-full h-full object-cover transition-all duration-500">

                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                    <div class="absolute bottom-4 left-4 right-4 flex items-end justify-between">
                        <span class="bg-black/50 backdrop-blur-sm text-white text-xs font-semibold px-3 py-1.5 rounded-full" x-text="current.label"></span>
                        <span class="bg-gold text-navy text-xs font-bold px-3 py-1.5 rounded-full capitalize" x-text="current.type"></span>
                    </div>

                    <button @click="prev()" aria-label="Previous bus image"
                        class="absolute left-3 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-black/60 text-white rounded-full p-2 transition-all">
                        <x-lucide-chevron-left class="w-5 h-5" />
                    </button>
                    <button @click="next()" aria-label="Next bus image"
                        class="absolute right-3 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-black/60 text-white rounded-full p-2 transition-all">
                        <x-lucide-chevron-right class="w-5 h-5" />
                    </button>
                </div>

                {{-- Thumbnails --}}
                <div class="flex gap-2 mt-3 overflow-x-auto pb-1">
                    <template x-for="(img, i) in filtered" :key="img.id">
                        <button @click="active = i"
                            :class="i === (active % filtered.length) ? 'border-gold' : 'border-transparent opacity-60 hover:opacity-90'"
                            class="flex-shrink-0 w-16 h-12 rounded-xl overflow-hidden border-2 transition-all">
                            <img :src="img.src" :alt="img.alt" class="w-full h-full object-cover">
                        </button>
                    </template>
                </div>
            </div>

            {{-- Features --}}
            <div>
                <h3 class="text-2xl font-extrabold text-white mb-6">What's On Every Bus</h3>
                <div class="grid grid-cols-2 gap-4 mb-8">
                    @foreach ($features as $f)
                        <div class="bg-white/8 backdrop-blur-sm rounded-2xl p-5 border border-white/10">
                            <x-dynamic-component :component="'lucide-' . $f['icon']" class="w-6 h-6 text-gold mb-3" />
                            <div class="font-bold text-white text-sm mb-1">{{ $f['label'] }}</div>
                            <div class="text-blue-200 text-xs leading-relaxed">{{ $f['desc'] }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- Fleet stats --}}
                <div class="bg-white/8 rounded-2xl p-6 border border-white/10">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        @foreach ([['value' => '14', 'label' => 'Buses in Fleet'], ['value' => '2023', 'label' => 'Avg. Model Year'], ['value' => '100%', 'label' => 'Daily Inspected']] as $s)
                            <div>
                                <div class="text-2xl font-extrabold text-gold font-tabular">{{ $s['value'] }}</div>
                                <div class="text-blue-200 text-xs mt-1">{{ $s['label'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>