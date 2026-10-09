<script src="https://cdn.jsdelivr.net/npm/fuse.js@6.6.2"></script>

<div x-data="hotelPickerComponent(window.bookingAreas)" x-init="initComponent()" class="space-y-3 relative z-10">
    
    <div class="flex gap-2 items-start">
        <div class="relative flex-1">
            <!-- Search Icon -->
            <div class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#1877c9]">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            
            <!-- Search Input -->
            <input
                x-ref="searchInput"
                type="text"
                x-model="query"
                @input="filterSuggestions"
                @focus="showList = true"
                @click.away="showList = false"
                :placeholder="t.map.search_placeholder"
                class="w-full pl-12 pr-10 py-3 rounded-xl border border-gray-200 outline-none transition-all duration-200 bg-white text-navy focus:border-[#1877c9] focus:ring-4 focus:ring-[#1877c9]/10"
                autocomplete="off"
            />
            
            <!-- Loading Spinner (Kept for UX consistency during rapid typing) -->
            <div x-show="isLoading" style="display: none;" class="absolute right-4 top-1/2 -translate-y-1/2 text-[#1877c9]">
                <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>

            <!-- Autocomplete Suggestions Dropdown -->
            <ul x-show="showList && suggestions.length > 0" 
                x-transition.opacity
                style="display: none;"
                class="absolute z-50 mt-2 max-h-72 w-full overflow-auto rounded-xl border border-gray-200 bg-white py-2 shadow-xl">
                <template x-for="s in suggestions" :key="s.id">
                    <li>
                        <button type="button" @click.prevent="selectHotel(s)" class="flex w-full items-start gap-3 px-4 py-2.5 text-left text-sm hover:bg-[#f0f7ff] transition-colors">
                            <div class="mt-0.5 shrink-0 text-[#1877c9]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <span class="min-w-0">
                                <!-- Dynamic Locale Hotel Name -->
                                <span class="block truncate font-bold text-navy" x-text="document.documentElement.lang === 'ru' && s.name_ru ? s.name_ru : s.name_en"></span>
                                <!-- Dynamic Locale Area Name -->
                                <span class="block truncate text-xs text-gray-500" x-text="s.area ? (document.documentElement.lang === 'ru' && s.area.name_ru ? s.area.name_ru : s.area.name_en) : t.map.assigned_area"></span>
                            </span>
                        </button>
                    </li>
                </template>
            </ul>
        </div>

        <!-- Locate Me Button -->
        <button type="button" @click.prevent="getCurrentLocation()"
            class="flex items-center justify-center shrink-0 w-[50px] h-[50px] rounded-xl border border-gray-200 bg-[#f0f7ff] text-[#1877c9] hover:bg-[#e1effe] hover:border-[#1877c9]/30 transition-all shadow-sm"
            title="Locate Me">
            
            <svg x-show="!isLocating" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22s-8-4.5-8-11.8A8 8 0 0112 2a8 8 0 018 8.2c0 7.3-8 11.8-8 11.8z" />
                <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2" />
            </svg>
            
            <svg x-show="isLocating" style="display:none;" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </button>
    </div>

    <!-- Error Message -->
    <p x-show="loadError" style="display: none;" class="text-xs text-red-500 font-medium" x-text="loadError"></p>

    <!-- Zone Indicator -->
    <div class="flex flex-wrap gap-2 items-center text-[10px] mt-1 mb-2">
        <span class="font-bold text-navy whitespace-nowrap" x-text="t.map.service_areas"></span>
        <template x-for="area in areas" :key="area.id">
            <button type="button" 
                    @click.prevent="zoomToArea(area)"
                    class="bg-gray-100 text-gray-600 hover:bg-[#1877c9] hover:text-white px-2 py-0.5 rounded-full transition-colors cursor-pointer" 
                    x-text="document.documentElement.lang === 'ru' && area.name_ru ? area.name_ru : area.name_en">
            </button>
        </template>
        <span x-show="areas.length === 0" class="text-gray-400 italic" x-text="t.map.no_zones"></span>
    </div>

    <!-- Google Map Container -->
    <div x-ref="mapContainer" class="h-64 w-full overflow-hidden rounded-xl border border-gray-200 bg-gray-50"></div>

    <!-- Selected Hotel Confirmation -->
    <div x-show="selectedHotel.id && !isLocating && !loadError" style="display: none;" class="flex items-start gap-2 rounded-xl bg-[#f0f7ff] px-4 py-3 text-sm text-navy border border-[#1877c9]/20">
        <span>📍</span>
        <span class="min-w-0">
            <span class="font-bold" x-text="document.documentElement.lang === 'ru' && selectedHotel.name_ru ? selectedHotel.name_ru : selectedHotel.name_en"></span>
            <span class="text-gray-500 text-xs block" x-text="'Zone: ' + (selectedHotel.area ? (document.documentElement.lang === 'ru' && selectedHotel.area.name_ru ? selectedHotel.area.name_ru : selectedHotel.area.name_en) : 'Standard')"></span>
        </span>
    </div>
    
    <p class="text-[10px] text-gray-400 font-medium mt-1" x-text="t.map.tip"></p>

    <input type="hidden" name="hotel" :value="selectedHotel.name_en">
    <input type="hidden" name="area_id" :value="selectedHotel.area_id">
    <input type="hidden" name="pickupLat" :value="selectedHotel.latitude">
    <input type="hidden" name="pickupLng" :value="selectedHotel.longitude">

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('hotelPickerComponent', (dbAreas = []) => ({
            query: '',
            suggestions: [],
            showList: false,
            isLoading: false,
            isLocating: false, 
            isReady: false,
            loadError: null,
            
            areas: dbAreas,
            hotels: [], 
            fuse: null, 
            
            selectedHotel: {
                id: null,
                name_en: '',
                name_ru: '',
                latitude: null,
                longitude: null,
                area_id: null,
                area: null
            },

            map: null,
            markers: [], 
            infoWindow: null, 
            sharmCenter: { lat: 27.9158, lng: 34.3299 }, 

            async initComponent() {
                await this.fetchHotelsData();

                this.loadGoogleMaps().then(async () => {
                    const g = window.google;
                    await g.maps.importLibrary("maps");
                    await g.maps.importLibrary("marker");

                    this.map = new g.maps.Map(this.$refs.mapContainer, {
                        center: this.sharmCenter,
                        zoom: 11,
                        disableDefaultUI: true,
                        zoomControl: true,
                        gestureHandling: "greedy",
                        clickableIcons: false
                    });

                    this.infoWindow = new g.maps.InfoWindow();

                    this.plotAllHotels();

                    this.isReady = true;
                }).catch(err => {
                    this.loadError = err.message;
                });
            },

            async fetchHotelsData() {
                try {
                    const response = await fetch('/book/active-hotels');
                    this.hotels = await response.json();

                    this.fuse = new Fuse(this.hotels, {
                        keys: ['name_en', 'name_ru', 'name_ar', 'keywords'],
                        threshold: 0.35, 
                        ignoreLocation: true,
                        minMatchCharLength: 2
                    });
                } catch (error) {
                    console.error("Failed to load hotels data", error);
                }
            },

            loadGoogleMaps() {
                if (window.google?.maps) return Promise.resolve();
                if (window.__waslaMapsPromise) return window.__waslaMapsPromise;

                const key = '{{ config("services.google_maps.key", env("GOOGLE_MAPS_API_KEY")) }}'; 
                
                window.__waslaMapsPromise = new Promise((resolve, reject) => {
                    window.__waslaInitMap = () => resolve();
                    const script = document.createElement("script");
                    script.src = `https://maps.googleapis.com/maps/api/js?key=${key}&loading=async&callback=__waslaInitMap`;
                    script.async = true;
                    script.defer = true;
                    script.onerror = () => reject(new Error("Failed to load Google Maps"));
                    document.head.appendChild(script);
                });

                return window.__waslaMapsPromise;
            },

            plotAllHotels() {
                const g = window.google;
                this.hotels.forEach(hotel => {
                    const lat = parseFloat(hotel.latitude);
                    const lng = parseFloat(hotel.longitude);

                    if (isNaN(lat) || isNaN(lng)) return;

                    const markerTitle = document.documentElement.lang === 'ru' && hotel.name_ru ? hotel.name_ru : hotel.name_en;

                    const marker = new g.maps.Marker({
                        position: { lat, lng },
                        map: this.map,
                        title: markerTitle,
                        draggable: false 
                    });

                    marker.addListener("click", () => {
                        this.selectHotel(hotel);
                    });

                    this.markers.push(marker);
                });
            },

            filterSuggestions() {
                if (!this.isReady || !this.fuse || this.query.trim().length < 2) {
                    this.suggestions = [];
                    return;
                }

                this.isLoading = true;
                const results = this.fuse.search(this.query);
                this.suggestions = results.slice(0, 8).map(result => result.item);
                this.isLoading = false;
            },

            selectHotel(hotel) {
                this.selectedHotel = hotel;
                this.query = document.documentElement.lang === 'ru' && hotel.name_ru ? hotel.name_ru : hotel.name_en; 
                this.suggestions = [];
                this.showList = false;
                this.loadError = null;

                const lat = parseFloat(hotel.latitude);
                const lng = parseFloat(hotel.longitude);

                this.map.panTo({ lat, lng });
                this.map.setZoom(16);

                this.infoWindow.setContent(`
                    <div style="padding:4px; text-align:center;">
                        <b>${this.query}</b><br>
                        <span style="font-size:11px; color:#1877c9;">${this.t.map.selected_location}</span>
                    </div>
                `);
                this.infoWindow.setPosition({ lat, lng });
                this.infoWindow.open(this.map);

                this.$dispatch('hotel-selected', this.selectedHotel);
            },
            
            zoomToArea(area) {
                if (!this.map) return;
                
                const lat = parseFloat(area.latitude);
                const lng = parseFloat(area.longitude);

                if (isNaN(lat) || isNaN(lng)) {
                    return;
                }

                this.map.panTo({ lat, lng });
                this.map.setZoom(13); 
            },

            getCurrentLocation() {
                if (!navigator.geolocation) {
                    alert("Your browser does not support geolocation.");
                    return;
                }

                this.isLocating = true;
                this.loadError = null;
                this.query = this.t.map.locating; 

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        this.map.panTo({ lat, lng });
                        this.map.setZoom(15); 

                        this.infoWindow.setContent(`
                            <div style="padding:5px; text-align:center;">
                                <b>${this.t.map.you_are_here}</b><br>
                                <span style="font-size:12px; color:gray;">${this.t.map.click_pin}</span>
                            </div>
                        `);
                        this.infoWindow.setPosition({ lat, lng });
                        this.infoWindow.open(this.map);

                        this.query = ""; 
                        this.isLocating = false;
                    },
                    (error) => {
                        this.isLocating = false;
                        this.query = ""; 
                        if (error.code === 1) {
                            alert(this.t.map.allow_location);
                        } else {
                            alert(this.t.map.could_not_locate);
                        }
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            }
        }));
    });
</script>