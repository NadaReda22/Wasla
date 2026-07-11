{{--
    Coverage map section — destination list + simulated map mockup
    resources/views/components/coverage-map.blade.php

    Usage:
        <x-coverage-map />

    NOTE: The map below is a stylized SVG mockup, not a live map. Swap the
    "Simulated map" block for the real <x-static-map /> component (or a
    Google Maps Static API image) whenever you're ready to go live with an
    actual embed — the pin/tooltip markup can stay as-is either way.
--}}
@php
    $destinations = [
        ['id' => 'dest-naama', 'name' => 'Naama Bay', 'arabicName' => 'نعمة باي', 'x' => '52%', 'y' => '38%', 'color' => 'bg-primary'],
        ['id' => 'dest-rasmed', 'name' => 'Ras Mohamed', 'arabicName' => 'رأس محمد', 'x' => '30%', 'y' => '78%', 'color' => 'bg-turquoise'],
        ['id' => 'dest-airport', 'name' => 'Airport', 'arabicName' => 'المطار', 'x' => '68%', 'y' => '28%', 'color' => 'bg-navy'],
        ['id' => 'dest-oldmarket', 'name' => 'Old Market', 'arabicName' => 'السوق القديم', 'x' => '58%', 'y' => '50%', 'color' => 'bg-coral'],
        ['id' => 'dest-dahab', 'name' => 'Dahab', 'arabicName' => 'دهب', 'x' => '80%', 'y' => '20%', 'color' => 'bg-gold'],
        ['id' => 'dest-sharks', 'name' => "Shark's Bay", 'arabicName' => 'خليج الحوت', 'x' => '72%', 'y' => '42%', 'color' => 'bg-primary-400'],
    ];
@endphp

<section class="section-padding bg-sand-light" id="coverage">
    <div class="container-max">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            {{-- Left: copy --}}
            <div>
                <div class="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-2 rounded-full text-sm font-semibold mb-6">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Coverage Area
                </div>
                <h2 class="text-3xl sm:text-4xl font-display font-bold text-navy mb-6">
                    We Cover All of <span class="text-primary">Sharm El Sheikh</span>
                </h2>
                <p class="text-navy/60 text-lg mb-8 leading-relaxed">
                    From the airport to Ras Mohamed National Park, our buses connect every major tourist destination in Sharm El Sheikh — with hotel pickup available from over 80 registered hotels.
                </p>

                <div class="space-y-3">
                    @foreach ($destinations as $dest)
                        <div class="flex items-center gap-3 bg-white rounded-xl px-4 py-3 shadow-card">
                            <div class="w-3 h-3 rounded-full flex-shrink-0 {{ $dest['color'] }}"></div>
                            <span class="font-semibold text-navy text-sm">{{ $dest['name'] }}</span>
                            <span class="text-navy/50 text-xs mr-auto">{{ $dest['arabicName'] }}</span>
                            <svg class="w-3.5 h-3.5 text-navy/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Right: stylized map mockup --}}
            <div class="relative">
                <div class="rounded-3xl overflow-hidden shadow-2xl border-4 border-white">
                    <div class="relative h-[420px] w-full overflow-hidden">
                        <div
                            class="absolute inset-0"
                            style="background: linear-gradient(135deg, #e8f4f8 0%, #d4edf7 30%, #c8e8f0 50%, #b8e0ea 70%, #17A2B8 100%);"
                        ></div>

                        <svg class="absolute inset-0 w-full h-full" viewBox="0 0 600 420">
                            <path d="M 50 200 Q 200 150 300 200 Q 400 250 550 180" stroke="#FFFFFF" stroke-width="4" fill="none" opacity="0.6" />
                            <path d="M 100 100 L 300 200 L 450 350" stroke="#FFFFFF" stroke-width="3" fill="none" opacity="0.5" />
                            <path d="M 300 50 L 300 200 L 200 380" stroke="#FFFFFF" stroke-width="3" fill="none" opacity="0.5" />
                            <path d="M 150 300 Q 300 280 500 320" stroke="#FFFFFF" stroke-width="2" fill="none" opacity="0.4" />
                            <ellipse cx="480" cy="350" rx="150" ry="100" fill="#17A2B8" opacity="0.3" />
                            <text x="430" y="360" fill="#0A2540" font-size="12" font-weight="600" opacity="0.7">Red Sea</text>
                            <text x="415" y="376" fill="#0A2540" font-size="10" opacity="0.6">البحر الأحمر</text>
                        </svg>

                        @foreach ($destinations as $dest)
                            <div
                                class="absolute -translate-x-1/2 -translate-y-full cursor-pointer group"
                                style="left: {{ $dest['x'] }}; top: {{ $dest['y'] }};"
                            >
                                <div class="w-7 h-7 {{ $dest['color'] }} rounded-full border-2 border-white shadow-lg flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1112 6.5a2.5 2.5 0 010 5z" /></svg>
                                </div>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 bg-navy text-white text-xs font-semibold px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                    {{ $dest['name'] }}
                                </div>
                            </div>
                        @endforeach

                        <div class="absolute bottom-3 right-3 bg-white/80 text-xs text-navy/60 px-2 py-1 rounded">
                            Sharm El Sheikh Coverage Map
                        </div>
                    </div>
                </div>

                <div class="absolute -bottom-4 -left-4 bg-white rounded-2xl shadow-card-hover px-5 py-4 border border-navy/10">
                    <div class="text-2xl font-bold text-primary font-tabular">80+</div>
                    <div class="text-xs text-navy/50 font-medium">Hotel Pickups</div>
                </div>
            </div>
        </div>
    </div>
</section>