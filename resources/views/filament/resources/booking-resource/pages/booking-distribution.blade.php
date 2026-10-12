<div>
    <x-filament-panels::page>
        
        @php
            $palette = [
                ['hex' => '#10B981', 'bg' => 'bg-emerald-50'],
                ['hex' => '#3B82F6', 'bg' => 'bg-blue-50'],
                ['hex' => '#F59E0B', 'bg' => 'bg-amber-50'],
                ['hex' => '#8B5CF6', 'bg' => 'bg-violet-50'],
                ['hex' => '#EC4899', 'bg' => 'bg-pink-50'],
                ['hex' => '#14B8A6', 'bg' => 'bg-teal-50'],
                ['hex' => '#EF4444', 'bg' => 'bg-red-50'],
                ['hex' => '#0EA5E9', 'bg' => 'bg-sky-50'],
            ];

            $mapPaletteAssignments = [];
            foreach ($selectedBookings as $index => $bookingId) {
                $mapPaletteAssignments[$bookingId] = $palette[$index % count($palette)]['hex'];
            }
        @endphp

        <div x-data="distributionEngine()" @refresh-map.window="setTimeout(() => plotBookings(), 50)">
            
            <div id="map-data" class="hidden" 
                 data-bookings="{{ json_encode($this->unassignedBookings) }}"
                 data-selected="{{ json_encode($selectedBookings) }}"
                 data-palette-assignments="{{ json_encode($mapPaletteAssignments) }}">
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <div class="lg:col-span-7 space-y-6">
                    
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
                        <div class="flex flex-wrap items-center justify-between mb-4 gap-3">
                            <h3 class="font-bold text-gray-800 flex items-center gap-2 text-lg">
                                <x-heroicon-s-funnel class="w-6 h-6 text-primary-600"/> 
                                {{ __('admin.distribution.filter_departures') ?? 'Filter Departures' }}
                            </h3>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">{{ __('admin.columns.date') ?? 'Date' }}</label>
                                <select wire:model.live="selectedDate" class="w-full rounded-lg border-gray-300 text-xs focus:ring-primary-500 focus:border-primary-500">
                                    @foreach ($this->availableDates as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">{{ __('admin.models.tour') ?? 'Tour' }}</label>
                                <select wire:model.live="selectedTour" class="w-full rounded-lg border-gray-300 text-xs focus:ring-primary-500 focus:border-primary-500">
                                    <option value="">{{ __('admin.options.all_tours') ?? 'All Tours' }}</option>
                                    @foreach ($this->availableTours as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">{{ __('admin.distribution.dep_time') ?? 'Dep. Time' }}</label>
                                <select wire:model.live="selectedDepartureTime" class="w-full rounded-lg border-gray-300 text-xs focus:ring-primary-500 focus:border-primary-500">
                                    <option value="">{{ __('admin.options.all_times') ?? 'All Times' }}</option>
                                    @foreach ($this->availableDepartureTimes as $timeVal => $timeLabel)
                                        <option value="{{ $timeLabel }}">{{ $timeLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">{{ __('admin.distribution.ret_time') ?? 'Ret. Time' }}</label>
                                <select wire:model.live="selectedReturnTime" class="w-full rounded-lg border-gray-300 text-xs focus:ring-primary-500 focus:border-primary-500">
                                    <option value="">{{ __('admin.options.all_times') ?? 'All Times' }}</option>
                                    @foreach ($this->availableReturnTimes as $timeVal => $timeLabel)
                                        <option value="{{ $timeLabel }}">{{ $timeLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">{{ __('admin.distribution.area_zone') ?? 'Area / Zone' }}</label>
                                <select wire:model.live="selectedAreaId" class="w-full rounded-lg border-gray-300 text-xs focus:ring-primary-500 focus:border-primary-500">
                                    <option value="">{{ __('admin.options.all_zones') ?? 'All Zones' }}</option>
                                    @foreach ($this->availableAreas as $aId => $aName)
                                        <option value="{{ $aId }}">{{ $aName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2 p-1 bg-gray-100 rounded-lg w-max max-w-full overflow-x-auto">
                        <button wire:click="$set('direction', 'outbound')" 
                                class="px-4 py-2.5 text-sm font-bold rounded-md transition-colors flex items-center gap-2 whitespace-nowrap {{ $direction === 'outbound' ? 'bg-white text-primary-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                            <x-heroicon-s-paper-airplane class="w-5 h-5 transform rtl:rotate-135 -rotate-45" /> {{ __('admin.distribution.outbound_btn') ?? 'Outbound (To Tour)' }}
                        </button>
                        <button wire:click="$set('direction', 'return')" 
                                class="px-4 py-2.5 text-sm font-bold rounded-md transition-colors flex items-center gap-2 whitespace-nowrap {{ $direction === 'return' ? 'bg-white text-primary-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                            <x-heroicon-s-arrow-uturn-left class="w-5 h-5 rtl:-scale-x-100" /> {{ __('admin.distribution.return_btn') ?? 'Return (To Hotel)' }}
                        </button>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-4 bg-gray-50 border-b border-gray-200 flex flex-wrap justify-between items-center gap-3">
                            <h3 class="font-bold text-gray-800 text-base">
                                {{ __('admin.distribution.unassigned_bookings') ?? 'Unassigned Bookings' }} ({{ $direction === 'outbound' ? (__('admin.distribution.outbound_short') ?? 'Outbound') : (__('admin.distribution.return_short') ?? 'Return') }})
                                <span class="bg-primary-100 text-primary-700 text-xs px-2.5 py-0.5 rounded-full ms-2">
                                    {{ count($this->unassignedBookings) }}
                                </span>
                            </h3>
                            
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">{{ __('admin.distribution.booked') ?? 'Booked:' }}</span>
                                <select wire:model.live="selectedBookingVehicleType" class="rounded-lg border-gray-300 text-xs py-1.5 ps-3 pe-8 focus:ring-primary-500 focus:border-primary-500">
                                    <option value="">{{ __('admin.options.all_vehicles') ?? 'All Vehicles' }}</option>
                                    <option value="microbus">{{ __('admin.options.shared_microbus') ?? 'Shared Microbus' }}</option>
                                    <option value="private">{{ __('admin.options.private_car') ?? 'Private Car' }}</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-start">
                                <thead class="bg-gray-50 text-gray-500 text-xs uppercase text-start">
                                    <tr>
                                        <th class="px-4 py-3 w-10"></th>
                                        <th class="px-4 py-3 text-start">{{ __('admin.columns.ref') ?? 'Ref' }}</th>
                                        <th class="px-4 py-3 text-start">{{ __('admin.columns.client') ?? 'Client' }}</th>
                                        <th class="px-4 py-3 text-start">{{ __('admin.distribution.location_zone') ?? 'Location & Zone' }}</th>
                                        <th class="px-4 py-3 text-start">{{ __('admin.distribution.tour_schedule') ?? 'Tour & Schedule' }}</th>
                                        <th class="px-4 py-3 text-center">{{ __('admin.distribution.seats') ?? 'Seats' }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($this->unassignedBookings as $booking)
                                        @php
                                            $selectedIndex = array_search($booking->id, $selectedBookings);
                                            $isSelected = $selectedIndex !== false;
                                            $theme = $isSelected ? $palette[$selectedIndex % count($palette)] : null;
                                            
                                            $depTimeRaw = $booking->departure?->departure_time;
                                            $retTimeRaw = $booking->departure?->return_time;
                                            
                                            $outTime = $depTimeRaw ? \Carbon\Carbon::parse($depTimeRaw)->format('g:i A') : '09:00 AM';
                                            $retTime = $retTimeRaw ? \Carbon\Carbon::parse($retTimeRaw)->format('g:i A') : __('admin.fleet.tbd');
                                        @endphp
                                        
                                        <tr class="hover:bg-gray-50 transition-colors {{ $isSelected ? $theme['bg'] : '' }}">
                                            <td class="px-4 py-3">
                                                <input type="checkbox" wire:model.live="selectedBookings" value="{{ $booking->id }}" 
                                                       class="rounded border-gray-300 w-5 h-5 cursor-pointer shadow-sm"
                                                       style="{{ $isSelected ? 'accent-color: ' . $theme['hex'] . ';' : '' }}">
                                            </td>
                                            <td class="px-4 py-3 font-mono text-xs font-bold text-gray-600">{{ $booking->booking_reference }}</td>
                                            <td class="px-4 py-3">
                                                <div class="font-black text-gray-900">{{ $booking->client->full_name ?? __('admin.options.na') ?? 'N/A' }}</div>
                                                <div class="text-xs font-bold text-green-600 mt-0.5 flex items-center gap-1">
                                                    <x-heroicon-s-phone class="w-3 h-3" /> {{ $booking->client->whatsapp_number ?? '' }}
                                                </div>
                                                
                                                @if($booking->pricingTier)
                                                    <div class="mt-1.5">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-bold border {{ $booking->pricingTier->vehicle_type === 'private' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-blue-50 text-blue-700 border-blue-200' }}">
                                                            @if($booking->pricingTier->vehicle_type === 'private')
                                                                <x-heroicon-s-user class="w-3 h-3" /> {{ __('admin.options.private_car') ?? 'Private Car' }}
                                                            @else
                                                                <x-heroicon-s-users class="w-3 h-3" /> {{ __('admin.options.shared_microbus') ?? 'Shared' }}
                                                            @endif
                                                        </span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="cursor-pointer group block" 
                                                     @click="focusMapPin({{ $booking->pickup_lat ?? 'null' }}, {{ $booking->pickup_lng ?? 'null' }})"
                                                     title="{{ __('admin.distribution.click_to_zoom') ?? 'Click to zoom on map' }}">
                                                    
                                                    <div class="font-bold flex items-start gap-1.5 transition-colors"
                                                         style="{{ $isSelected ? 'color: ' . $theme['hex'] . ';' : 'color: #374151;' }}">
                                                        <x-heroicon-s-map-pin class="w-5 h-5 flex-shrink-0" style="color: {{ $isSelected ? $theme['hex'] : '#9ca3af' }};" />
                                                        <span class="truncate max-w-[200px] mt-0.5">{{ $booking->pickup_name ?? __('admin.options.unknown') ?? 'Unknown' }}</span>
                                                    </div>
                                                    
                                                    @if($booking->area)
                                                        <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 shadow-sm ms-6">
                                                            <x-heroicon-s-map class="w-3 h-3" /> {{ app()->getLocale() === 'ar' ? ($booking->area->name_ar ?? $booking->area->name_en) :$booking->area->name_en }}
                                                        </span>
                                                    @endif

                                                    @if($booking->pickup_address)
                                                        <div class="text-[10px] font-semibold truncate max-w-[200px] ps-6 mt-1"
                                                             style="color: {{ $isSelected ? $theme['hex'] : '#9ca3af' }}; opacity: 0.9;">
                                                            {{ $booking->pickup_address }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="font-black text-gray-800 text-[11px] mb-1.5 leading-tight" style="{{ $isSelected ? 'color: ' . $theme['hex'] . ';' : '' }}">
                                                    {{ app()->getLocale() === 'ar' ? ($booking->tour->name_ar ?? $booking->tour->name_en ?? __('admin.options.custom_tour') ?? 'Custom Tour') : ($booking->tour->name_en ?? __('admin.options.custom_tour') ?? 'Custom Tour') }}
                                                </div>
                                                <div class="flex flex-col gap-1 text-[10px] font-bold text-gray-700">
                                                    <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 px-2 py-0.5 rounded border border-blue-100 w-max shadow-sm">
                                                        <x-heroicon-s-paper-airplane class="w-3 h-3 transform rtl:rotate-135 -rotate-45" /> {{ __('admin.list_tours.out') ?? 'Out:' }} {{ $outTime }}
                                                    </span>
                                                    <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded border border-emerald-100 w-max shadow-sm">
                                                        <x-heroicon-s-arrow-uturn-left class="w-3 h-3 rtl:-scale-x-100" /> {{ __('admin.list_tours.ret') ?? 'Ret:' }} {{ $retTime }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-center font-black text-lg" style="{{ $isSelected ? 'color: ' . $theme['hex'] . ';' : 'color: #374151;' }}">
                                                <span class="flex items-center justify-center gap-1.5">
                                                    {{ $booking->seats_count }} <x-heroicon-s-user class="w-5 h-5 opacity-75" />
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                                                <x-heroicon-o-document-magnifying-glass class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                                                <span class="text-sm font-semibold">{{ __('admin.distribution.no_bookings') ?? 'No unassigned bookings found for this filter.' }}</span>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="lg:col-span-5 space-y-6">
                    
                   <div wire:ignore class="bg-white p-2 rounded-xl shadow-sm border border-gray-200 isolate">
                        <div id="distribution-map" class="w-full rounded-lg bg-gray-50 relative overflow-hidden shadow-inner" style="height: 420px; border: 1px solid #cbd5e1;">
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 z-0">
                                <x-heroicon-s-map class="w-12 h-12 mb-2 opacity-30"/>
                                <span class="text-sm font-bold opacity-75">{{ __('admin.distribution.map_select_prompt') ?? 'Select bookings to view them on the map' }}</span>
                            </div>
                        </div>
                        <div class="mt-3 px-2 text-center text-[11px] text-gray-500 font-bold uppercase tracking-wider">
                            {{ __('admin.distribution.map_colors_info') ?? 'Colors on the map perfectly match your selections in the table' }}
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-4 bg-gray-50 border-b border-gray-200 flex flex-wrap justify-between items-center gap-3">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2 text-base">
                            <x-heroicon-s-truck class="w-6 h-6 text-primary-600"/>
                            {{ __('admin.distribution.available_fleet') ?? 'Available Fleet' }} ({{ count($this->activeVehicles) }})
                        </h3>

                        <div class="flex items-center gap-2">
                            <select wire:model.live="selectedVehicleType" class="rounded-lg border-gray-300 text-xs py-1.5 focus:ring-primary-500 focus:border-primary-500 font-bold">
                                <option value="">{{ __('admin.options.all_types') ?? 'All Types' }}</option>
                                <option value="microbus">{{ __('admin.options.microbus') ?? 'Microbus' }}</option>
                                <option value="private_car">{{ __('admin.options.private_car') ?? 'Private Car' }}</option>
                            </select>

                            {{ $this->checkGreenApiStatusAction }}
                            {{ $this->ensureAndNotifyDailyAction }}
                        </div>
                    </div>
                        
                        <div class="p-4 space-y-4 max-h-[550px] overflow-y-auto">
                            @forelse ($this->activeVehicles as $vehicle)
                                @php
                                    $percentage =$vehicle->capacity > 0 ? ($vehicle->seats_taken / $vehicle->capacity) * 100 : 0;
                                    $barColor = $percentage >= 100 ? 'bg-red-500' : ($percentage >= 80 ? 'bg-amber-400' : 'bg-emerald-500');
                                @endphp
                                
                                <div class="border border-gray-200 rounded-xl p-4 hover:border-primary-400 transition-colors bg-white shadow-sm">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h4 class="font-black text-gray-900 text-lg flex items-center gap-2">
                                                    {{ $vehicle->plate_number }}
                                                </h4>
                                                <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 shadow-sm {{ $vehicle->type === 'private_car' ? 'bg-rose-100 text-rose-700 border border-rose-200' : 'bg-blue-100 text-blue-700 border border-blue-200' }}">
                                                    @if($vehicle->type === 'private_car')
                                                        <x-heroicon-s-user class="w-3.5 h-3.5" /> {{ __('admin.distribution.private') ?? 'Private' }}
                                                    @else
                                                        <x-heroicon-s-users class="w-3.5 h-3.5" /> {{ __('admin.distribution.shared') ?? 'Shared' }}
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="text-sm font-semibold text-gray-600 mt-2 flex flex-wrap items-center gap-4">
                                                <span class="flex items-center gap-1.5"><x-heroicon-s-user-circle class="w-5 h-5 text-gray-400"/> {{ $vehicle->driver_name ?? __('admin.options.no_driver') ?? 'No Driver' }}</span>
                                                <span class="flex items-center gap-1.5"><x-heroicon-s-phone class="w-4 h-4 text-green-500"/> {{ $vehicle->driver_phone ?? __('admin.options.no_phone') ?? 'No Phone' }}</span>
                                            </div>
                                        </div>

                                        <div class="flex flex-col items-end gap-2">
                                            <button wire:click="assignToVehicle({{ $vehicle->id }})" 
                                                    wire:loading.attr="disabled"
                                                    class="bg-primary-600 hover:bg-primary-500 text-white text-xs font-black py-2.5 px-5 rounded-lg transition-colors disabled:opacity-50 shadow-md">
                                                {{ __('admin.actions.assign') ?? 'Assign' }} ({{ $direction === 'outbound' ? (__('admin.distribution.outbound_short') ?? 'Outbound') : (__('admin.distribution.return_short') ?? 'Return') }})
                                            </button>

                                            {{-- Each direction is locked on its own:
                                                 pending_ensure_count = directions of this vehicle still waiting for "Ensure",
                                                 locked_count         = directions of this vehicle that are already locked. --}}
                                            @if($vehicle->pending_ensure_count > 0)
                                                {{ ($this->ensureVehicleAction)(['vehicleId' => $vehicle->id, 'plate' =>$vehicle->plate_number]) }}
                                            @elseif($vehicle->locked_count > 0)
                                                <span class="inline-flex items-center gap-1.5 text-[11px] font-black text-gray-400 bg-gray-100 border border-gray-200 rounded-lg py-2 px-4 cursor-not-allowed whitespace-nowrap" 
                                                      title="{{ __('admin.fleet.all_locked_notified') ?? 'All bookings for this vehicle are already locked & notified' }}">
                                                    <x-heroicon-s-lock-closed class="w-3.5 h-3.5" />
                                                    {{ __('admin.distribution.all_locked') ?? 'All Locked' }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($vehicle->is_tour_staying)
                                        <div class="mt-4 p-3 rounded-xl bg-gradient-to-r rtl:bg-gradient-to-l from-purple-100 to-fuchsia-50 border border-purple-300 shadow-sm flex items-start gap-3">
                                            <div class="p-2 bg-purple-600 rounded-full text-white shadow-md animate-pulse mt-0.5">
                                                <x-heroicon-s-lock-closed class="w-5 h-5" /> 
                                            </div>
                                            <div class="flex-1">
                                                <div class="text-xs font-black uppercase tracking-widest text-purple-900">{{ __('admin.distribution.locked_on_site') ?? 'Locked On-Site' }}</div>
                                                <div class="text-sm font-bold text-purple-700 mt-1">
                                                    <div class="flex items-center gap-1.5 mb-0.5">
                                                        <x-heroicon-s-flag class="w-4 h-4 opacity-75" /> 
                                                        <span>{{ $vehicle->staying_tour_name }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-1.5">
                                                        <x-heroicon-s-clock class="w-4 h-4 opacity-75" /> 
                                                        <span dir="ltr">{{ $vehicle->staying_time_range['out'] }} &nbsp;➔&nbsp; {{$vehicle->staying_time_range['ret'] }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mt-4 bg-gray-50 rounded-xl p-3 border border-gray-200 space-y-2">
                                        <div class="text-[10px] font-black text-gray-500 uppercase tracking-widest flex items-center justify-between pb-1 border-b border-gray-200">
                                            <span>{{ __('admin.distribution.current_assignments') ?? 'Current Assignments Today' }} ({{ count($vehicle->assignments_list) }})</span>
                                        </div>

                                        @forelse ($vehicle->assignments_list as $assignment)
                                            <div class="bg-white p-2.5 rounded-lg border border-gray-200 text-sm flex flex-col gap-1.5 shadow-sm">
                                                <div class="flex items-center justify-between">
                                                    <span class="font-black text-gray-800 flex items-center gap-2">
                                                        <span class="text-[10px] font-black px-2 py-1 rounded-md flex items-center gap-1 shadow-sm border {{ $assignment['direction'] === 'outbound' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' }}">
                                                            @if($assignment['direction'] === 'outbound')
                                                                <x-heroicon-s-paper-airplane class="w-3.5 h-3.5 transform rtl:rotate-135 -rotate-45" /> {{ __('admin.distribution.outbound_short') ?? 'Out' }}
                                                            @else
                                                                <x-heroicon-s-arrow-uturn-left class="w-3.5 h-3.5 rtl:-scale-x-100" /> {{ __('admin.distribution.return_short') ?? 'Ret' }}
                                                            @endif
                                                        </span>
                                                        {{ $assignment['tour_name'] }}
                                                    </span>

                                                    {{-- A locked direction cannot be removed; only unlocked bookings can. --}}
                                                    @if($assignment['is_locked'])
                                                        <span class="text-xs font-bold text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md border border-gray-200 flex items-center gap-1"
                                                              title="{{ __('admin.fleet.leg_locked_hint') }}">
                                                            <x-heroicon-s-lock-closed class="w-3.5 h-3.5" /> {{ __('admin.distribution.locked') }}
                                                        </span>
                                                    @else
                                                        <button wire:click="unassignBooking({{ json_encode($assignment['booking_ids']) }}, '{{$assignment['direction'] }}')"
                                                                class="text-xs font-bold text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-2.5 py-1 rounded-md transition-colors flex items-center gap-1 border border-red-200 shadow-sm"
                                                                title="{{ __('admin.distribution.unassign_leg') ?? 'Unassign this leg' }}">
                                                            <x-heroicon-s-x-mark class="w-3.5 h-3.5" /> {{ __('admin.actions.remove') ?? 'Remove' }}
                                                        </button>
                                                    @endif
                                                </div>

                                                <div class="flex items-center gap-4 text-xs text-gray-700 mt-1">
                                                    <span class="flex items-center gap-1.5 font-bold"><x-heroicon-s-clock class="w-4 h-4 text-amber-500" /> <span dir="ltr">{{ $assignment['time'] }}</span></span>
                                                    <span class="flex items-center gap-1.5 font-bold"><x-heroicon-s-users class="w-4 h-4 text-indigo-500" /> {{ $assignment['pax'] }} {{ __('admin.distribution.seats') ?? 'Seats' }}</span>

                                                    @if($assignment['locked_count'] > 0 && !$assignment['is_locked'])
                                                        <span class="flex items-center gap-1.5 font-bold text-gray-500" title="{{ __('admin.fleet.leg_locked_hint') }}">
                                                            <x-heroicon-s-lock-closed class="w-3.5 h-3.5" /> {{ $assignment['locked_count'] }} {{ __('admin.distribution.locked') }}
                                                        </span>
                                                    @endif
                                                </div>

                                                @if(!empty($assignment['zones']))
                                                    <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                                                        @foreach ($assignment['zones'] as $zone)
                                                            <span class="px-2 py-1 rounded-md text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 shadow-sm flex items-center gap-1">
                                                                <x-heroicon-s-map class="w-3 h-3" /> {{ $zone }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-xs text-gray-400 font-bold italic py-2 text-center">
                                                {{ __('admin.distribution.no_trips_assigned') ?? 'No trips assigned yet today — completely free.' }}
                                            </div>
                                        @endforelse
                                    </div>
                                    
                                    <div class="mt-4">
                                        <div class="flex justify-between text-xs font-black mb-1.5">
                                            <span class="text-gray-500 uppercase tracking-widest text-[10px]">{{ __('admin.distribution.slot_capacity') ?? 'Slot Capacity' }}</span>
                                            <span class="{{ $percentage >= 100 ? 'text-red-600' : 'text-gray-800' }}" dir="ltr">
                                                {{ $vehicle->seats_taken }} / {{$vehicle->capacity }} {{ __('admin.distribution.seats') ?? 'Seats' }}
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-3.5 overflow-hidden border border-gray-200 shadow-inner">
                                            <div class="{{ $barColor }} h-full rounded-full transition-all duration-500" style="width: {{ min($percentage, 100) }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-sm text-gray-400 py-10">
                                    <x-heroicon-s-truck class="w-14 h-14 mx-auto text-gray-300 mb-4"/>
                                    <span class="font-bold">{{ __('admin.distribution.no_active_vehicles') ?? 'No active vehicles registered.' }}</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('distributionEngine', () => ({
            map: null,
            markers: [],
            items: [],   // { marker, lat, lng, color } for every plotted pin
            extras: [],  // leader lines + anchor dots drawn for spread-out pins

            init() {
                this.loadLeaflet().then(() => {
                    this.initMap();
                    this.plotBookings();
                });

                window.addEventListener('refresh-map', () => {
                    setTimeout(() => {
                        this.plotBookings();
                    }, 100);
                });
            },

            loadLeaflet() {
                if (window.L) return Promise.resolve();
                if (window.__waslaAdminLeafletPromise) return window.__waslaAdminLeafletPromise;

                window.__waslaAdminLeafletPromise = new Promise((resolve, reject) => {
                    const css = document.createElement('link');
                    css.rel = 'stylesheet';
                    css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    document.head.appendChild(css);

                    const script = document.createElement('script');
                    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    script.async = true;
                    script.defer = true;
                    script.onload = () => resolve();
                    script.onerror = reject;
                    document.head.appendChild(script);
                });

                return window.__waslaAdminLeafletPromise;
            },

            initMap() {
                const center = [27.9158, 34.3299];
                this.map = L.map('distribution-map', { zoomControl: true }).setView(center, 11);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(this.map);

                // Re-spread overlapping pins every time the zoom level changes
                this.map.on('zoomend', () => this.layoutMarkers());
            },

            createCustomPin(colorHex) {
                const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="36" height="48"><path fill="${colorHex}" stroke="#ffffff" stroke-width="15" d="M172.268 501.67C26.97 291.031 0 269.413 0 192 0 85.961 85.961 0 192 0s192 85.961 192 192c0 77.413-26.97 99.031-172.268 309.67-9.535 13.774-29.93 13.773-39.464 0zM192 272c44.183 0 80-35.817 80-80s-35.817-80-80-80-80 35.817-80 80 35.817 80 80 80z"/></svg>`;
                return 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg);
            },

            clearPins() {
                this.markers.forEach(marker => this.map.removeLayer(marker));
                this.extras.forEach(layer => this.map.removeLayer(layer));
                this.markers = [];
                this.extras = [];
                this.items = [];
            },

            plotBookings() {
                if (!this.map) return;

                const dataNode = document.getElementById('map-data');
                if (!dataNode) return;

                const currentBookings = JSON.parse(dataNode.getAttribute('data-bookings') || '[]');
                const selectedIds = JSON.parse(dataNode.getAttribute('data-selected') || '[]').map(String);
                const paletteAssignments = JSON.parse(dataNode.getAttribute('data-palette-assignments') || '{}');

                this.clearPins();

                if (selectedIds.length === 0) {
                    this.map.setView([27.9158, 34.3299], 11);
                    return;
                }

                const boundsPoints = [];

                currentBookings.forEach(booking => {
                    if (!selectedIds.includes(String(booking.id))) return;
                    if (!booking.pickup_lat || !booking.pickup_lng) return;

                    const lat = parseFloat(booking.pickup_lat);
                    const lng = parseFloat(booking.pickup_lng);
                    const pinColor = paletteAssignments[String(booking.id)] || '#1877C9';

                    const icon = L.icon({
                        iconUrl: this.createCustomPin(pinColor),
                        iconSize: [36, 48],
                        iconAnchor: [18, 48],
                        popupAnchor: [0, -48]
                    });

                    const marker = L.marker([lat, lng], {
                        icon: icon,
                        title: booking.pickup_name + ' (' + booking.seats_count + ' {{ __('admin.distribution.seats') ?? "Seats" }})',
                        zIndexOffset: 1000
                    }).addTo(this.map);

                    marker.bindPopup(`
                        <div style="padding: 5px; font-family: sans-serif; min-width: 150px; border-top: 4px solid ${pinColor}; border-radius: 4px;" dir="${document.dir || 'ltr'}">
                            <strong style="color: ${pinColor}; font-size: 14px;">${booking.pickup_name}</strong><br>
                            <span style="color: #6b7280; font-size: 11px;">{{ __('admin.columns.ref') ?? 'Ref' }}: ${booking.booking_reference}</span><br>
                            <span style="font-weight: bold; font-size: 13px; margin-top: 4px; display: inline-block;" dir="ltr">
                                💺 ${booking.seats_count} {{ __('admin.distribution.seats') ?? 'Seats' }}
                            </span>
                        </div>
                    `);

                    this.markers.push(marker);
                    this.items.push({ marker, lat, lng, color: pinColor });
                    boundsPoints.push([lat, lng]);
                });

                if (boundsPoints.length === 1) {
                    this.map.setView(boundsPoints[0], 14);
                } else if (boundsPoints.length > 1) {
                    this.map.fitBounds(L.latLngBounds(boundsPoints), { maxZoom: 16, padding: [30, 30] });
                }

                this.layoutMarkers();
            },

            layoutMarkers() {
                if (!this.map) return;

                this.extras.forEach(layer => this.map.removeLayer(layer));
                this.extras = [];

                const items = this.items;
                if (!items.length) return;

                const GROUP_DISTANCE = 30; 
                const PIN_SPACING = 40;    
                const MIN_RADIUS = 32;     

                const points = items.map(it => this.map.project([it.lat, it.lng]));
                const used = new Array(items.length).fill(false);

                for (let i = 0; i < items.length; i++) {
                    if (used[i]) continue;

                    const group = [i];
                    used[i] = true;
                    for (let j = i + 1; j < items.length; j++) {
                        if (!used[j] && points[i].distanceTo(points[j]) < GROUP_DISTANCE) {
                            group.push(j);
                            used[j] = true;
                        }
                    }

                    if (group.length === 1) {
                        items[i].marker.setLatLng([items[i].lat, items[i].lng]);
                        continue;
                    }

                    const n = group.length;
                    const cx = group.reduce((sum, k) => sum + points[k].x, 0) / n;
                    const cy = group.reduce((sum, k) => sum + points[k].y, 0) / n;
                    const centerLatLng = this.map.unproject(L.point(cx, cy));
                    const radius = Math.max(MIN_RADIUS, PIN_SPACING / (2 * Math.sin(Math.PI / n)));

                    group.forEach((k, idx) => {
                        const angle = (2 * Math.PI * idx) / n;
                        const target = this.map.unproject(L.point(
                            cx + radius * Math.cos(angle),
                            cy + radius * Math.sin(angle)
                        ));

                        items[k].marker.setLatLng(target);

                        const line = L.polyline([centerLatLng, target], {
                            color: items[k].color,
                            weight: 2,
                            opacity: 0.7,
                            interactive: false
                        }).addTo(this.map);
                        this.extras.push(line);
                    });

                    const dot = L.circleMarker(centerLatLng, {
                        radius: 5,
                        color: '#ffffff',
                        weight: 2,
                        fillColor: '#111827',
                        fillOpacity: 1,
                        interactive: false
                    }).addTo(this.map);
                    this.extras.push(dot);
                }
            },

            focusMapPin(lat, lng) {
                if (!this.map || !lat || !lng) return;
                this.map.setView([parseFloat(lat), parseFloat(lng)], 17);
            }
        }));
    });
</script>
        </div> 
          <x-filament-actions::modals />
    </x-filament-panels::page>
</div>