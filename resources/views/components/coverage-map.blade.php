@props(['areas' => collect()])

@php
    // $settings is automatically provided globally by AppServiceProvider
    $isHidden = $settings?->hide_coverage_area ?? false;
    
    // Fetch dynamic count of active hotels
    $hotelCount = \App\Models\Hotel::where('is_active', true)->count();
@endphp

@if(!$isHidden)
    @php
        $availableColors = ['bg-primary', 'bg-turquoise', 'bg-navy', 'bg-coral', 'bg-gold', 'bg-primary-400'];

        $destinations = $areas->map(function($area) use ($availableColors) {

            // The geographical bounds of your stylized map
            $minLat = 27.80; $maxLat = 28.05;
            $minLng = 34.20; $maxLng = 34.45;

            // 1. Calculate Center Pin Position
            if ($area->latitude && $area->longitude) {
                $xPct = max(5, min(95, (($area->longitude - $minLng) / ($maxLng - $minLng)) * 100));
                $yPct = max(5, min(95, (1 - ($area->latitude - $minLat) / ($maxLat - $minLat)) * 100));
                $x = round($xPct) . '%';
                $y = round($yPct) . '%';
            } else {
                $x = mt_rand(20, 80) . '%';
                $y = mt_rand(20, 80) . '%';
            }

            // 2. Map the Polygon Coordinates to SVG space (600x420)
            $svgPolygon = '';
            if (!empty($area->polygon_coordinates)) {
                $coords = is_string($area->polygon_coordinates) ? json_decode($area->polygon_coordinates, true) : $area->polygon_coordinates;
                
                if (is_array($coords)) {
                    $points = [];
                    foreach ($coords as $c) {
                        if (isset($c['lat']) && isset($c['lng'])) {
                            $px = (($c['lng'] - $minLng) / ($maxLng - $minLng)) * 600;
                            $py = (1 - ($c['lat'] - $minLat) / ($maxLat - $minLat)) * 420;
                            $points[] = round($px, 1) . ',' . round($py, 1);
                        }
                    }
                    $svgPolygon = implode(' ', $points);
                }
            }

            $color = $area->color ?: $availableColors[array_rand($availableColors)];
            
            // Convert Tailwind background class to text class for SVG stroke/fill
            $textColor = str_replace('bg-', 'text-', $color);

            return [
                'id' => 'dest-' . $area->id,
                'name' => app()->getLocale() === 'ru' && !empty($area->name_ru) ? $area->name_ru : $area->name_en,
                'arabicName' => $area->name_ar ?? '',
                'x' => $x,
                'y' => $y,
                'color' => $color,
                'text_color' => $textColor,
                'svg_polygon' => $svgPolygon,
            ];
        });
    @endphp

    <section class="section-padding bg-sand-light" id="coverage">
        <div class="container-max">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                {{-- Left: copy --}}
                <div>
                    <div class="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-2 rounded-full text-sm font-semibold mb-6">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {{ __('coverage.badge') }}
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-display font-bold text-navy mb-6">
                        {{ __('coverage.heading_pre') }} <span class="text-primary">{{ __('coverage.heading_highlight') }}</span>
                    </h2>
                    <p class="text-navy/60 text-lg mb-8 leading-relaxed">
                        {{-- Added dynamic count variable here --}}
                        {{ __('coverage.description', ['count' => $hotelCount]) }}
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
                            <div class="absolute inset-0" style="background: linear-gradient(135deg, #e8f4f8 0%, #d4edf7 30%, #c8e8f0 50%, #b8e0ea 70%, #17A2B8 100%);"></div>

                            <svg class="absolute inset-0 w-full h-full" viewBox="0 0 600 420">
                                <path d="M 50 200 Q 200 150 300 200 Q 400 250 550 180" stroke="#FFFFFF" stroke-width="4" fill="none" opacity="0.6" />
                                <path d="M 100 100 L 300 200 L 450 350" stroke="#FFFFFF" stroke-width="3" fill="none" opacity="0.5" />
                                <path d="M 300 50 L 300 200 L 200 380" stroke="#FFFFFF" stroke-width="3" fill="none" opacity="0.5" />
                                <path d="M 150 300 Q 300 280 500 320" stroke="#FFFFFF" stroke-width="2" fill="none" opacity="0.4" />
                                <ellipse cx="480" cy="350" rx="150" ry="100" fill="#17A2B8" opacity="0.3" />
                                <text x="430" y="360" fill="#0A2540" font-size="12" font-weight="600" opacity="0.7">{{ __('coverage.red_sea') }}</text>

                                <!-- Draw the Shaded Polygons on the SVG -->
                                @foreach ($destinations as $dest)
                                    @if($dest['svg_polygon'])
                                        <polygon points="{{ $dest['svg_polygon'] }}" class="{{ $dest['text_color'] }}" fill="currentColor" fill-opacity="0.35" stroke="currentColor" stroke-width="2" stroke-opacity="0.8" />
                                    @endif
                                @endforeach
                            </svg>

                            <!-- Center Map Pins -->
                            @foreach ($destinations as $dest)
                                <div class="absolute -translate-x-1/2 -translate-y-full cursor-pointer group" style="left: {{ $dest['x'] }}; top: {{ $dest['y'] }}; transition: all 0.5s ease;">
                                    <div class="w-7 h-7 {{ $dest['color'] }} rounded-full border-2 border-white shadow-lg flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1112 6.5a2.5 2.5 0 010 5z" /></svg>
                                    </div>
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 bg-navy text-white text-xs font-semibold px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-20 shadow-md">
                                        {{ $dest['name'] }}
                                    </div>
                                </div>
                            @endforeach

                            <div class="absolute bottom-3 right-3 bg-white/90 text-xs text-navy/80 font-medium shadow-sm px-2.5 py-1 rounded-md">
                                {{ __('coverage.map_caption') }}
                            </div>
                        </div>
                    </div>

                    <div class="absolute -bottom-4 -left-4 bg-white rounded-2xl shadow-card-hover px-5 py-4 border border-navy/10">
                        {{-- Made this number dynamic --}}
                        <div class="text-2xl font-bold text-primary font-tabular">{{ $hotelCount }}+</div>
                        <div class="text-xs text-navy/50 font-medium">{{ __('coverage.hotel_pickups') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif