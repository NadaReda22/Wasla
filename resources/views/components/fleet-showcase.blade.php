@php
    $settings = \App\Models\Setting::first();
    $dbImages = is_array($settings?->fleet_images) ? $settings->fleet_images : [];

    // The hardcoded defaults ensuring the system never breaks
    $defaults = [
        ['default_path' => 'images/bus-showcase/vehicle1.png', 'alt' => 'Modern white tourist vehicle', 'label' => __('fleet.bus_ext_main'), 'type' => 'exterior'],
        ['default_path' => 'images/bus-showcase/vehicle2.png', 'alt' => 'Luxury tourist vehicle exterior', 'label' => __('fleet.bus_ext_airport'), 'type' => 'exterior'],
        ['default_path' => 'images/bus-showcase/vehicle3.png', 'alt' => 'Interior of air-conditioned tourist vehicle', 'label' => __('fleet.bus_int_seats'), 'type' => 'interior'],
        ['default_path' => 'images/bus-showcase/vehicle6.png', 'alt' => 'Vehicle interior showing panoramic windows', 'label' => __('fleet.bus_int_windows'), 'type' => 'interior'],
        ['default_path' => 'images/bus-showcase/vehicle5.png', 'alt' => 'Modern tourist minibus interior', 'label' => __('fleet.bus_int_premium'), 'type' => 'interior'],
        ['default_path' => 'images/bus-showcase/vehicle4.png', 'alt' => 'Tourist vehicle driving along coastal road', 'label' => __('fleet.bus_ext_coastal'), 'type' => 'exterior'],
        ['default_path' => 'images/bus-showcase/vehicle7.png', 'alt' => 'Modern tourist minibus interior', 'label' => __('fleet.bus_int_premium'), 'type' => 'interior'],
    ];

    $fleetImages = collect($defaults)->map(function ($default, $index) use ($dbImages) {
        $dbRow = $dbImages[$index] ?? null;
        
        // THE MAGIC FALLBACK LOGIC:
        // If the admin uploaded a file, it lives in storage. 
        // If they didn't (or pressed "Reset to Defaults"), we use the beautiful public/images files!
        $imageSrc = !empty($dbRow['image']) 
            ? asset('storage/' . $dbRow['image']) 
            : asset($default['default_path']);

        return [
            'id' => 'vehicle-' . $index,
            'src' => $imageSrc,
            'alt' => !empty($dbRow['alt']) ? $dbRow['alt'] : $default['alt'],
            'label' => !empty($dbRow['label']) ? $dbRow['label'] : $default['label'],
            'type' => !empty($dbRow['type']) ? $dbRow['type'] : $default['type'],
        ];
    })->toArray();

    $features = [
        ['icon' => 'wind', 'label' => __('fleet.feature_ac_title'), 'desc' => __('fleet.feature_ac_desc')],
        ['icon' => 'clock', 'label' => __('fleet.feature_time_title'), 'desc' => __('fleet.feature_time_desc')],
        ['icon' => 'shield-check', 'label' => __('fleet.feature_safety_title'), 'desc' => __('fleet.feature_safety_desc')],
        ['icon' => 'star', 'label' => __('fleet.feature_star_title'), 'desc' => __('fleet.feature_star_desc')],
    ];

    $filterLabels = [
        'all' => __('fleet.filter_all'),
        'exterior' => __('fleet.filter_exterior'),
        'interior' => __('fleet.filter_interior'),
    ];
@endphp

@if(count($fleetImages) > 0)
<section class="py-24 bg-navy overflow-hidden" id="fleet"
    x-data="{
        images: {{ Illuminate\Support\Js::from($fleetImages) }},
        filterLabels: {{ Illuminate\Support\Js::from($filterLabels) }},
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

        <div class="text-center mb-14">
            <div class="inline-flex items-center gap-2 bg-gold/20 text-gold px-4 py-2 rounded-full text-sm font-semibold mb-4">
                {{ __('fleet.badge') }}
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-4">
                {{ __('fleet.heading') }}
            </h2>
            <p class="text-blue-200 text-lg max-w-xl mx-auto">
                {{ __('fleet.desc') }}
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="relative">
                <div class="flex gap-2 mb-4">
                    @foreach (['all', 'exterior', 'interior'] as $f)
                        <button
                            @click="setFilter('{{ $f }}')"
                            :class="filter === '{{ $f }}' ? 'bg-gold text-navy' : 'bg-white/10 text-blue-200 hover:bg-white/20'"
                            class="px-4 py-1.5 rounded-full text-xs font-semibold transition-all">
                            {{ $filterLabels[$f] }}
                        </button>
                    @endforeach
                </div>

                <div class="relative rounded-3xl overflow-hidden aspect-[4/3] bg-navy/50 shadow-2xl">
                    <img :src="current.src" :alt="current.alt"
                         class="w-full h-full object-cover transition-all duration-500">

                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                    <div class="absolute bottom-4 left-4 right-4 flex items-end justify-between">
                        <span class="bg-black/50 backdrop-blur-sm text-white text-xs font-semibold px-3 py-1.5 rounded-full" x-text="current.label"></span>
                        <span class="bg-gold text-navy text-xs font-bold px-3 py-1.5 rounded-full capitalize" x-text="filterLabels[current.type] || current.type"></span>
                    </div>

                    <button @click="prev()" aria-label="Previous vehicle image"
                        class="absolute left-3 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-black/60 text-white rounded-full p-2 transition-all">
                        <x-lucide-chevron-left class="w-5 h-5" />
                    </button>
                    <button @click="next()" aria-label="Next vehicle image"
                        class="absolute right-3 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-black/60 text-white rounded-full p-2 transition-all">
                        <x-lucide-chevron-right class="w-5 h-5" />
                    </button>
                </div>

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

            <div>
                <h3 class="text-2xl font-extrabold text-white mb-6">{{ __('fleet.features_heading') }}</h3>
                <div class="grid grid-cols-2 gap-4 mb-8">
                    @foreach ($features as $f)
                        <div class="bg-white/8 backdrop-blur-sm rounded-2xl p-5 border border-white/10">
                            <x-dynamic-component :component="'lucide-' . $f['icon']" class="w-6 h-6 text-gold mb-3" />
                            <div class="font-bold text-white text-sm mb-1">{{ $f['label'] }}</div>
                            <div class="text-blue-200 text-xs leading-relaxed">{{ $f['desc'] }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="bg-white/8 rounded-2xl p-6 border border-white/10">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        @foreach ([
                            ['value' => '14', 'label' => __('fleet.stat_pax')],
                            ['value' => '2026', 'label' => __('fleet.stat_year')],
                            ['value' => '100%', 'label' => __('fleet.stat_inspected')]
                        ] as $s)
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
@endif