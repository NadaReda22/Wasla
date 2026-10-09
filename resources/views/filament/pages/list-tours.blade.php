<x-filament-panels::page>
    <style>
        .wp-card {
            min-width: 320px;
            max-width: 320px;
            border-radius: 28px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            overflow: hidden;
            cursor: pointer;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .wp-card:hover { transform: translateY(-3px); box-shadow: 0 14px 28px rgba(0,0,0,0.12); }
        .wp-card.is-selected { border-color: #1877C9; box-shadow: 0 0 0 4px rgba(24,119,201,0.2); }
        .wp-card.is-paused { border-style: dashed; border-color: #d1d5db; opacity: .7; }
        .wp-card.wp-dep-card { min-width: 240px; max-width: 240px; }

        .wp-card__header {
            padding: 18px;
            color: #ffffff;
            position: relative;
            background: linear-gradient(135deg, #0A3250 0%, #1360A0 55%, #0E7A8C 100%);
        }
        .wp-card__header.is-base { background: linear-gradient(135deg, #1b3a5a 0%, #2b5c8f 100%); }

        .wp-card__toprow { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }

        .wp-pill-white {
            display: inline-flex; align-items: center; gap: 4px;
            background: #ffffff; color: #1877C9;
            border-radius: 999px; padding: 4px 10px;
            font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .03em;
            white-space: nowrap;
        }
        .wp-pill-ghost {
            display: inline-flex; align-items: center; gap: 4px;
            background: rgba(255,255,255,0.2); color: #ffffff;
            border-radius: 999px; padding: 4px 10px;
            font-size: 11px; font-weight: 700; white-space: nowrap;
        }
        .wp-badge-discount {
            display: inline-flex; align-items: center;
            background: #E63946; color: #ffffff;
            border-radius: 999px; padding: 4px 9px;
            font-size: 10px; font-weight: 900; white-space: nowrap;
        }
        .wp-offer-name { font-size: 1.2rem; font-weight: 800; color: #ffffff; line-height: 1.2; }

        .wp-tours-list {
            font-size: 10px;
            font-weight: 500;
            color: rgba(255,255,255,0.85);
            margin-top: 6px;
            display: flex;
            align-items: flex-start;
            gap: 4px;
            line-height: 1.4;
        }

        .wp-prices { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; padding: 14px; }
        .wp-price-pill { border-radius: 12px; text-align: center; padding: 10px 4px; background: #f3f4f6; }
        .wp-price-pill.is-total { background: rgba(24,119,201,0.1); }
        .wp-price-pill__label { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: #9ca3af; }
        .wp-price-pill.is-total .wp-price-pill__label { color: #1877C9; }
        .wp-price-pill__value { margin-top: 2px; font-size: 16px; font-weight: 800; color: #0A2540; }
        .wp-price-pill.is-total .wp-price-pill__value { color: #1877C9; }

        .wp-dep-time { text-align: center; margin-top: 14px; font-size: 2.2rem; font-weight: 900; letter-spacing: -0.02em; color: #ffffff; line-height: 1; }
        .wp-ret-time { display: flex; align-items: center; justify-content: center; gap: 4px; font-size: 1rem; color: rgba(255,255,255,0.8); margin-top: 6px; font-weight: 700; }

        .wp-actions { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-top: 1px solid #e5e7eb; }
        .wp-toggle { display: inline-flex; align-items: center; gap: 4px; border-radius: 999px; padding: 5px 10px; font-size: 11px; font-weight: 800; background: rgba(23,162,184,0.1); color: #0E7A8C; border: none; cursor: pointer; }
        .wp-toggle.is-paused { background: #f3f4f6; color: #9ca3af; }
        .wp-icon-btn { width: 28px; height: 28px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; color: #9ca3af; border: none; background: transparent; cursor: pointer; }
        .wp-icon-btn:hover { background: #f3f4f6; color: #0A2540; }
        .wp-icon-btn.is-delete { color: #E63946; }
        .wp-icon-btn.is-delete:hover { background: rgba(230,57,70,0.1); }
        .wp-icon-14 { width: 14px; height: 14px; }
        .wp-icon-10 { width: 10px; height: 10px; }
    </style>

    <div x-data="{
        scrollContainer(id, direction) {
            const container = document.getElementById(id);
            const scrollAmount = document.dir === 'rtl' ? (direction === 'left' ? 400 : -400) : (direction === 'left' ? -400 : 400);
            container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        }
    }" class="space-y-8">

        {{-- ========== PRICING OFFERS ========== --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
            <div class="flex flex-wrap items-center justify-between mb-5 gap-4">
                <h2 class="text-xl font-black text-gray-800 flex items-center gap-2">
                    <x-heroicon-o-tag class="w-6 h-6 text-[#F5B400]" />
                    {{ __('admin.list_tours.pricing') ?? 'Pricing' }}
                </h2>
                <div class="flex items-center gap-3">
                    @if($this->selectedPricingOfferId)
                        <button wire:click="clearPricingFilter"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-bold rounded-lg bg-white border border-gray-300 text-gray-600 hover:bg-gray-100 transition">
                            <x-heroicon-o-x-mark class="w-4 h-4 me-1" />
                            {{ __('admin.actions.clear_selection') ?? 'Clear Selection' }}
                        </button>
                    @endif
                    <button wire:click="mountAction('deleteAllPricing')"
                            class="inline-flex items-center px-4 py-2 text-sm font-bold rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition">
                        <x-heroicon-o-trash class="w-4 h-4 me-1.5" />
                        {{ __('admin.actions.delete_all') ?? 'Delete All' }}
                    </button>
                    <div class="h-8 w-px bg-gray-200"></div>
                    <button @click="scrollContainer('pricing-slider', 'left')" class="p-2.5 rounded-full bg-gray-50 hover:bg-gray-200 border border-gray-200 text-gray-700 transition shadow-sm rtl:rotate-180">
                        <x-heroicon-o-chevron-left class="w-5 h-5" />
                    </button>
                    <button @click="scrollContainer('pricing-slider', 'right')" class="p-2.5 rounded-full bg-gray-50 hover:bg-gray-200 border border-gray-200 text-gray-700 transition shadow-sm rtl:rotate-180">
                        <x-heroicon-o-chevron-right class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 mb-5">
                <div class="relative flex-1 min-w-[200px] max-w-sm">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 absolute start-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <input type="text" wire:model.live.debounce.300ms="pricingSearch"
                           placeholder="{{ __('admin.list_tours.search_offers') ?? 'Search offers...' }}"
                           class="w-full ps-10 pe-3 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm font-medium text-gray-800 placeholder:text-gray-400 focus:bg-white focus:border-[#1877C9] focus:ring-2 focus:ring-[#1877C9]/15 transition">
                </div>
                
                <div class="inline-flex bg-gray-100 rounded-xl p-1 gap-1">
                    <button wire:click="$set('pricingTypeFilter', 'all')"
                            class="px-4 py-1.5 rounded-lg text-xs font-bold transition {{ ($pricingTypeFilter ?? 'all') === 'all' ? 'bg-white shadow-sm text-[#0A2540]' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ __('admin.options.all_types') ?? 'All Types' }}
                    </button>
                    <button wire:click="$set('pricingTypeFilter', 'base')"
                            class="px-4 py-1.5 rounded-lg text-xs font-bold transition {{ ($pricingTypeFilter ?? 'all') === 'base' ? 'bg-white shadow-sm text-[#0A2540]' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ __('admin.options.base_prices') ?? 'Base Prices' }}
                    </button>
                    <button wire:click="$set('pricingTypeFilter', 'offer')"
                            class="px-4 py-1.5 rounded-lg text-xs font-bold transition {{ ($pricingTypeFilter ?? 'all') === 'offer' ? 'bg-white shadow-sm text-[#0A2540]' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ __('admin.options.offers') ?? 'Offers' }}
                    </button>
                </div>

                <div class="inline-flex bg-gray-100 rounded-xl p-1 gap-1">
                    <button wire:click="$set('pricingVehicleFilter', 'all')"
                            class="px-4 py-1.5 rounded-lg text-xs font-bold transition {{ ($pricingVehicleFilter ?? 'all') === 'all' ? 'bg-white shadow-sm text-[#0A2540]' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ __('admin.options.all_vehicles') ?? 'All Vehicles' }}
                    </button>
                    <button wire:click="$set('pricingVehicleFilter', 'microbus')"
                            class="px-4 py-1.5 rounded-lg text-xs font-bold transition {{ ($pricingVehicleFilter ?? 'all') === 'microbus' ? 'bg-white shadow-sm text-[#0A2540]' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ __('admin.options.microbus') ?? 'Microbus' }}
                    </button>
                    <button wire:click="$set('pricingVehicleFilter', 'private')"
                            class="px-4 py-1.5 rounded-lg text-xs font-bold transition {{ ($pricingVehicleFilter ?? 'all') === 'private' ? 'bg-white shadow-sm text-[#0A2540]' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ __('admin.options.private_car') ?? 'Private Car' }}
                    </button>
                </div>

                <div class="inline-flex bg-gray-100 rounded-xl p-1 gap-1">
                    <button wire:click="$set('pricingTripTypeFilter', 'all')"
                            class="px-4 py-1.5 rounded-lg text-xs font-bold transition {{ ($pricingTripTypeFilter ?? 'all') === 'all' ? 'bg-white shadow-sm text-[#0A2540]' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ __('admin.options.all_trips') ?? 'All Trips' }}
                    </button>
                    <button wire:click="$set('pricingTripTypeFilter', 'one_way')"
                            class="px-4 py-1.5 rounded-lg text-xs font-bold transition {{ ($pricingTripTypeFilter ?? 'all') === 'one_way' ? 'bg-white shadow-sm text-[#0A2540]' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ __('admin.options.one_way') ?? 'One Way' }}
                    </button>
                    <button wire:click="$set('pricingTripTypeFilter', 'round_trip')"
                            class="px-4 py-1.5 rounded-lg text-xs font-bold transition {{ ($pricingTripTypeFilter ?? 'all') === 'round_trip' ? 'bg-white shadow-sm text-[#0A2540]' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ __('admin.options.round_trip') ?? 'Round Trip' }}
                    </button>
                </div>

                @if(!empty($pricingSearch) || ($pricingTripTypeFilter ?? 'all') !== 'all' || ($pricingVehicleFilter ?? 'all') !== 'all' || ($pricingTypeFilter ?? 'all') !== 'all')
                    <button wire:click="clearPricingCardFilters"
                            class="inline-flex items-center px-3 py-2 text-sm font-bold rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition border border-red-200">
                        <x-heroicon-o-x-mark class="w-4 h-4 me-1" />
                        {{ __('admin.actions.clear_filters') ?? 'Clear Filters' }}
                    </button>
                @endif
            </div>

            @if($this->availablePricingTiers->count() > 0)
                <div id="pricing-slider" class="flex gap-4 overflow-x-auto scrollbar-hide scroll-smooth pb-4 pt-2 px-1">
                    @foreach($this->availablePricingTiers as $tier)
                        @php
                            $isSelected = $this->selectedPricingOfferId == $tier->id;
                            $cardTours = $tier->cardTours ?? collect();
                        @endphp

                        <article wire:key="tier-{{ $tier->id }}" wire:click="selectPricingOffer({{ $tier->id }})"
                             class="wp-card {{ $isSelected ? 'is-selected' : '' }} {{ !$tier->is_active ? 'is-paused' : '' }}">

                            <div class="wp-card__header {{ $tier->is_base ? 'is-base' : '' }}">
                                
                                <div class="absolute top-0 end-0 px-4 py-1.5 text-[10px] font-black uppercase tracking-widest {{ app()->getLocale() === 'ar' ? 'rounded-br-xl' : 'rounded-bl-xl' }} shadow-sm {{ $tier->is_base ? 'bg-[#0A2540] text-[#48c4d8]' : 'bg-[#E63946] text-white' }}">
                                    {{ $tier->is_base ? (__('admin.list_tours.base_price') ?? 'Base Price') : (__('admin.list_tours.special_offer') ?? 'Special Offer') }}
                                </div>

                                <div class="wp-card__toprow mt-4">
                                    <div class="flex gap-2">
                                        <span class="wp-pill-white">
                                            @if($tier->vehicle_type === 'private')
                                                <x-heroicon-o-truck class="wp-icon-10" />
                                            @else
                                                <x-heroicon-o-users class="wp-icon-10" />
                                            @endif
                                            {{ $tier->vehicle_type === 'private' ? (__('admin.options.private_car') ?? 'Private Car') : (__('admin.options.microbus') ?? 'Microbus') }}
                                        </span>
                                        <span class="wp-pill-white">
                                            @if($tier->trip_type === 'round_trip')
                                                <x-heroicon-o-arrows-right-left class="wp-icon-10" />
                                            @else
                                                <x-heroicon-o-arrow-right class="wp-icon-10" />
                                            @endif
                                            {{ $tier->trip_type === 'round_trip' ? (__('admin.options.round_trip') ?? 'Round Trip') : (__('admin.options.one_way') ?? 'One Way') }}
                                        </span>
                                    </div>

                                    <span style="display:flex; align-items:center; gap:6px;">
                                        <span class="wp-pill-ghost">
                                            <x-heroicon-o-user-group class="wp-icon-10" />
                                            {{ $tier->pax_count }} {{ __('admin.columns.pax') ?? 'Pax' }}
                                        </span>
                                    </span>
                                </div>

                                <div class="wp-offer-name">
                                    {{ $tier->offer_name }}

                                    <div class="wp-tours-list">
                                        <x-heroicon-o-map-pin class="w-3 h-3 mt-0.5 flex-shrink-0" />
                                        @if($cardTours->isEmpty())
                                            <span>{{ __('admin.list_tours.all_tours_global') ?? 'All Tours (Global)' }}</span>
                                        @else
                                            <div class="flex flex-col">
                                                <span>{{ app()->getLocale() === 'ar' ? $cardTours->pluck('name_ar')->filter()->implode(', ') : $cardTours->pluck('name_en')->implode(', ') }}</span>
                                                @if(app()->getLocale() !== 'ar' && $cardTours->pluck('name_ru')->filter()->isNotEmpty())
                                                    <span class="text-[9px] text-white/60 font-medium mt-0.5">
                                                        {{ $cardTours->pluck('name_ru')->filter()->implode(', ') }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="wp-prices">
                                <div class="wp-price-pill">
                                    <div class="wp-price-pill__label">{{ __('admin.fields.deposit_amount') ?? 'Deposit' }}</div>
                                    <div class="wp-price-pill__value">${{ number_format($tier->deposit_amount, 0) }}</div>
                                </div>
                                <div class="wp-price-pill is-total">
                                    <div class="wp-price-pill__label">{{ __('admin.fields.total_price') ?? 'Total' }}</div>
                                    <div class="wp-price-pill__value">${{ number_format($tier->total_price, 0) }}</div>
                                </div>
                            </div>

                            <div class="wp-actions">
                                <button wire:click.stop="togglePricingActive({{ $tier->id }})"
                                        class="wp-toggle {{ !$tier->is_active ? 'is-paused' : '' }}">
                                    <x-heroicon-s-power class="wp-icon-10" />
                                    {{ $tier->is_active ? (__('admin.list_tours.active') ?? 'Active') : (__('admin.list_tours.paused') ?? 'Paused') }}
                                </button>
                                <div style="display:flex; align-items:center; gap:4px;">
                                    <button wire:click.stop="mountAction('editPricingOffer', { id: {{ $tier->id }} })" aria-label="Edit" class="wp-icon-btn">
                                        <x-heroicon-o-pencil-square class="wp-icon-14" />
                                    </button>
                                    <button wire:click.stop="mountAction('deletePricingOffer', { id: {{ $tier->id }} })" aria-label="Delete" class="wp-icon-btn is-delete">
                                        <x-heroicon-o-trash class="wp-icon-14" />
                                    </button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 text-gray-400 text-sm font-medium">
                    {{ __('admin.list_tours.no_pricing_found') ?? 'No pricing configurations match your search or filter.' }}
                </div>
            @endif
        </div>

        {{-- ========== DEPARTURE TIMES ========== --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
            <div class="flex flex-wrap items-center justify-between mb-5 gap-4">
                <h2 class="text-xl font-black text-gray-800 flex items-center gap-2">
                    <x-heroicon-o-clock class="w-6 h-6 text-[#F5B400]" />
                    {{ __('admin.list_tours.departure_times') ?? 'Departure Times' }}
                </h2>
                <div class="flex items-center gap-3">
                    <button wire:click="mountAction('deleteAllDepartures')" class="inline-flex items-center px-4 py-2 text-sm font-bold rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition"><x-heroicon-o-trash class="w-4 h-4 me-1.5" /> {{ __('admin.actions.delete_all') ?? 'Delete All' }}</button>
                    <div class="h-8 w-px bg-gray-200"></div>
                    <button @click="scrollContainer('time-slider', 'left')" class="p-2.5 rounded-full bg-gray-50 hover:bg-gray-200 border border-gray-200 text-gray-700 transition shadow-sm rtl:rotate-180"><x-heroicon-o-chevron-left class="w-5 h-5" /></button>
                    <button @click="scrollContainer('time-slider', 'right')" class="p-2.5 rounded-full bg-gray-50 hover:bg-gray-200 border border-gray-200 text-gray-700 transition shadow-sm rtl:rotate-180"><x-heroicon-o-chevron-right class="w-5 h-5" /></button>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 mb-5">
                <div class="relative flex-1 max-w-xs">
                    <x-heroicon-o-calendar class="w-4 h-4 text-gray-400 absolute start-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <input type="date" wire:model.live="departureDateFilter" class="w-full ps-10 pe-3 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm font-medium text-gray-800 focus:bg-white focus:border-[#1877C9] focus:ring-2 focus:ring-[#1877C9]/15 transition">
                </div>
                <div class="relative flex-1 max-w-xs">
                    <x-heroicon-o-clock class="w-4 h-4 text-gray-400 absolute start-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <input type="time" wire:model.live="departureTimeFilter" class="w-full ps-10 pe-3 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm font-medium text-gray-800 focus:bg-white focus:border-[#1877C9] focus:ring-2 focus:ring-[#1877C9]/15 transition">
                </div>
                @if(!empty($departureDateFilter) || !empty($departureTimeFilter) || !empty($selectedDepartureId))
                    <button wire:click="clearTimeFilter" class="inline-flex items-center px-3 py-1.5 text-sm font-bold rounded-lg bg-white border border-gray-300 text-gray-600 hover:bg-gray-100 transition self-start sm:self-auto"><x-heroicon-o-x-mark class="w-4 h-4 mr-1" /> {{ __('admin.actions.clear_filters') ?? 'Clear Filters' }}</button>
                @endif
            </div>

            @if($this->availableDepartures->count() > 0)
                <div id="time-slider" class="flex gap-4 overflow-x-auto scrollbar-hide scroll-smooth pb-4 pt-2 px-1">
                    @foreach($this->availableDepartures as $dep)
                        @php
                            $timeStr = date('g:i A', strtotime($dep->departure_time));
                            $returnTimeStr = $dep->return_time ? date('g:i A', strtotime($dep->return_time)) : null;
                            $dateStr = $dep->departure_date ? \Carbon\Carbon::parse($dep->departure_date)->toDateString() : '';
                            $dateLabel = $dep->departure_date ? \Carbon\Carbon::parse($dep->departure_date)->format('D, M j') : (__('admin.list_tours.every_day') ?? 'Every day');
                            $isSelected = $this->selectedDepartureId === $dep->id;
                        @endphp
                        <article wire:key="dep-{{ $dep->id }}" wire:click="selectDepartureCard({{ $dep->id }})" class="wp-card wp-dep-card {{ $isSelected ? 'is-selected' : '' }} {{ !$dep->is_active ? 'is-paused' : '' }}">
                            <div class="wp-card__header">
                                <div class="wp-card__toprow">
                                    <span class="wp-pill-white"><x-heroicon-o-calendar class="wp-icon-10" /> {{ $dateLabel }}</span>
                                    <span class="wp-pill-ghost">
                                        @if($returnTimeStr)
                                            <x-heroicon-o-arrows-right-left class="wp-icon-10" /> {{ __('admin.options.round_trip_short') ?? 'Round' }}
                                        @else
                                            <x-heroicon-o-arrow-right class="wp-icon-10" /> {{ __('admin.options.one_way_short') ?? 'One-way' }}
                                        @endif
                                    </span>
                                </div>
                                <div class="wp-dep-time">{{ $timeStr }}</div>
                                @if($returnTimeStr)
                                    <div class="wp-ret-time">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                                        {{ __('admin.list_tours.ret') ?? 'Ret:' }} {{ $returnTimeStr }}
                                    </div>
                                @endif
                            </div>
                            <div class="wp-actions">
                                <button wire:click.stop="toggleDepartureActive({{ $dep->id }})" class="wp-toggle {{ !$dep->is_active ? 'is-paused' : '' }}"><x-heroicon-s-power class="wp-icon-10" /> {{ $dep->is_active ? (__('admin.list_tours.active') ?? 'Active') : (__('admin.list_tours.paused') ?? 'Paused') }}</button>
                                <div style="display:flex; align-items:center; gap:4px;">
                                    <button wire:click.stop="mountAction('editDeparture', { id: {{ $dep->id }} })" class="wp-icon-btn"><x-heroicon-o-pencil-square class="wp-icon-14" /></button>
                                    <button wire:click.stop="mountAction('deleteDeparture', { id: {{ $dep->id }} })" class="wp-icon-btn is-delete"><x-heroicon-o-trash class="wp-icon-14" /></button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ========== FILAMENT TABLE ========== --}}
        <div class="mt-8">
            {{ $this->table }}
        </div>

      

    </div>
</x-filament-panels::page>