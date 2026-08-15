<div x-data="hotelPickerComponent()" x-init="initComponent()" class="space-y-3 relative z-10">
    <label class="block text-sm font-semibold text-navy">
        Hotel / Pickup Location
    </label>
    
    <div class="relative">
        <!-- Search Icon -->
        <div class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-turquoise">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        
        <!-- Search Input using your custom wasla-input class -->
        <input
            x-ref="searchInput"
            type="text"
            x-model="query"
            @input.debounce.250ms="fetchSuggestions"
            @focus="showList = true"
            @click.away="showList = false"
            placeholder="Start typing your hotel name…"
            class="wasla-input pl-12"
            autocomplete="off"
        />
        
        <!-- Loading Spinner -->
        <div x-show="isLoading" style="display: none;" class="absolute right-4 top-1/2 -translate-y-1/2 text-turquoise">
            <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </div>

        <!-- Autocomplete Suggestions Dropdown using your custom wasla-shadow class -->
        <ul x-show="showList && suggestions.length > 0" 
            x-transition.opacity
            style="display: none;"
            class="absolute z-50 mt-2 max-h-72 w-full overflow-auto rounded-2xl border border-gray-200 bg-white py-2 wasla-shadow">
            <template x-for="s in suggestions" :key="s.placeId">
                <li>
                    <button type="button" @click.prevent="selectSuggestion(s)" class="flex w-full items-start gap-3 px-4 py-2.5 text-left text-sm hover:bg-sand-light transition-colors">
                        <div class="mt-0.5 shrink-0 text-coral">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <span class="min-w-0">
                            <span class="block truncate font-bold text-navy" x-text="s.primary"></span>
                            <span class="block truncate text-xs text-gray-500" x-text="s.secondary"></span>
                        </span>
                    </button>
                </li>
            </template>
        </ul>
    </div>

    <!-- Error Message -->
    <p x-show="loadError" style="display: none;" class="text-xs text-coral font-medium" x-text="'Map error: ' + loadError"></p>

    <!-- Google Map Container -->
    <div x-ref="mapContainer" class="h-64 w-full overflow-hidden rounded-2xl border border-gray-200 bg-sand-light"></div>

    <!-- Selected Hotel Confirmation -->
    <div x-show="selectedHotel.name" style="display: none;" class="flex items-start gap-2 rounded-xl bg-turquoise/10 px-4 py-3 text-sm text-navy border border-turquoise/20">
        <span>📍</span>
        <span class="min-w-0">
            <span class="font-bold" x-text="selectedHotel.name"></span>
            <span class="text-gray-500 text-xs block" x-text="selectedHotel.address"></span>
        </span>
    </div>
    
    <p class="text-xs text-gray-500 font-medium">
        Tip: Drag the pin on the map to fine-tune your exact pickup point.
    </p>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('hotelPickerComponent', () => ({
            query: '',
            suggestions: [],
            showList: false,
            isLoading: false,
            isReady: false,
            loadError: null,
            
            selectedHotel: {
                name: '',
                address: '',
                lat: null,
                lng: null
            },

            map: null,
            marker: null,
            sessionToken: null,
            sharmCenter: { lat: 27.9158, lng: 34.3299 }, // Sharm Center coordinates from Lovable

            initComponent() {
                this.loadGoogleMaps().then(async () => {
                    const g = window.google;
                    await g.maps.importLibrary("places");
                    await g.maps.importLibrary("maps");
                    await g.maps.importLibrary("marker");

                    this.map = new g.maps.Map(this.$refs.mapContainer, {
                        center: this.sharmCenter,
                        zoom: 12,
                        disableDefaultUI: true,
                        zoomControl: true,
                        gestureHandling: "greedy",
                        clickableIcons: false
                    });

                    this.sessionToken = new g.maps.places.AutocompleteSessionToken();
                    this.isReady = true;
                }).catch(err => {
                    this.loadError = err.message;
                });
            },

            loadGoogleMaps() {
                if (window.google?.maps?.places) return Promise.resolve();
                if (window.__waslaMapsPromise) return window.__waslaMapsPromise;

                // Safely pulling key from your Laravel .env file
                const key = '{{ env("GOOGLE_MAPS_API_KEY") }}'; 
                
                window.__waslaMapsPromise = new Promise((resolve, reject) => {
                    window.__waslaInitMap = () => resolve();
                    const script = document.createElement("script");
                    script.src = `https://maps.googleapis.com/maps/api/js?key=${key}&libraries=places&loading=async&callback=__waslaInitMap`;
                    script.async = true;
                    script.defer = true;
                    script.onerror = () => reject(new Error("Failed to load Google Maps"));
                    document.head.appendChild(script);
                });

                return window.__waslaMapsPromise;
            },

            async fetchSuggestions() {
                if (!this.isReady || this.query.trim().length < 2) {
                    this.suggestions = [];
                    return;
                }

                this.isLoading = true;
                try {
                    const g = window.google;
                    const { AutocompleteSuggestion } = await g.maps.importLibrary("places");
                    
                    const response = await AutocompleteSuggestion.fetchAutocompleteSuggestions({
                        input: this.query,
                        sessionToken: this.sessionToken,
                        includedPrimaryTypes: ["lodging"], // Limits lookups specifically to hotels
                        locationBias: { center: this.sharmCenter, radius: 25000 }, 
                        language: "en",
                    });

                    this.suggestions = (response.suggestions || []).map(s => {
                        const p = s.placePrediction;
                        return p ? {
                            placeId: p.placeId,
                            primary: p.mainText?.text || p.text?.text || "",
                            secondary: p.secondaryText?.text || "",
                        } : null;
                    }).filter(Boolean);

                } catch (e) {
                    console.warn("Autocomplete Error:", e);
                } finally {
                    this.isLoading = false;
                }
            },

            async selectSuggestion(suggestion) {
                this.isLoading = true;
                const g = window.google;
                const { Place } = await g.maps.importLibrary("places");
                
                const place = new Place({ id: suggestion.placeId, requestedLanguage: "en" });
                await place.fetchFields({ fields: ["displayName", "formattedAddress", "location"] });

                const lat = typeof place.location.lat === "function" ? place.location.lat() : place.location.lat;
                const lng = typeof place.location.lng === "function" ? place.location.lng() : place.location.lng;

                this.selectedHotel = {
                    name: place.displayName || suggestion.primary,
                    address: place.formattedAddress || "",
                    lat: lat,
                    lng: lng
                };

                this.query = this.selectedHotel.name;
                this.suggestions = [];
                this.showList = false;
                this.isLoading = false;

                this.placeMarker(lat, lng);
                
                this.sessionToken = new g.maps.places.AutocompleteSessionToken();

                // Send the choice upward to your primary Alpine form scope
                this.$dispatch('hotel-selected', this.selectedHotel);
            },

            placeMarker(lat, lng) {
                const g = window.google;
                if (!this.map || !g) return;

                if (this.marker) this.marker.setMap(null);

                this.marker = new g.maps.Marker({
                    position: { lat, lng },
                    map: this.map,
                    draggable: true,
                    animation: g.maps.Animation.DROP,
                });

                this.marker.addListener("dragend", () => {
                    const pos = this.marker.getPosition();
                    this.selectedHotel.lat = pos.lat();
                    this.selectedHotel.lng = pos.lng();
                    this.selectedHotel.address = "Custom adjusted pin location";
                    this.$dispatch('hotel-selected', this.selectedHotel);
                });

                this.map.panTo({ lat, lng });
                this.map.setZoom(16);
            }
        }));
    });
</script>