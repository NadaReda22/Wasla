{{-- Full‑width wrapper: breaks out of the list page’s padding --}}
<div class="coverage-map-wrapper" style="margin-left: -1.5rem; margin-right: -1.5rem;">
    <div class="bg-white border border-[#DCE3EB] shadow-sm p-0 overflow-hidden" style="border-radius: 0;">
        @if($areas->isEmpty())
            <div class="flex items-center justify-center h-64 text-gray-400">
                No areas with coordinates set.
            </div>
        @else
            <!-- Native Alpine.js Map Component -->
            <div
                x-data="{
                    map: null,
                    areas: {{ json_encode($areas->values()->toArray()) }},
                    
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

                        // 4. Add Markers
                        const bounds = [];
                        this.areas.forEach((area) => {
                            const lat = parseFloat(area.latitude);
                            const lng = parseFloat(area.longitude);
                            
                            if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
                                L.marker([lat, lng])
                                 .addTo(this.map)
                                 .bindPopup(`<b>${area.name_en}</b><br>${area.name_ar ?? ''}`);
                                bounds.push([lat, lng]);
                            }
                        });

                        if (bounds.length) {
                            this.map.fitBounds(bounds, { padding: [50, 50], maxZoom: 14 });
                        }

                        // 5. FIX THE WHITE BOARD BUG
                        // Wait a fraction of a second for Livewire to finish rendering, then force the map to recalculate its size
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