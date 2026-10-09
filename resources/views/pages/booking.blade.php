@extends('layouts.app')

@section('content')

{{-- PASS DB POLYGONS TO THE FRONTEND --}}
<script>
   window.bookingAreas = {!! $areas->toJson() !!};
</script>

<div class="min-h-screen bg-[#fdfbf7] pb-24 font-sans selection:bg-[#1877c9] selection:text-white" x-data="bookingEngine(@js($routes), @js(__('booking')), @js($limits))">

    {{-- TOAST NOTIFICATION --}}
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="fixed bottom-6 right-2 sm:right-6 z-[9999] max-w-md w-full bg-white shadow-2xl rounded-2xl pointer-events-auto border border-gray-100 overflow-hidden"
         style="display: none;">
        <div class="p-5">
            <div class="flex items-start">
                <div class="flex-shrink-0 mt-0.5">
                    <svg x-show="toast.type === 'error'" class="h-7 w-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg x-show="toast.type === 'warning'" class="h-7 w-7 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <svg x-show="toast.type === 'success'" class="h-7 w-7 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4 w-0 flex-1 pt-0.5">
                    <p class="text-base font-extrabold text-navy" x-text="toast.title"></p>
                    <p class="mt-1 text-sm text-gray-500 whitespace-pre-line leading-relaxed" x-text="toast.message"></p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button @click="toast.show = false" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-[#1877c9]/20">
                        <span class="sr-only">{{ __('booking.toast.close') }}</span>
                        <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    {{-- HERO HEADER --}}
     @php
        $settings = \App\Models\Setting::first();
    @endphp
  <section class="relative py-16 md:py-20 flex items-center overflow-hidden gradient-navy">
        <div class="absolute inset-0">
            <img src="{{ !empty($settings?->booking_background) ? asset('storage/' . $settings->booking_background) : asset('images/booking-background.png') }}" 
                 alt="Sharm El Sheikh" 
                 class="w-full h-full object-cover opacity-70" />
        </div>
        <div class="absolute inset-0 bg-gradient-to-b from-navy/80 via-navy/40 to-navy"></div>

        <div class="container-max px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <div class="inline-flex items-center gap-2 badge-gold mb-6 animate-fade-in">
                <span class="w-2 h-2 bg-gold rounded-full animate-pulse"></span>
                {{ __('booking.badge') }}
            </div>
            <h1 class="text-4xl sm:text-5xl font-display font-bold text-white mb-4 animate-slide-up text-balance">
                {{ __('booking.heading') }}
            </h1>
            <p class="text-white/80 text-lg max-w-xl mx-auto animate-slide-up">
                {{ __('booking.subheading') }}
            </p>
        </div>
    </section>

    <div class="pt-10">
        {{-- INSTRUCTIONS WINDOW --}}
        <div class="max-w-4xl mx-auto px-4 mb-6">
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden" x-data="{ expanded: false }">
                <button type="button" @click="expanded = !expanded"
                    class="w-full bg-[#f0f7ff] px-6 py-4 border-b border-[#e1effe] flex items-center justify-between gap-3 text-left transition-colors hover:bg-[#e6f2ff]">
                    <div class="flex items-center gap-3">
                        <div class="bg-[#1877c9] text-white rounded-full w-6 h-6 flex items-center justify-center font-bold text-sm">i</div>
                        <div>
                            <h2 class="font-extrabold text-navy text-lg leading-tight">{{ __('booking.read_this_first') }}</h2>
                            <p class="text-xs text-navy/50" x-show="!expanded">{{ __('booking.tap_to_expand') }}</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-[#1877c9] transition-transform duration-300 flex-shrink-0" :class="expanded ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="expanded" x-transition class="p-6 md:p-8" style="display: none;">
                    <x-instructions-content />
                </div>

                <div class="px-6 md:px-8 py-5 border-t border-gray-100 bg-gray-50/50">
                    <label class="flex items-center gap-3 cursor-pointer group w-max select-none">
                        <input type="checkbox" x-model="agreedToTerms" class="hidden">
                        <div class="w-6 h-6 rounded-full border-2 transition-all duration-200 flex items-center justify-center shadow-sm"
                             :class="agreedToTerms ? 'border-[#1877c9] bg-[#1877c9]' : 'border-gray-300 bg-white'">
                            <svg class="w-3.5 h-3.5 text-white transition-opacity duration-200"
                                 :class="agreedToTerms ? 'opacity-100' : 'opacity-0'"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span class="font-bold transition-colors" :class="agreedToTerms ? 'text-[#1877c9]' : 'text-navy group-hover:text-[#1877c9]'">
                            {{ __('booking.agree_to_instructions') }}
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <!-- INTERACTIVE STEPPER -->
        <div class="max-w-4xl mx-auto px-4 mb-8">
            <div class="bg-white rounded-full shadow-sm border border-gray-100 py-3 px-6 sm:px-10 flex justify-between items-center relative overflow-hidden">
                <div class="absolute top-1/2 left-10 right-10 h-[2px] bg-gray-100 -z-0 -translate-y-1/2"></div>
                <div class="absolute top-1/2 left-10 h-[2px] bg-[#10b981] -z-0 -translate-y-1/2 transition-all duration-500"
                     :style="isStep3Complete ? 'width: 100%' : (isStep2Complete ? 'width: 80%' : (isStep1Complete ? 'width: 40%' : 'width: 0%'))"></div>

                <div class="relative z-10 bg-white px-2 sm:px-4 flex items-center gap-2 sm:gap-3 rounded-full">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-extrabold transition-all duration-300 shadow-sm"
                         :class="isStep1Complete ? 'bg-[#10b981] text-white ring-4 ring-[#10b981]/20' : 'bg-gold text-navy'">
                        <span x-show="!isStep1Complete">1</span>
                        <svg x-show="isStep1Complete" style="display:none;" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span class="font-extrabold text-sm hidden sm:block transition-colors duration-300" :class="isStep1Complete ? 'text-[#10b981]' : 'text-navy'">{{ __('booking.stepper.trip_details') }}</span>
                </div>

                <div class="relative z-10 bg-white px-2 sm:px-4 flex items-center gap-2 sm:gap-3 rounded-full">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-extrabold transition-all duration-300 shadow-sm"
                         :class="isStep2Complete ? 'bg-[#10b981] text-white ring-4 ring-[#10b981]/20' : (isStep1Complete ? 'bg-gold text-navy' : 'bg-gray-100 text-gray-400 border border-gray-200')">
                        <span x-show="!isStep2Complete">2</span>
                        <svg x-show="isStep2Complete" style="display:none;" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span class="font-extrabold text-sm hidden sm:block transition-colors duration-300" :class="isStep2Complete ? 'text-[#10b981]' : (isStep1Complete ? 'text-navy' : 'text-gray-400')">{{ __('booking.stepper.your_info') }}</span>
                </div>

                <div class="relative z-10 bg-white px-2 sm:px-4 flex items-center gap-2 sm:gap-3 rounded-full">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-extrabold transition-all duration-300 shadow-sm"
                         :class="isStep3Complete ? 'bg-[#10b981] text-white ring-4 ring-[#10b981]/20' : ((isStep1Complete && isStep2Complete) ? 'bg-gold text-navy' : 'bg-gray-100 text-gray-400 border border-gray-200')">
                        <span x-show="!isStep3Complete">3</span>
                        <svg x-show="isStep3Complete" style="display:none;" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span class="font-extrabold text-sm hidden sm:block transition-colors duration-300" :class="isStep3Complete ? 'text-[#10b981]' : ((isStep1Complete && isStep2Complete) ? 'text-navy' : 'text-gray-400')">{{ __('booking.stepper.payment') }}</span>
                </div>
            </div>
        </div>

        <!-- MAIN GRID -->
        <form @submit.prevent="submitBooking" class="max-w-5xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- LEFT COLUMN: FORM -->
            <div class="lg:col-span-8 space-y-6">

                <!-- STEP 1 CARD -->
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-5 md:p-6 relative" :class="isStep1Complete ? 'ring-2 ring-[#10b981]/50' : ''">
                    <div x-show="isStep1Complete" style="display:none;" class="absolute top-6 right-6 w-8 h-8 bg-[#10b981]/10 text-[#10b981] rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>

                    <div class="flex items-start gap-4 mb-5">
                        <div class="w-10 h-10 rounded-full bg-[#f0f7ff] text-[#1877c9] flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 21h8m-4-10v.01M5 8h14M6 4h12a2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2z"></path>
                                <circle cx="8" cy="17" r="1" fill="currentColor"></circle>
                                <circle cx="16" cy="17" r="1" fill="currentColor"></circle>
                            </svg>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-[#1877c9] uppercase tracking-wider mb-1">{{ __('booking.step1.label') }}</div>
                            <h2 class="text-2xl font-extrabold text-navy leading-none mb-1">{{ __('booking.step1.title') }}</h2>
                            <p class="text-sm text-navy/60">{{ __('booking.step1.subtitle') }}</p>
                        </div>
                    </div>

                    <!-- Choose your ride -->
                    <div class="relative mb-6">
                        <div class="hidden sm:flex absolute top-1/2 right-[-1%] -translate-y-1/2 z-20 pointer-events-none">
                            <img x-show="vehicleType === 'microbus'" src="{{ asset('images/microbus-placeholder.png') }}" class="w-72 h-auto object-contain drop-shadow-2xl transition-all duration-500" alt="Microbus">
                            <img x-show="vehicleType === 'private'" style="display:none" src="{{ asset('images/vehicle-private-placeholder.png') }}" class="w-64 md:w-80 h-auto object-contain drop-shadow-2xl transition-all duration-500" alt="Private Car">
                        </div>

                        <div class="rounded-3xl overflow-hidden flex flex-col shadow-md">
                            <div class="flex flex-col sm:flex-row">
                                <div class="w-full sm:w-[60%] bg-[#1877c9] pt-6 pb-8 px-6 md:px-8 text-white relative z-10">
                                    <div class="text-[10px] font-bold text-white/70 uppercase tracking-wider mb-2">{{ __('booking.step1.trip_setup') }}</div>
                                    <h3 class="text-2xl md:text-3xl font-extrabold mb-2">{{ __('booking.step1.choose_ride') }}</h3>
                                    
                                    <div class="flex gap-2 mb-6 bg-white/10 p-1 rounded-full w-max mt-4">
                                        <button type="button" @click="tripType = 'one-way'" :class="tripType === 'one-way' ? 'bg-white text-[#1877c9] shadow-sm' : 'text-white hover:bg-white/10'" class="px-4 py-1.5 rounded-full font-bold text-sm transition-all flex items-center gap-2">
                                            {{ __('booking.step1.one_way') }}
                                        </button>
                                        <button type="button" @click="tripType = 'round-trip'" :class="tripType === 'round-trip' ? 'bg-white text-[#1877c9] shadow-sm' : 'text-white hover:bg-white/10'" class="px-4 py-1.5 rounded-full font-bold text-sm transition-all flex items-center gap-2">
                                            {{ __('booking.step1.round_trip') }}
                                        </button>
                                    </div>

                                    <div class="space-y-3">
                                        <label class="block cursor-pointer">
                                            <input type="radio" x-model="vehicleType" value="microbus" class="peer sr-only">
                                            <div class="flex items-center justify-between p-3.5 rounded-2xl transition-all duration-200 peer-checked:bg-white peer-checked:text-navy text-white bg-white/10 hover:bg-white/20">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 rounded-full bg-[#f0f7ff] text-[#1877c9] flex items-center justify-center shrink-0">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8M8 11h8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v10a2 2 0 002 2z"></path></svg>
                                                    </div>
                                                    <div>
                                                        <div class="font-extrabold text-sm">{{ __('booking.step1.shared_microbus') }}</div>
                                                        <div class="text-[10px] opacity-80 font-medium">{{ __('booking.step1.shared_microbus_desc') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>

                                        <label class="block cursor-pointer">
                                            <input type="radio" x-model="vehicleType" value="private" class="peer sr-only">
                                            <div class="flex items-center justify-between p-3.5 rounded-2xl transition-all duration-200 peer-checked:bg-white peer-checked:text-navy text-white bg-white/10 hover:bg-white/20">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 rounded-full bg-[#f0f7ff] text-[#1877c9] flex items-center justify-center shrink-0">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16a2 2 0 11-4 0 2 2 0 014 0zm12 0a2 2 0 11-4 0 2 2 0 014 0zm-8-3V9m0 0H6.5a2.5 2.5 0 00-2.3 1.5l-1 2.5h16l-1-2.5a2.5 2.5 0 00-2.3-1.5H12z"></path></svg>
                                                    </div>
                                                    <div>
                                                        <div class="font-extrabold text-sm">{{ __('booking.step1.private_car') }}</div>
                                                        <div class="text-[10px] opacity-80 font-medium">{{ __('booking.step1.private_car_desc') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="w-full sm:w-[40%] bg-gold relative min-h-[120px] sm:min-h-auto overflow-hidden"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Input Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                        <div>
                            <label class="block text-xs font-bold text-navy mb-2">
                                {{ __('booking.step1.route') }} <span class="text-red-500">*</span>
                            </label>
                            <select x-model="routeId" @change="updateRouteName(); departureDate=''; departureTime=''" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none transition-all duration-200 bg-white text-navy focus:border-[#1877c9] focus:ring-4 focus:ring-[#1877c9]/10 appearance-none cursor-pointer" style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'8\' viewBox=\'0 0 12 8\' fill=\'none\'><path d=\'M1 1l5 5 5-5\' stroke=\'%230A2540\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'/></svg>'); background-repeat: no-repeat; background-position: right 1rem center; padding-right: 2.5rem;">
                                <option value="">{{ __('booking.step1.select_route') }}</option>
                                <template x-for="route in routes" :key="route.id">
                                    <option :value="route.id" x-text="route.name + (route.name_ar ? ' / ' + route.name_ar : '')"></option> 
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-navy mb-2">
                                {{ __('booking.step1.date') }} <span class="text-red-500">*</span>
                            </label>
                            <select x-model="departureDate" @change="departureTime=''" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none transition-all duration-200 bg-white text-navy focus:border-[#1877c9] focus:ring-4 focus:ring-[#1877c9]/10 appearance-none cursor-pointer" style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'8\' viewBox=\'0 0 12 8\' fill=\'none\'><path d=\'M1 1l5 5 5-5\' stroke=\'%230A2540\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'/></svg>'); background-repeat: no-repeat; background-position: right 1rem center; padding-right: 2.5rem;">
                                <option value="">{{ __('booking.step1.select_date') }}</option>
                                <template x-for="date in availableDates" :key="date.value">
                                    <option :value="date.value" x-text="date.label"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-navy mb-2 flex items-center gap-1">
                                <span class="text-[#1877c9]">⏱</span> 
                                <span x-text="tripType === 'round-trip' ? t.step1.pickup_return_time : t.step1.pickup_time">{{ __('booking.step1.pickup_time') }}</span>
                                <span class="text-red-500">*</span>
                            </label>
                            <select x-model="departureTime" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none transition-all duration-200 bg-white text-navy focus:border-[#1877c9] focus:ring-4 focus:ring-[#1877c9]/10 appearance-none cursor-pointer" style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'8\' viewBox=\'0 0 12 8\' fill=\'none\'><path d=\'M1 1l5 5 5-5\' stroke=\'%230A2540\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'/></svg>'); background-repeat: no-repeat; background-position: right 1rem center; padding-right: 2.5rem;">
                                <option value="">{{ __('booking.step1.select_time') }}</option>
                                <template x-for="time in availableTimes" :key="time">
                                    <option :value="time" x-text="time"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-navy mb-2">
                                {{ __('booking.step1.passengers') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center justify-between border border-gray-200 rounded-xl px-4 py-1.5 bg-transparent">
                                <button type="button" @click="if(passengers > 1) passengers--" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-navy font-bold hover:bg-gray-200 transition-colors">-</button>
                                <div class="flex flex-col items-center justify-center">
                                    <span class="font-extrabold text-navy text-lg leading-none" x-text="passengers"></span>
                                    <span class="text-[8px] font-bold text-[#1877c9] uppercase tracking-widest">{{ __('booking.step1.traveler') }}</span>
                                </div>
                                <button type="button" @click="
                                    let maxLimit = vehicleType === 'private' ? limits.private : limits.microbus; 
                                    if(passengers < maxLimit) { 
                                        passengers++; 
                                    } else { 
                                        let message = vehicleType === 'private' 
                                            ? (t.toast.private_limit ? t.toast.private_limit.replace(':limit', maxLimit) : 'Private cars support up to ' + maxLimit + ' passengers.') 
                                            : (t.toast.microbus_limit ? t.toast.microbus_limit.replace(':limit', maxLimit) : 'Maximum passenger limit reached.');
                                        showToast(t.toast.limit_reached || 'Limit Reached', message, 'warning'); 
                                    }
                                " class="w-8 h-8 rounded-full bg-gold text-navy flex items-center justify-center font-bold hover:bg-gold-dark transition-colors">+</button>
                            </div>
                        </div>
                    </div>

                    {{-- Hotel Map --}}
                    <div class="w-full mt-5">
                        <label class="flex items-center gap-2 text-xs font-bold text-navy mb-1">
                            <svg class="w-4 h-4 text-[#1877c9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            {{ __('booking.step1.hotel_label') }} <span class="text-red-500">*</span>
                        </label>
                        <p class="text-[11px] text-navy/60 font-medium mb-3">{{ __('booking.step1.hotel_hint') }}</p>
                        
                        <div class="bg-[#f8f9fa] rounded-xl border border-gray-200 hover:border-[#1877c9] transition-colors" 
                             @hotel-selected.window="
                                hotel = $event.detail.name_en; 
                                hotelDisplayEn = $event.detail.name_en; 
                                hotelDisplayAr = $event.detail.name_ar || ''; 
                                pickupLat = $event.detail.latitude; 
                                pickupLng = $event.detail.longitude; 
                                pickupAddress = ''; 
                                if($event.detail.area_id) area_id = $event.detail.area_id;
                             ">
                            <x-booking.hotel-picker />
                        </div>
                    </div>
                </div> <!-- END STEP 1 CARD -->

                <!-- STEP 2 CARD: YOUR INFO -->
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-5 md:p-6 mt-6 relative" :class="isStep2Complete ? 'ring-2 ring-[#10b981]/50' : ''">
                    <div x-show="isStep2Complete" style="display:none;" class="absolute top-6 right-6 w-8 h-8 bg-[#10b981]/10 text-[#10b981] rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>

                    <div class="flex items-start gap-4 mb-6">
                        <div class="w-10 h-10 rounded-full bg-[#f0f7ff] text-[#1877c9] flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-[#1877c9] uppercase tracking-wider mb-1">{{ __('booking.step2.label') }}</div>
                            <h2 class="text-2xl font-extrabold text-navy leading-none mb-1">{{ __('booking.step2.title') }}</h2>
                            <p class="text-sm text-navy/60">{{ __('booking.step2.subtitle') }}</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Name -->
                            <div>
                                <label for="name" class="flex items-center gap-2 text-xs font-bold text-navy mb-2">
                                    <svg class="w-4 h-4 text-[#1877c9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    {{ __('booking.step2.full_name') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="name" name="name" x-model="leadName" autocomplete="name" placeholder="{{ __('booking.step2.name_placeholder') }}" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none transition-all duration-200 bg-white text-navy focus:border-[#1877c9] focus:ring-4 focus:ring-[#1877c9]/10">
                            </div>
                            
                            <!-- Email -->
                            <div>
                                <label for="email" class="flex items-center gap-2 text-xs font-bold text-navy mb-2">
                                    <svg class="w-4 h-4 text-[#1877c9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    {{ __('booking.step2.email') }} <span class="font-medium text-gray-400 font-normal">{{ __('booking.step1.optional') }}</span>
                                </label>
                                <input type="email" id="email" name="email" x-model="email" autocomplete="email" placeholder="example@email.com" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none transition-all duration-200 bg-white text-navy focus:border-[#1877c9] focus:ring-4 focus:ring-[#1877c9]/10">
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="tel" class="flex items-center gap-2 text-xs font-bold text-navy mb-1">
                                    <svg class="w-4 h-4 text-[#1877c9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    {{ __('booking.step2.whatsapp_number') }} <span class="text-red-500">*</span>
                                </label>
                                <p class="text-[10px] text-navy/50 mb-2">{{ __('booking.step2.whatsapp_hint') }}</p>
                                <input type="tel" id="tel" name="tel" x-model="whatsapp" autocomplete="tel" placeholder="+20 100 000 0000" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none transition-all duration-200 bg-white text-navy focus:border-[#1877c9] focus:ring-4 focus:ring-[#1877c9]/10">
                            </div>
                            
                            <!-- Nationality -->
                            <div x-data="{ open: false, search: '' }" class="relative" @click.away="open = false">
                                <label class="flex items-center gap-2 text-xs font-bold text-navy mb-2">
                                    <svg class="w-4 h-4 text-[#1877c9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ __('booking.step2.nationality') }} <span class="text-red-500">*</span>
                                </label>
                                
                                <button type="button" @click="open = !open" 
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none transition-all duration-200 bg-white text-navy focus:border-[#1877c9] focus:ring-4 focus:ring-[#1877c9]/10 text-left flex justify-between items-center h-[50px]">
                                    <span x-text="nationality ? countries.find(c => c.code === nationality)?.name : t.step2.select_country" :class="nationality ? 'text-navy' : 'text-gray-400'"></span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>

                                <div x-show="open" x-transition.opacity class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden" style="display: none;">
                                    <div class="p-2 border-b border-gray-100 bg-gray-50/50">
                                        <div class="relative">
                                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                            <input type="text" x-model="search" :placeholder="t.step2.search_country" class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#1877c9] focus:ring-1 focus:ring-[#1877c9]/20">
                                        </div>
                                    </div>
                                    <ul class="max-h-48 overflow-y-auto p-1 scrollbar-thin">
                                        <template x-for="country in countries.filter(c => c.name.toLowerCase().includes(search.toLowerCase()))" :key="country.code">
                                            <li @click="nationality = country.code; open = false; search = ''" 
                                                class="px-3 py-2 text-sm text-navy hover:bg-[#f0f7ff] hover:text-[#1877c9] cursor-pointer rounded-lg transition-colors flex items-center justify-between">
                                                <span x-text="country.name"></span>
                                                <svg x-show="nationality === country.code" class="w-4 h-4 text-[#1877c9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            </li>
                                        </template>
                                        <li x-show="!isLoadingCountries && countries.filter(c => c.name.toLowerCase().includes(search.toLowerCase())).length === 0" class="px-3 py-4 text-sm text-gray-400 text-center" x-text="t.step2.no_countries_found"></li>
                                        <li x-show="isLoadingCountries" class="px-3 py-4 text-sm text-gray-400 text-center" x-text="t.step2.loading_nationalities"></li>
                                    </ul>
                                </div>
                            </div>
                            
                            <!-- Room Number & Reception Number -->
                            <div class="md:col-span-2 mt-2 border-t border-gray-100 pt-4">
                                <p class="text-[11px] text-navy/60 font-medium mb-3">{{ __('booking.step2.room_or_reception_hint') }}</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <!-- Room -->
                                    <div>
                                        <label for="room" class="flex items-center gap-2 text-xs font-bold text-navy mb-2">
                                            <svg class="w-4 h-4 text-[#1877c9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                            {{ __('booking.step2.room_number') }} <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" id="room" name="room" x-model="roomNumber"
                                               placeholder="{{ __('booking.step2.room_placeholder') }}"
                                               class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none transition-all duration-200 bg-white text-navy focus:border-[#1877c9] focus:ring-4 focus:ring-[#1877c9]/10">
                                    </div>

                                    <!-- Reception number -->
                                    <div>
                                        <label for="reception" class="flex items-center gap-2 text-xs font-bold text-navy mb-2">
                                            <svg class="w-4 h-4 text-[#1877c9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                            {{ __('booking.step2.reception_number') }} <span class="text-red-500">*</span>
                                        </label>
                                        <input type="tel" id="reception" name="reception" x-model="receptionNumber"
                                               placeholder="{{ __('booking.step2.reception_placeholder') }}"
                                               class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none transition-all duration-200 bg-white text-navy focus:border-[#1877c9] focus:ring-4 focus:ring-[#1877c9]/10">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div> <!-- END STEP 2 CARD -->

                <!-- STEP 3 CARD: PAYMENT METHOD -->
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-5 md:p-6 mt-6 relative" :class="isStep3Complete ? 'ring-2 ring-[#1877c9]/50' : ''">
                    <div class="flex items-start gap-4 mb-5">
                        <div class="w-10 h-10 rounded-full bg-[#f0f7ff] text-[#1877c9] flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-[#1877c9] uppercase tracking-wider mb-1">{{ __('booking.step3.label') }}</div>
                            <h2 class="text-2xl font-extrabold text-navy leading-none mb-1">{{ __('booking.step3.title') }}</h2>
                            <p class="text-sm text-navy/60">{{ __('booking.step3.subtitle') }}</p>
                        </div>
                    </div>

                    <!-- Mandatory Kashier Selection -->
                    <div class="mt-4" x-show="isStep1Complete && isStep2Complete" x-transition>
                        <label class="block cursor-pointer">
                            <input type="radio" x-model="paymentMethod" value="kashier" class="peer sr-only">
                            <div class="flex items-center justify-between p-4 rounded-2xl border-2 transition-all duration-200 peer-checked:border-[#1877c9] peer-checked:bg-[#f0f7ff] border-gray-100 hover:border-gray-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-white shadow-sm flex items-center justify-center shrink-0 p-2 border border-gray-100">
                                        <span class="font-bold text-navy text-xs">Kashier</span>
                                    </div>
                                    <div>
                                        <div class="font-extrabold text-sm text-navy">Pay Deposit Securely</div>
                                        <div class="text-[10px] text-gray-500 font-medium">Credit / Debit Card</div>
                                    </div>
                                </div>
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors" :class="paymentMethod === 'kashier' ? 'border-[#1877c9] bg-[#1877c9]' : 'border-gray-300 bg-white'">
                                    <svg class="w-3 h-3 text-white transition-opacity" :class="paymentMethod === 'kashier' ? 'opacity-100' : 'opacity-0'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </div>
                        </label>
                    </div>
                    
                    <div x-show="!(isStep1Complete && isStep2Complete)" class="p-4 bg-gray-50 rounded-xl border border-gray-100 text-center">
                        <span class="text-xs font-bold text-gray-400">Please complete Steps 1 & 2 first</span>
                    </div>
                </div> <!-- END STEP 3 CARD -->

            </div> <!-- END LEFT COLUMN -->

            <!-- RIGHT COLUMN: SUMMARY CARD -->
            <div class="lg:col-span-4">
                <div class="bg-[#1b3a5a] rounded-[2rem] p-6 md:p-8 sticky top-28 shadow-2xl text-white">

                    <div class="flex justify-between items-start mb-6">
                        <div class="text-[10px] font-bold text-gold uppercase tracking-wider">{{ __('booking.summary.title') }}</div>
                        <div class="bg-white/10 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider" x-text="tripType === 'one-way' ? t.step1.one_way : t.step1.round_trip"></div>
                    </div>

                    {{-- Tour Name (English & Arabic Separated) --}}
                    <div class="mb-6">
                        <h3 class="text-2xl font-extrabold leading-tight" x-text="routeName || t.summary.select_route"></h3>
                        <div x-show="routeArabicName" class="text-sm font-medium text-white/70 mt-1" x-text="routeArabicName"></div>
                    </div>
                    
                    <p class="text-sm text-white/70 font-medium mb-6" x-text="vehicleType === 'private' ? t.summary.private_sedan : t.summary.shared_microbus"></p>

                    <div class="space-y-4 border-t border-white/10 pt-6">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-white/70" x-text="tripType === 'round-trip' ? t.summary.schedule : t.summary.outbound"></span>
                            <span class="font-bold text-right">
                                <span x-text="departureDate ? departureDate : t.summary.select_date"></span>
                                <span x-show="departureTime" x-text="' • ' + departureTime"></span>
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-white/70">{{ __('booking.summary.passengers') }}</span>
                            <span class="font-bold" x-text="passengers"></span>
                        </div>
                        
                        {{-- Hotel Name (English & Arabic Separated) --}}
                        <div class="flex justify-between items-start text-sm">
                            <span class="text-white/70 mt-0.5">{{ __('booking.summary.pickup_point') }}</span>
                            <div class="text-right flex flex-col items-end">
                                <span class="font-bold" x-text="hotelDisplayEn ? hotelDisplayEn : t.summary.not_selected"></span>
                                <span x-show="hotelDisplayAr" class="text-[11px] text-white/70 mt-0.5" x-text="hotelDisplayAr"></span>
                            </div>
                        </div>
                        
                        <!-- Applied Offers Section -->
                        <template x-if="pricing.appliedOffers.length > 0">
                            <div class="mt-4 p-3 bg-green-500/10 rounded-xl border border-green-500/20 animate-fade-in">
                                <span class="block text-[10px] font-bold text-green-400 uppercase tracking-wider mb-2">{{ __('booking.summary.offers_applied') }}</span>
                                <ul class="text-sm font-bold text-white space-y-1.5">
                                    <template x-for="offer in pricing.appliedOffers" :key="offer">
                                        <li class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            <span x-text="offer"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>
                    </div>

                    <div class="space-y-3 border-t border-white/10 pt-6 mt-6 mb-8">
                        <div class="flex justify-between items-end">
                            <span class="text-white/70 text-sm mb-1">{{ __('booking.summary.total_fare') }}</span>
                            <div class="text-right">
                                <template x-if="pricing.hasDiscount">
                                    <span class="text-white/50 line-through text-sm mr-2" x-text="'$' + pricing.originalTotal"></span>
                                </template>
                                <span class="font-extrabold text-2xl" x-text="'$' + pricing.total"></span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-white/70 text-sm">{{ __('booking.summary.deposit_due_now') }}</span>
                            <div class="text-right">
                                <template x-if="pricing.hasDiscount && parseFloat(pricing.originalDeposit) > parseFloat(pricing.deposit)">
                                    <span class="text-white/50 line-through text-xs mr-2" x-text="'$' + pricing.originalDeposit"></span>
                                </template>
                                <span class="font-extrabold text-gold text-lg" x-text="'$' + pricing.deposit"></span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-white/70 text-sm">{{ __('booking.summary.balance_on_board') }}</span>
                            <span class="font-bold text-lg text-white" x-text="'$' + pricing.balance"></span>
                        </div>
                    </div>

                    <!-- Enforced Validation Disable + isSubmitting logic -->
                    <div x-show="!pricing.isValid && isStep1Complete" class="text-red-400 text-xs font-bold text-center mb-3">
                        {{ __('booking.summary.pricing_invalid') }}
                    </div>
                    <button type="submit" :disabled="!agreedToTerms || !isStep3Complete || isSubmitting || !pricing.isValid" :class="(!agreedToTerms || !isStep3Complete || isSubmitting || !pricing.isValid) ? 'opacity-50 cursor-not-allowed' : 'hover:-translate-y-0.5 hover:shadow-lg'" class="w-full bg-[#48c4d8] text-white rounded-full py-4 font-extrabold text-sm transition-all flex flex-col items-center justify-center gap-1 shadow-md active:scale-[0.98]">
                        <span x-show="!isSubmitting">Proceed to Secure Payment</span>
                        <span x-show="isSubmitting" style="display: none;">Redirecting to Kashier...</span>
                        <span class="text-[9px] font-medium opacity-80 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            {{ __('booking.summary.secured_by') }}
                        </span>
                    </button>

                </div>
            </div> <!-- END RIGHT COLUMN -->

        </form>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('bookingEngine', (dbRoutes, translations, vehicleLimits) => {
            
            const urlParams = new URLSearchParams(window.location.search);
            const initialRoute = urlParams.get('tour') || '';
            const initialDate = urlParams.get('date') || '';
            const locale = document.documentElement.lang || 'en';

            return {
                t: translations,
                limits: vehicleLimits,
                agreedToTerms: false,
                isSubmitting: false,
                tripType: 'one-way',
                vehicleType: 'microbus',
                paymentMethod: '', 
                
                routeId: '',
                routeName: '',
                routeArabicName: '', // Separated
                departureDate: '', 
                departureTime: '',
                passengers: 1,

                leadName: '{{ auth()->check() ? auth()->user()->name : '' }}',
                email: '{{ auth()->check() ? auth()->user()->email : '' }}',
                whatsapp: '{{ auth()->check() ? auth()->user()->phone : '' }}',
                nationality: '',
                roomNumber: '',
                receptionNumber: '',
                
                hotel: '',
                hotelDisplayEn: '', // Separated EN
                hotelDisplayAr: '', // Separated AR
                pickupLat: null,
                pickupLng: null,
                pickupAddress: '',
                area_id: null,

                routes: dbRoutes,
                countries: [], 
                isLoadingCountries: true,

                toast: { show: false, title: '', message: '', type: 'error' },
                toastTimeout: null,

                showToast(title, message, type = 'error') {
                    this.toast.title = title;
                    this.toast.message = message;
                    this.toast.type = type;
                    this.toast.show = true;

                    if (this.toastTimeout) clearTimeout(this.toastTimeout);
                    this.toastTimeout = setTimeout(() => {
                        this.toast.show = false;
                    }, 10000); 
                },

                init() {
                    this.$nextTick(() => {
                        if (initialRoute) {
                            this.routeId = initialRoute;
                            this.updateRouteName();
                            
                            this.$nextTick(() => {
                                if (initialDate && this.availableDates.find(d => d.value === initialDate)) {
                                    this.departureDate = initialDate;
                                    
                                    this.$nextTick(() => {
                                        if (this.availableTimes && this.availableTimes.length > 0) {
                                            this.departureTime = this.availableTimes[0];
                                        }
                                    });
                                }
                            });
                        }
                    });

                    this.$watch('vehicleType', (val) => {
                        let maxLimit = val === 'private' ? this.limits.private : this.limits.microbus;
                        if (this.passengers > maxLimit) {
                            this.passengers = maxLimit;
                            this.showToast(this.t.toast.action_required || 'Notice', this.t.toast.adjusted_capacity || 'Passenger count adjusted to match vehicle capacity.', 'warning');
                        }
                    });

                    try {
                        const regionNames = new Intl.DisplayNames([locale], { type: 'region' });
                        let generatedCountries = [];

                        const A = 65, Z = 90;
                        for (let i = A; i <= Z; ++i) {
                            for (let j = A; j <= Z; ++j) {
                                let code = String.fromCharCode(i) + String.fromCharCode(j);
                                try {
                                    let name = regionNames.of(code);
                                    if (code !== name && !name.match(/[0-9]/)) {
                                        generatedCountries.push({ code: code, name: name });
                                    }
                                } catch (e) {}
                            }
                        }
                        
                        const uniqueCountries = Array.from(
                            new Map(generatedCountries.map(item => [item.name, item])).values()
                        );

                        this.countries = uniqueCountries.sort((a, b) => a.name.localeCompare(b.name, locale));
                        this.isLoadingCountries = false;

                    } catch (error) {
                        console.error("Native Intl dropdown generation failed, falling back.", error);
                        this.countries = [
                            {code: 'EG', name: 'Egypt'},
                            {code: 'GB', name: 'United Kingdom'},
                            {code: 'RU', name: 'Russia'},
                            {code: 'DE', name: 'Germany'},
                            {code: 'SA', name: 'Saudi Arabia'}
                        ];
                        this.isLoadingCountries = false;
                    }
                },

                get selectedRouteData() {
                    return this.routes.find(r => String(r.id) === String(this.routeId)) || null;
                },

                get availableDates() {
                    if (!this.selectedRouteData) return [];
                    return Object.keys(this.selectedRouteData.schedule).map(dateStr => {
                        const d = new Date(dateStr);
                        return {
                            value: dateStr,
                            label: d.toLocaleDateString(locale, { weekday: 'short', month: 'short', day: 'numeric' })
                        };
                    });
                },

                get availableTimes() {
                    if (!this.selectedRouteData || !this.departureDate) return [];
                    return this.selectedRouteData.schedule[this.departureDate] || [];
                },

                get isStep1Complete() {
                    return this.routeId !== '' && this.departureDate !== '' && this.departureTime !== '';
                },

                get isStep2Complete() {
                    return this.isStep1Complete && 
                           this.leadName.trim() !== '' && 
                           this.whatsapp.trim() !== '' &&
                           this.nationality !== '' &&
                           this.hotel.trim() !== '' &&
                           this.roomNumber.trim() !== '' &&   
                           this.receptionNumber.trim() !== '';
                },
                
                get isStep3Complete() {
                    return this.isStep1Complete && this.isStep2Complete && this.paymentMethod === 'kashier';
                },
                
                updateRouteName() {
                    if (this.selectedRouteData) {
                        this.routeName = this.selectedRouteData.name;
                        this.routeArabicName = this.selectedRouteData.name_ar || '';
                    } else {
                        this.routeName = '';
                        this.routeArabicName = '';
                    }
                },

                get pricing() {
                    const emptyPricing = { total: '0.00', deposit: '0.00', balance: '0.00', originalTotal: '0.00', originalDeposit: '0.00', hasDiscount: false, appliedOffers: [], isValid: false };
                    
                    if (!this.selectedRouteData) return emptyPricing;
                    
                    const type = this.tripType === 'round-trip' ? 'round_trip' : 'one_way';
                    const vehicle = this.vehicleType;
                    
                    let availableTiers = this.selectedRouteData.tiers.filter(t => t.type === type && t.vehicle === vehicle);
                    
                    const baseTier = availableTiers.find(t => t.is_base);
                    let offers = availableTiers.filter(t => !t.is_base).sort((a, b) => b.pax - a.pax);

                    if (!baseTier && offers.length === 0) {
                        return emptyPricing; 
                    }

                    let total = 0;
                    let deposit = 0;
                    let remainingPax = this.passengers;
                    let appliedOffers = [];
                    let originalTotal = 0;
                    let originalDeposit = 0;

                    if (vehicle === 'private') {
                        let pTier = offers.length > 0 ? offers[0] : baseTier; 
                        let capacity = pTier.pax > 0 ? pTier.pax : 3;
                        let carsNeeded = Math.ceil(remainingPax / capacity);
                        
                        total = carsNeeded * pTier.price;
                        deposit = carsNeeded * pTier.deposit;

                        let comparisonTier = baseTier ? baseTier : pTier;
                        originalTotal = carsNeeded * comparisonTier.price;
                        originalDeposit = carsNeeded * comparisonTier.deposit;
                        
                        if (originalTotal > total && pTier.name) {
                            let pct = Math.round(((originalTotal - total) / originalTotal) * 100);
                            appliedOffers.push(`${pTier.name} (${pct}% ${this.t.summary.off})`);
                        }
                    } else {
                        for (let offer of offers) {
                            if (remainingPax <= 0) break;
                            if (remainingPax >= offer.pax) {
                                let multiplier = Math.floor(remainingPax / offer.pax);
                                
                                total += offer.price * multiplier;
                                deposit += offer.deposit * multiplier;
                                remainingPax -= offer.pax * multiplier;
                                
                                if (offer.discount > 0 && offer.name) {
                                    appliedOffers.push(`${offer.name} (${offer.discount}% ${this.t.summary.off_fare})`);
                                }
                            }
                        }
                        if (remainingPax > 0) {
                            if (baseTier) {
                                total += remainingPax * baseTier.price;
                                deposit += remainingPax * baseTier.deposit;
                            } else {
                                return emptyPricing;
                            }
                        }

                        if (baseTier) {
                            originalTotal = this.passengers * baseTier.price;
                            originalDeposit = this.passengers * baseTier.deposit;
                        } else {
                            originalTotal = total;
                            originalDeposit = deposit;
                        }
                    }

                    return {
                        total: total.toFixed(2),
                        originalTotal: originalTotal.toFixed(2),
                        deposit: deposit.toFixed(2),
                        originalDeposit: originalDeposit.toFixed(2),
                        balance: (total - deposit).toFixed(2),
                        hasDiscount: originalTotal > total,
                        appliedOffers: [...new Set(appliedOffers)],
                        isValid: true 
                    };
                },

                submitBooking(event) {
                    if (!this.agreedToTerms) {
                        this.showToast(this.t.toast.action_required, this.t.toast.agree_first, 'warning');
                        return;
                    }

                    const payload = {
                        routeId: this.routeId,
                        departureDate: this.departureDate,
                        departureTime: this.departureTime,
                        passengers: this.passengers,
                        leadName: this.leadName,
                        email: this.email,
                        whatsapp: this.whatsapp,
                        nationality: this.nationality,
                        roomNumber: this.roomNumber.trim() || null,
                        receptionNumber: this.receptionNumber.trim() || null,
                        
                        hotel: this.hotel, // ONLY ENGLISH SUBMITTED TO BACKEND
                        pickupLat: this.pickupLat,
                        pickupLng: this.pickupLng,
                        pickupAddress: this.pickupAddress,
                        area_id: this.area_id,
                        tripType: this.tripType,
                        vehicleType: this.vehicleType,
                        paymentMethod: this.paymentMethod, 
                    };

                    this.isSubmitting = true;

                    fetch('{{ route('booking.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(async response => {
                        const data = await response.json();
                        
                        if (!response.ok) {
                            if (response.status === 422) {
                                if (data.errors) {
                                    let errorText = '';
                                    for (const field in data.errors) {
                                        errorText += data.errors[field].join('\n') + '\n';
                                    }
                                    this.showToast(this.t.toast.validation_error || 'Validation Error', (this.t.toast.check_inputs || '') + '\n' + errorText, 'error');
                                } 
                                else if (data.message) {
                                    this.showToast(this.t.toast.booking_error || 'Error', data.message, 'error');
                                }
                            } else {
                                this.showToast(this.t.toast.error || 'Error', data.message || this.t.toast.something_wrong, 'error');
                            }
                            this.isSubmitting = false;
                            return Promise.reject('API Validation Error'); 
                        }
                        
                        return data;
                    })
                    .then(data => {
                        if (data && data.success && data.payment_url) {
                            this.showToast(
                                this.t.toast.redirecting || 'Processing', 
                                'Redirecting to secure payment...', 
                                'success'
                            );
                            window.location.href = data.payment_url;
                        } 
                        else if (data && data.success && !data.payment_url) {
                            this.showToast(
                                this.t.toast.booking_confirmed || 'Confirmed',
                                (this.t.toast.booking_confirmed_message || 'Success').replace(':reference', data.reference),
                                'success'
                            );
                            
                            this.isSubmitting = false;
                            setTimeout(() => { window.location.reload(); }, 8000); 
                        } else if (data) {
                            this.showToast(this.t.toast.error || 'Error', this.t.toast.something_wrong || 'An unknown error occurred.', 'error');
                            this.isSubmitting = false;
                        }
                    })
                    .catch(error => {
                        if (error !== 'API Validation Error') {
                            console.error('Error:', error);
                            this.showToast(this.t.toast.server_error || 'Error', this.t.toast.network_error || 'Network error.', 'error');
                            this.isSubmitting = false;
                        }
                    });
                }
            };
        });
    });
</script>
@endsection