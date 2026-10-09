{{-- Full‑width wrapper: breaks out of the list page’s padding --}}
<div class="coverage-map-wrapper" style="margin-inline: -1.5rem;">
    <div class="bg-white border border-[#DCE3EB] shadow-sm p-0 overflow-hidden" style="border-radius: 0;">
        @if($areas->isEmpty())
            <div class="flex items-center justify-center h-64 text-gray-400">
                {{ __('admin.list_tours.no_areas') }}
            </div>
        @else
            <!-- Native Alpine.js Map Component -->
            <div
                x-data="{
                    map: null,
                    areas: {{ json_encode($areas->values()->toArray()) }},
                    zonePopup: {{ json_encode(__('admin.areas_map.zone_popup')) }},

                    initMap() {
                        // 1. Wait until Leaflet is fully downloaded
                        if (typeof L === 'undefined') {
                            setTimeout(() => this.initMap(), 150);
                            return;
                        }

                        // 2. Prevent initializing twice
                        if (this.map) {
                            this.map.remove();
                        }

                        // 3. Create Map
                        this.map = L.map($refs.mapContainer, {
                            center: [27.9158, 34.3298],
                            zoom: 12,
                            scrollWheelZoom: false
                        });

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap'
                        }).addTo(this.map);

                        const bounds = [];

                        // Tailwind to Hex Map for Polygon shading
                        const hexColors = {
                            'bg-primary': '#1877c9',
                            'bg-turquoise': '#48c4d8',
                            'bg-navy': '#1b3a5a',
                            'bg-coral': '#ff6b6b',
                            'bg-gold': '#d4af37',
                            'bg-primary-400': '#60a5fa'
                        };

                        // 4. Add Markers & Polygons
                        this.areas.forEach((area) => {
                            const lat = parseFloat(area.latitude);
                            const lng = parseFloat(area.longitude);
                            const areaColor = hexColors[area.color] || '#1877c9';

                            // A) Draw the Center Pin
                            if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
                                const coloredPin = L.divIcon({
                                    className: 'custom-colored-pin',
                                    html: `<div style='background-color: ${areaColor}; width: 16px; height: 16px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.5);'></div>`,
                                    iconSize: [16, 16],
                                    iconAnchor: [8, 8]
                                });

                                L.marker([lat, lng], { icon: coloredPin })
                                 .addTo(this.map)
                                 .bindPopup(`<b>${area.name_en}</b><br>${area.name_ar ?? ''}`);
                                bounds.push([lat, lng]);
                            }

                            // B) Draw the Polygon Boundaries
                            if (area.polygon_coordinates) {
                                try {
                                    // Parse JSON if it's a string
                                    let coords = typeof area.polygon_coordinates === 'string'
                                        ? JSON.parse(area.polygon_coordinates)
                                        : area.polygon_coordinates;

                                    if (Array.isArray(coords) && coords.length > 2) {
                                        // Leaflet expects [lat, lng] format
                                        const mappedCoords = coords.map(c => [parseFloat(c.lat), parseFloat(c.lng)]);

                                        L.polygon(mappedCoords, {
                                            color: areaColor,
                                            fillColor: areaColor,
                                            fillOpacity: 0.3,
                                            weight: 2
                                        }).addTo(this.map).bindPopup(`<b>${this.zonePopup.replace(':name', area.name_en)}</b>`);

                                        // Expand map bounds to fit the whole polygon
                                        mappedCoords.forEach(c => bounds.push(c));
                                    }
                                } catch (e) {
                                    console.error('Invalid polygon JSON for area: ' + area.name_en, e);
                                }
                            }
                        });

                        if (bounds.length) {
                            this.map.fitBounds(bounds, { padding: [50, 50], maxZoom: 14 });
                        }

                        // 5. FIX THE WHITE BOARD BUG
                        setTimeout(() => {
                            this.map.invalidateSize();
                        }, 300);
                    }
                }"
                x-init="initMap()"
            >
                <!-- Map Target Element -->
                <div x-ref="mapContainer" style="height: 420px; width: 100%; z-index: 10;"></div>
            </div>
        @endif
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if(typeof L !== 'undefined') {
            L.Icon.Default.prototype.options.imagePath = "https://unpkg.com/leaflet@1.9.4/dist/images/";
        }
    });
</script>