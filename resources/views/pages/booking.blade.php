@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#fdfbf7] pb-24 font-sans selection:bg-[#1877c9] selection:text-white" x-data="bookingEngine(@js($routes))">

    {{-- HERO HEADER --}}
    <section class="relative py-16 md:py-20 flex items-center overflow-hidden gradient-navy">
        <div class="absolute inset-0">
            <img src="{{ asset('images/booking-background.png') }}" alt="Sharm El Sheikh" class="w-full h-full object-cover opacity-70" />
        </div>
        <div class="absolute inset-0 bg-gradient-to-b from-navy/80 via-navy/40 to-navy"></div>

        <div class="container-max px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <div class="inline-flex items-center gap-2 badge-gold mb-6 animate-fade-in">
                <span class="w-2 h-2 bg-gold rounded-full animate-pulse"></span>
                Book Your Trip
            </div>
            <h1 class="text-4xl sm:text-5xl font-display font-bold text-white mb-4 animate-slide-up text-balance">
                Reserve Your Seat
            </h1>
            <p class="text-white/80 text-lg max-w-xl mx-auto animate-slide-up">
                Fill in your trip details below — pay just a small deposit to lock in your seat, and settle the rest on board.
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
                            <h2 class="font-extrabold text-navy text-lg leading-tight">Read this first</h2>
                            <p class="text-xs text-navy/50" x-show="!expanded">Tap to expand the full booking instructions</p>
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
                            I have read and agree to the instructions above.
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
                     :style="isStep2Complete ? 'width: 80%' : (isStep1Complete ? 'width: 40%' : 'width: 0%')"></div>

                <div class="relative z-10 bg-white px-2 sm:px-4 flex items-center gap-2 sm:gap-3 rounded-full">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-extrabold transition-all duration-300 shadow-sm"
                         :class="isStep1Complete ? 'bg-[#10b981] text-white ring-4 ring-[#10b981]/20' : 'bg-gold text-navy'">
                        <span x-show="!isStep1Complete">1</span>
                        <svg x-show="isStep1Complete" style="display:none;" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span class="font-extrabold text-sm hidden sm:block transition-colors duration-300" :class="isStep1Complete ? 'text-[#10b981]' : 'text-navy'">Trip details</span>
                </div>

                <div class="relative z-10 bg-white px-2 sm:px-4 flex items-center gap-2 sm:gap-3 rounded-full">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-extrabold transition-all duration-300 shadow-sm"
                         :class="isStep2Complete ? 'bg-[#10b981] text-white ring-4 ring-[#10b981]/20' : (isStep1Complete ? 'bg-gold text-navy' : 'bg-gray-100 text-gray-400 border border-gray-200')">
                        <span x-show="!isStep2Complete">2</span>
                        <svg x-show="isStep2Complete" style="display:none;" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span class="font-extrabold text-sm hidden sm:block transition-colors duration-300" :class="isStep2Complete ? 'text-[#10b981]' : (isStep1Complete ? 'text-navy' : 'text-gray-400')">Your info</span>
                </div>

                <div class="relative z-10 bg-white px-2 sm:px-4 flex items-center gap-2 sm:gap-3 rounded-full">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-extrabold transition-all duration-300 shadow-sm"
                         :class="(isStep1Complete && isStep2Complete) ? 'bg-gold text-navy' : 'bg-gray-100 text-gray-400 border border-gray-200'">
                        3
                    </div>
                    <span class="font-extrabold text-sm hidden sm:block transition-colors duration-300" :class="(isStep1Complete && isStep2Complete) ? 'text-navy' : 'text-gray-400'">Payment</span>
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
                            <div class="text-[10px] font-bold text-[#1877c9] uppercase tracking-wider mb-1">Step 1</div>
                            <h2 class="text-2xl font-extrabold text-navy leading-none mb-1">Trip details</h2>
                            <p class="text-sm text-navy/60">Where and when should we pick you up?</p>
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
                                    <div class="text-[10px] font-bold text-white/70 uppercase tracking-wider mb-2">Trip Setup</div>
                                    <h3 class="text-2xl md:text-3xl font-extrabold mb-2">Choose your ride</h3>
                                    
                                    <div class="flex gap-2 mb-6 bg-white/10 p-1 rounded-full w-max mt-4">
                                        <button type="button" @click="tripType = 'one-way'" :class="tripType === 'one-way' ? 'bg-white text-[#1877c9] shadow-sm' : 'text-white hover:bg-white/10'" class="px-4 py-1.5 rounded-full font-bold text-sm transition-all flex items-center gap-2">
                                            → One way
                                        </button>
                                        <button type="button" @click="tripType = 'round-trip'" :class="tripType === 'round-trip' ? 'bg-white text-[#1877c9] shadow-sm' : 'text-white hover:bg-white/10'" class="px-4 py-1.5 rounded-full font-bold text-sm transition-all flex items-center gap-2">
                                            ⇄ Round trip
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
                                                        <div class="font-extrabold text-sm">Shared Micro Bus</div>
                                                        <div class="text-[10px] opacity-80 font-medium">Best value • Shared ride</div>
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
                                                        <div class="font-extrabold text-sm">Private Car</div>
                                                        <div class="text-[10px] opacity-80 font-medium">Door-to-door • Just for you</div>
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
                            <label class="block text-xs font-bold text-navy mb-2">Route</label>
                            <select x-model="routeId" @change="updateRouteName(); departureDate=''; departureTime=''" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none transition-all duration-200 bg-white text-navy focus:border-[#1877c9] focus:ring-4 focus:ring-[#1877c9]/10 appearance-none cursor-pointer" style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'8\' viewBox=\'0 0 12 8\' fill=\'none\'><path d=\'M1 1l5 5 5-5\' stroke=\'%230A2540\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'/></svg>'); background-repeat: no-repeat; background-position: right 1rem center; padding-right: 2.5rem;">
                                <option value="">Select a route...</option>
                                <template x-for="route in routes" :key="route.id">
                                    <option :value="route.id" x-text="route.name"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-navy mb-2">Date</label>
                            <select x-model="departureDate" @change="departureTime=''" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none transition-all duration-200 bg-white text-navy focus:border-[#1877c9] focus:ring-4 focus:ring-[#1877c9]/10 appearance-none cursor-pointer" style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'8\' viewBox=\'0 0 12 8\' fill=\'none\'><path d=\'M1 1l5 5 5-5\' stroke=\'%230A2540\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'/></svg>'); background-repeat: no-repeat; background-position: right 1rem center; padding-right: 2.5rem;">
                                <option value="">Select an available date...</option>
                                <template x-for="date in availableDates" :key="date.value">
                                    <option :value="date.value" x-text="date.label"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-navy mb-2 flex items-center gap-1">
                                <span class="text-[#1877c9]">⏱</span> Pickup time
                            </label>
                            <select x-model="departureTime" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none transition-all duration-200 bg-white text-navy focus:border-[#1877c9] focus:ring-4 focus:ring-[#1877c9]/10 appearance-none cursor-pointer" style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'8\' viewBox=\'0 0 12 8\' fill=\'none\'><path d=\'M1 1l5 5 5-5\' stroke=\'%230A2540\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'/></svg>'); background-repeat: no-repeat; background-position: right 1rem center; padding-right: 2.5rem;">
                                <option value="">Select time...</option>
                                <template x-if="departureDate && availableTimes">
                                    <template x-for="time in availableTimes">
                                        <option :value="time" x-text="time"></option>
                                    </template>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-navy mb-2">Passengers</label>
                            <div class="flex items-center justify-between border border-gray-200 rounded-xl px-4 py-1.5 bg-transparent">
                                <button type="button" @click="if(passengers > 1) passengers--" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-navy font-bold hover:bg-gray-200 transition-colors">-</button>
                                <div class="flex flex-col items-center justify-center">
                                    <span class="font-extrabold text-navy text-lg leading-none" x-text="passengers"></span>
                                    <span class="text-[8px] font-bold text-[#1877c9] uppercase tracking-widest">Traveler</span>
                                </div>
                                <button type="button" @click="passengers++" class="w-8 h-8 rounded-full bg-gold text-navy flex items-center justify-center font-bold hover:bg-gold-dark transition-colors">+</button>
                            </div>
                        </div>
                    </div>
                </div>

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
                            <div class="text-[10px] font-bold text-[#1877c9] uppercase tracking-wider mb-1">Step 2</div>
                            <h2 class="text-2xl font-extrabold text-navy leading-none mb-1">Your Info</h2>
                            <p class="text-sm text-navy/60">So our driver can find you at the right spot.</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <input type="email" id="email" name="email" autocomplete="email" tabindex="-1" style="position: absolute; left: -9999px; opacity: 0; height: 0; width: 0;" aria-hidden="true">
                            
                            <!-- Name -->
                            <div>
                                <label for="name" class="flex items-center gap-2 text-xs font-bold text-navy mb-2">
                                    <svg class="w-4 h-4 text-[#1877c9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    Full name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="name" name="name" x-model="leadName" autocomplete="name" placeholder="e.g. Anna Ivanova" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none transition-all duration-200 bg-white text-navy focus:border-[#1877c9] focus:ring-4 focus:ring-[#1877c9]/10">
                            </div>
                            
                            <!-- Phone -->
                            <div>
                                <label for="tel" class="flex items-center gap-2 text-xs font-bold text-navy mb-2">
                                    <svg class="w-4 h-4 text-[#1877c9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    WhatsApp number <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" id="tel" name="tel" x-model="whatsapp" autocomplete="tel" placeholder="+20 100 000 0000" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none transition-all duration-200 bg-white text-navy focus:border-[#1877c9] focus:ring-4 focus:ring-[#1877c9]/10">
                            </div>
                            
                            <!-- Nationality -->
                            <div x-data="{ open: false, search: '' }" class="relative" @click.away="open = false">
                                <label class="flex items-center gap-2 text-xs font-bold text-navy mb-2">
                                    <svg class="w-4 h-4 text-[#1877c9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Nationality <span class="text-red-500">*</span>
                                </label>
                                
                                <button type="button" @click="open = !open" 
                                        class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none transition-all duration-200 bg-white text-navy focus:border-[#1877c9] focus:ring-4 focus:ring-[#1877c9]/10 text-left flex justify-between items-center h-[50px]">
                                    <span x-text="nationality ? countries.find(c => c.code === nationality)?.name : 'Select your country'" :class="nationality ? 'text-navy' : 'text-gray-400'"></span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>

                                <div x-show="open" x-transition.opacity class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden" style="display: none;">
                                    <div class="p-2 border-b border-gray-100 bg-gray-50/50">
                                        <div class="relative">
                                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                            <input type="text" x-model="search" placeholder="Search country..." class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#1877c9] focus:ring-1 focus:ring-[#1877c9]/20">
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
                                        <li x-show="!isLoadingCountries && countries.filter(c => c.name.toLowerCase().includes(search.toLowerCase())).length === 0" class="px-3 py-4 text-sm text-gray-400 text-center">No countries found</li>
                                        <li x-show="isLoadingCountries" class="px-3 py-4 text-sm text-gray-400 text-center">Loading nationalities...</li>
                                    </ul>
                                </div>
                            </div>
                            <!-- Room Number -->
                            <div>
                                <label for="room" class="flex items-center gap-2 text-xs font-bold text-navy mb-2">
                                    <svg class="w-4 h-4 text-[#1877c9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    Room number <span class="font-medium text-gray-400 font-normal">(optional)</span>
                                </label>
                                <input type="text" id="room" name="room" x-model="roomNumber" placeholder="e.g. 412" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none transition-all duration-200 bg-white text-navy focus:border-[#1877c9] focus:ring-4 focus:ring-[#1877c9]/10">
                            </div>
                        </div>

                        {{-- Extra Passengers Contact --}}
                        <template x-if="passengers > 1">
                            <div class="mt-5 p-5 bg-[#f0f7ff] rounded-2xl border border-[#e1effe]">
                                <label class="flex items-start gap-3 cursor-pointer select-none mb-3">
                                    <input type="checkbox" x-model="notifyOthers" class="hidden">
                                    <div class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors shrink-0 mt-0.5"
                                        :class="notifyOthers ? 'border-[#1877c9] bg-[#1877c9]' : 'border-gray-300 bg-white'">
                                        <svg class="w-3 h-3 text-white transition-opacity" :class="notifyOthers ? 'opacity-100' : 'opacity-0'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <div>
                                        <span class="font-bold text-navy text-sm block leading-tight">Notify other passengers about this booking</span>
                                        <span class="text-[10px] text-gray-500 font-medium mt-1 block">We'll send them the trip details, pickup time, and driver info.</span>
                                    </div>
                                </label>
                                
                                <div x-show="notifyOthers" x-transition>
                                    <div class="space-y-3 mt-3 pl-8">
                                        <template x-for="(phone, index) in extraWhatsAppNumbers" :key="index">
                                            <div class="flex items-center gap-2 animate-fade-in">
                                                <div class="relative flex-1">
                                                    <svg class="w-4 h-4 text-[#1877c9] absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                                    <input type="tel" x-model="phone.value" placeholder="Passenger WhatsApp (e.g. +20 100...)" class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 outline-none transition-all focus:border-[#1877c9] focus:ring-2 focus:ring-[#1877c9]/20 text-sm">
                                                </div>
                                                <button type="button" @click="extraWhatsAppNumbers.splice(index, 1)" class="p-2.5 text-red-500 hover:bg-red-50 rounded-xl transition-colors shrink-0">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        </template>
                                        
                                        <button type="button" x-show="extraWhatsAppNumbers.length < (passengers - 1)" @click="extraWhatsAppNumbers.push({value: ''})" class="text-xs font-bold text-[#1877c9] hover:text-[#1360a0] flex items-center gap-1 mt-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                            Add another passenger
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Hotel Map --}}
                        <div class="w-full">
                            <label class="flex items-center gap-2 text-xs font-bold text-navy mb-2">
                                <svg class="w-4 h-4 text-[#1877c9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Hotel / pickup location <span class="text-red-500">*</span>
                            </label>
                            <div class="bg-[#f8f9fa] rounded-xl border border-gray-200 hover:border-[#1877c9] transition-colors" @hotel-selected.window="hotel = $event.detail.name">
                                <x-booking.hotel-picker />
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="w-full">
                            <label for="notes" class="flex items-center gap-2 text-xs font-bold text-navy mb-2">
                                <svg class="w-4 h-4 text-[#1877c9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Notes for the driver <span class="font-medium text-gray-400 font-normal">(optional)</span>
                            </label>
                            <textarea id="notes" name="notes" x-model="notes" rows="2" placeholder="Extra luggage, child seat..." class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none transition-all duration-200 bg-white text-navy focus:border-[#1877c9] focus:ring-4 focus:ring-[#1877c9]/10 resize-none h-[70px]"></textarea>
                        </div>
                    </div>
                </div>

                <!-- STEP 3 CARD: PAYMENT METHOD -->
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-5 md:p-6 mt-6 relative" :class="(isStep1Complete && isStep2Complete) ? 'ring-2 ring-[#1877c9]/50' : ''">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-[#f0f7ff] text-[#1877c9] flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-[#1877c9] uppercase tracking-wider mb-1">Step 3</div>
                            <h2 class="text-2xl font-extrabold text-navy leading-none mb-1">Payment method</h2>
                            <p class="text-sm text-navy/60">Reserve your seat now - pay the rest when you board.</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: SUMMARY CARD -->
            <div class="lg:col-span-4">
                <div class="bg-[#1b3a5a] rounded-[2rem] p-6 md:p-8 sticky top-28 shadow-2xl text-white">

                    <div class="flex justify-between items-start mb-6">
                        <div class="text-[10px] font-bold text-gold uppercase tracking-wider">Booking Summary</div>
                        <div class="bg-white/10 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider" x-text="tripType.replace('-', ' ')"></div>
                    </div>

                    <h3 class="text-2xl font-extrabold mb-1" x-text="routeName || 'Select Route'"></h3>
                    <p class="text-sm text-white/70 font-medium mb-6" x-text="vehicleType === 'private' ? 'Private Sedan' : 'Shared Micro Bus'"></p>

                    <div class="space-y-4 border-t border-white/10 pt-6">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-white/70">Outbound</span>
                            <span class="font-bold text-right">
                                <span x-text="departureDate ? departureDate : 'Select Date'"></span>
                                <span x-show="departureTime" x-text="' • ' + departureTime"></span>
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-white/70">Passengers</span>
                            <span class="font-bold" x-text="passengers"></span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-white/70">Pickup point</span>
                            <span class="font-bold text-right" x-text="hotel ? hotel : 'Not selected'"></span>
                        </div>
                        
                        <!-- Applied Offers Section -->
                        <template x-if="pricing.appliedOffers.length > 0">
                            <div class="mt-4 p-3 bg-green-500/10 rounded-xl border border-green-500/20 animate-fade-in">
                                <span class="block text-[10px] font-bold text-green-400 uppercase tracking-wider mb-2">Offers Applied</span>
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
                            <span class="text-white/70 text-sm mb-1">Total Fare</span>
                            <div class="text-right">
                                <template x-if="pricing.hasDiscount">
                                    <span class="text-white/50 line-through text-sm mr-2" x-text="'$' + pricing.originalTotal"></span>
                                </template>
                                <span class="font-extrabold text-2xl" x-text="'$' + pricing.total"></span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-white/70 text-sm">Deposit due now</span>
                            <div class="text-right">
                                <template x-if="pricing.hasDiscount && parseFloat(pricing.originalDeposit) > parseFloat(pricing.deposit)">
                                    <span class="text-white/50 line-through text-xs mr-2" x-text="'$' + pricing.originalDeposit"></span>
                                </template>
                                <span class="font-extrabold text-gold text-lg" x-text="'$' + pricing.deposit"></span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-white/70 text-sm">Balance on board</span>
                            <span class="font-bold text-lg text-white" x-text="'$' + pricing.balance"></span>
                        </div>
                    </div>

                    <!-- Enforced Validation Disable + isSubmitting logic -->
                    <div x-show="!pricing.isValid && isStep1Complete" class="text-red-400 text-xs font-bold text-center mb-3">
                        Pricing is not currently configured for this selection. Please try a different option.
                    </div>
                    <button type="submit" :disabled="!agreedToTerms || !isStep1Complete || !isStep2Complete || isSubmitting || !pricing.isValid" :class="(!agreedToTerms || !isStep1Complete || !isStep2Complete || isSubmitting || !pricing.isValid) ? 'opacity-50 cursor-not-allowed' : 'hover:-translate-y-0.5 hover:shadow-lg'" class="w-full bg-[#48c4d8] text-white rounded-full py-4 font-extrabold text-sm transition-all flex flex-col items-center justify-center gap-1 shadow-md active:scale-[0.98]">
                        <span x-show="!isSubmitting">Confirm Booking — Pay $<span x-text="pricing.deposit"></span></span>
                        <span x-show="isSubmitting" style="display: none;">Processing Booking...</span>
                        <span class="text-[9px] font-medium opacity-80 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            Secured by Wasla • SSL encrypted
                        </span>
                    </button>

                </div>
            </div>

        </form>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('bookingEngine', (dbRoutes) => {
            
            const urlParams = new URLSearchParams(window.location.search);
            const initialRoute = urlParams.get('tour') || '';
            const initialDate = urlParams.get('date') || '';

            return {
                agreedToTerms: false,
                isSubmitting: false,
                tripType: 'one-way',
                vehicleType: 'microbus',
                
                routeId: '',
                routeName: '',
                departureDate: '', 
                departureTime: '',
                passengers: 1,

                leadName: '{{ auth()->check() ? auth()->user()->name : '' }}',
                whatsapp: '{{ auth()->check() ? auth()->user()->phone : '' }}',
                nationality: '',
                roomNumber: '',
                notes: '',
                hotel: '',

                notifyOthers: false,
                extraWhatsAppNumbers: [], 
                routes: dbRoutes,
                countries: [], 
                isLoadingCountries: true,

                init() {
                    // Delay the selection using $nextTick so the <option> HTML renders first!
                    this.$nextTick(() => {
                        if (initialRoute) {
                            this.routeId = initialRoute;
                            this.updateRouteName();
                            
                            this.$nextTick(() => {
                                // Check if a date was provided and is valid
                                if (initialDate && this.availableDates.find(d => d.value === initialDate)) {
                                    this.departureDate = initialDate;
                                    
                                    this.$nextTick(() => {
                                        // Auto-select the first available time
                                        if (this.availableTimes && this.availableTimes.length > 0) {
                                            this.departureTime = this.availableTimes[0];
                                        }
                                    });
                                }
                            });
                        }
                    });

                    // Watch passengers to manage extra WhatsApp inputs
                    this.$watch('passengers', (val) => {
                        if (val === 1) {
                            this.notifyOthers = false;
                            this.extraWhatsAppNumbers = [];
                        } else if (this.extraWhatsAppNumbers.length >= val) {
                            this.extraWhatsAppNumbers.splice(val - 1);
                        }
                    });

                    // ==========================================
                    // ENTERPRISE REGION GENERATOR (Intl API)
                    // ==========================================
                    try {
                        const regionNames = new Intl.DisplayNames(['en'], { type: 'region' });
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

                        this.countries = uniqueCountries.sort((a, b) => a.name.localeCompare(b.name));
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
                    return this.routes.find(r => r.id === this.routeId) || null;
                },

                get availableDates() {
                    if (!this.selectedRouteData) return [];
                    return Object.keys(this.selectedRouteData.schedule).map(dateStr => {
                        const d = new Date(dateStr);
                        return {
                            value: dateStr,
                            label: d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })
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
                           this.hotel.trim() !== '';
                },

                updateRouteName() {
                    this.routeName = this.selectedRouteData ? this.selectedRouteData.name : '';
                },

                get pricing() {
                    const emptyPricing = { total: '0.00', deposit: '0.00', balance: '0.00', originalTotal: '0.00', originalDeposit: '0.00', hasDiscount: false, appliedOffers: [], isValid: false };
                    
                    if (!this.selectedRouteData) return emptyPricing;
                    
                    const type = this.tripType === 'round-trip' ? 'round_trip' : 'one_way';
                    const vehicle = this.vehicleType;
                    
                    let availableTiers = this.selectedRouteData.tiers.filter(t => t.type === type && t.vehicle === vehicle);
                    
                    // Fixed Bug: Removed strict restriction for pax === 1 so private cars can locate their base tier correctly!
                    const baseTier = availableTiers.find(t => t.is_base);
                    let offers = availableTiers.filter(t => !t.is_base).sort((a, b) => b.pax - a.pax);

                    if (!baseTier && offers.length === 0) {
                        return emptyPricing; // Cannot book without pricing tiers
                    }

                    let total = 0;
                    let deposit = 0;
                    let remainingPax = this.passengers;
                    let appliedOffers = [];
                    let originalTotal = 0;
                    let originalDeposit = 0;

                    if (vehicle === 'private') {
                        // 1. PRIVATE CAR PRICING
                        let pTier = offers.length > 0 ? offers[0] : baseTier; 
                        let capacity = pTier.pax > 0 ? pTier.pax : 3;
                        let carsNeeded = Math.ceil(remainingPax / capacity);
                        
                        total = carsNeeded * pTier.price;
                        deposit = carsNeeded * pTier.deposit;

                        // Base original price on baseTier if it exists (as requested), otherwise use the offer itself
                        let comparisonTier = baseTier ? baseTier : pTier;
                        originalTotal = carsNeeded * comparisonTier.price;
                        originalDeposit = carsNeeded * comparisonTier.deposit;
                        
                        if (originalTotal > total && pTier.name) {
                            let pct = Math.round(((originalTotal - total) / originalTotal) * 100);
                            appliedOffers.push(`${pTier.name} (${pct}% Off)`);
                        }
                    } else {
                        // 2. MICROBUS PRICING
                        for (let offer of offers) {
                            if (remainingPax <= 0) break;
                            if (remainingPax >= offer.pax) {
                                let multiplier = Math.floor(remainingPax / offer.pax);
                                
                                total += offer.price * multiplier;
                                deposit += offer.deposit * multiplier;
                                remainingPax -= offer.pax * multiplier;
                                
                                if (offer.discount > 0 && offer.name) {
                                    appliedOffers.push(`${offer.name} (${offer.discount}% Off Fare)`);
                                }
                            }
                        }
                        if (remainingPax > 0) {
                            if (baseTier) {
                                total += remainingPax * baseTier.price;
                                deposit += remainingPax * baseTier.deposit;
                            } else {
                                // If no base tier exists to cover the remaining passengers, it is unbookable
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
                        isValid: true // Flags that a valid pricing tier was successfully found
                    };
                },

                submitBooking(event) {
                    if (!this.agreedToTerms) {
                        alert('Please check "I have read and agree to the instructions above" to proceed.');
                        return;
                    }

                    const payload = {
                        routeId: this.routeId,
                        departureDate: this.departureDate,
                        departureTime: this.departureTime,
                        passengers: this.passengers,
                        leadName: this.leadName,
                        whatsapp: this.whatsapp,
                        nationality: this.nationality,
                        roomNumber: this.roomNumber,
                        hotel: this.hotel,
                        notes: this.notes,
                        tripType: this.tripType,
                        vehicleType: this.vehicleType,
                        extraWhatsApp: this.extraWhatsAppNumbers.map(n => n.value).filter(val => val !== '')
                    };

                    this.isSubmitting = true;

                    fetch('{{ route('booking.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = data.redirect_url;
                        } else {
                            alert('Something went wrong. Please try again.');
                            this.isSubmitting = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('A server error occurred. Please check your inputs.');
                        this.isSubmitting = false;
                    });
                }
            };
        });
    });
</script>
@endsection