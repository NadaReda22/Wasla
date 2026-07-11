@extends('layouts.app')

@section('content')
<div 
    x-data="bookingForm(@js($trips), '{{ request()->route('tripId') }}')" 
    class="min-h-screen pt-24 pb-16 bg-sand-light"
>
    <div x-show="showInstructions && !instructionsAccepted" class="fixed inset-0 z-50 pt-20 pb-16 bg-sand-light/95 flex items-center justify-center px-4 overflow-y-auto">
        <div class="max-w-2xl w-full">
            <div class="card p-8 animate-scale-in mt-10">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-gold/10 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gold-dark" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-navy mb-2">Booking Instructions</h2>
                    <p class="text-navy/60">Please read these instructions carefully before proceeding</p>
                </div>

                <div class="space-y-4 mb-6">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold text-sm">1</div>
                        <div>
                            <h4 class="font-semibold text-navy text-sm">Flat Rate Pricing</h4>
                            <p class="text-navy/60 text-sm mt-0.5 leading-relaxed">Every route costs $4 flat. You pay $1 as a deposit online and the remaining $3 in cash on the bus.</p>
                        </div>
                    </div>
                    </div>

                <div class="flex gap-3">
                    <button @click="instructionsAccepted = true; showInstructions = false;" class="btn-primary flex-1 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        I have read and understood — Proceed to Booking
                    </button>
                    <a href="{{ url('/') }}" class="btn-ghost">Cancel</a>
                </div>
            </div>
        </div>
    </div>

    <div x-show="instructionsAccepted" style="display:none;" class="container-max px-4 sm:px-6 lg:px-8">
        <div class="mb-8 text-center">
            <span class="badge-primary mb-3 inline-block">Book Your Trip</span>
            <h1 class="text-3xl sm:text-4xl font-display font-bold text-navy">
                Reserve Your Seat Now
            </h1>
            <p class="text-navy/60 mt-2">Fill in your details below and pay $1 deposit to secure your seat</p>
        </div>

        <form action="{{ route('booking.store') }}" method="POST" class="max-w-5xl mx-auto">
            @csrf
            
            <input type="hidden" name="trip_type" :value="form.trip_type">
            <input type="hidden" name="payment_method" :value="paymentMethod">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="card p-6">
                        <h3 class="font-bold text-lg text-navy mb-4 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-sm">1</span>
                            Select Your Trip
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="label">Trip *</label>
                                <select name="trip_id" x-model="form.trip_id" @change="updateSelectedTrip" class="input" required>
                                    <option value="" disabled>Select a trip...</option>
                                    <template x-for="trip in trips" :key="trip.id">
                                        <option :value="trip.id" x-text="trip.title + ' — $' + trip.flat_price + ' flat'"></option>
                                    </template>
                                </select>
                            </div>

                            <template x-if="selectedTrip">
                                <div class="bg-sand-light rounded-xl p-4 flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-navy/50">Flat rate per passenger</p>
                                        <p class="font-bold text-primary text-xl" x-text="'$' + selectedTrip.flat_price"></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-navy/50">Deposit online</p>
                                        <p class="font-bold text-coral text-xl">$1</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-navy/50">Cash on bus</p>
                                        <p class="font-bold text-turquoise text-xl" x-text="'$' + (selectedTrip.flat_price - 1).toFixed(2)"></p>
                                    </div>
                                </div>
                            </template>
                            
                            <div>
                                <label class="label">Trip Type *</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <button type="button" @click="form.trip_type = 'one_way'" :class="form.trip_type === 'one_way' ? 'border-primary bg-primary/5 text-primary' : 'border-gray-200 text-navy/60'" class="p-3 rounded-xl border-2 transition-all font-semibold">One Way</button>
                                    <button type="button" @click="form.trip_type = 'round_trip'" :class="form.trip_type === 'round_trip' ? 'border-gold bg-gold/5 text-gold-dark' : 'border-gray-200 text-navy/60'" class="p-3 rounded-xl border-2 transition-all">
                                        <span class="font-semibold block">Round Trip</span>
                                        <span class="text-xs mt-0.5 font-normal block">Save 25%</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card p-6">
                        <h3 class="font-bold text-lg text-navy mb-4 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-sm">2</span>
                            Passenger Details
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div><label class="label">Full Name *</label><input type="text" name="passenger_name" class="input" required></div>
                            <div><label class="label">WhatsApp Number *</label><input type="tel" name="passenger_phone" class="input" required></div>
                            <div><label class="label">Country</label><input type="text" name="country" x-model="form.country" class="input"></div>
                            <div><label class="label">Number of Passengers *</label><input type="number" name="passenger_count" x-model="form.passenger_count" min="1" max="10" class="input" required></div>
                        </div>
                    </div>

                    <div class="card p-6">
                        <h3 class="font-bold text-lg text-navy mb-4 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-sm">3</span>
                            Trip Details
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="label">Departure Date *</label>
                                <input type="date" name="departure_date" x-model="form.departure_date" class="input" required :min="new Date().toISOString().split('T')[0]">
                            </div>
                            <div>
                                <label class="label">Departure Time *</label>
                                <select name="departure_time" class="input" required>
                                    <template x-for="time in (selectedTrip ? selectedTrip.departure_times : [])" :key="time">
                                        <option :value="time" x-text="time"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="label">Special Notes</label>
                                <textarea name="notes" class="input min-h-[80px] resize-y"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card p-6">
                        <h3 class="font-bold text-lg text-navy mb-4 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-sm">4</span>
                            Payment Method
                        </h3>
                        
                        <template x-if="isRestrictedCountry">
                            <div class="bg-coral/10 border border-coral/30 rounded-xl p-4 mb-4 flex items-start gap-3">
                                <svg class="w-5 h-5 text-coral flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
                                <div>
                                    <p class="font-semibold text-coral-dark text-sm">Cash payment available for your country</p>
                                    <p class="text-coral-dark/70 text-xs mt-1">Due to banking restrictions, card payment is disabled. You can pay the full amount in cash on the bus.</p>
                                </div>
                            </div>
                        </template>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <button type="button" @click="form.payment_method = 'card'" :disabled="isRestrictedCountry" :class="(paymentMethod === 'card' && !isRestrictedCountry) ? 'border-primary bg-primary/5' : (isRestrictedCountry ? 'border-gray-200 opacity-50 cursor-not-allowed' : 'border-gray-200 hover:border-primary/30')" class="p-4 rounded-xl border-2 transition-all text-left">
                                <p class="font-semibold text-navy">Card / Fawry</p>
                                <p class="text-xs text-navy/50" x-text="isRestrictedCountry ? 'Not available' : 'Pay $' + depositTotal.toFixed(2) + ' deposit online'"></p>
                            </button>
                            <button type="button" @click="form.payment_method = 'cash'" :class="paymentMethod === 'cash' ? 'border-turquoise bg-turquoise/5' : 'border-gray-200'" class="p-4 rounded-xl border-2 transition-all text-left">
                                <p class="font-semibold text-navy">Cash on Bus</p>
                                <p class="text-xs text-navy/50" x-text="'Pay $' + totalAmount.toFixed(2) + ' to driver'"></p>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="card p-6 sticky top-24">
                        <h3 class="font-bold text-lg text-navy mb-4">Booking Summary</h3>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-navy/60">Deposit (online)</span>
                                <span class="font-bold text-coral" x-text="'$' + depositTotal.toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-navy/60">Cash on bus</span>
                                <span class="font-bold text-turquoise" x-text="'$' + remainingAmount.toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between items-center pt-3 border-t border-navy/10">
                                <span class="font-bold text-navy">Total</span>
                                <span class="text-3xl font-bold text-primary" x-text="'$' + totalAmount.toFixed(2)"></span>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-coral w-full text-lg py-4 flex justify-center items-center gap-2">
                            <span x-text="isRestrictedCountry ? 'Confirm Booking' : 'Pay $' + depositTotal.toFixed(2) + ' Deposit & Book'"></span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('bookingForm', (initialTrips, urlTripId) => ({
            trips: initialTrips,
            selectedTrip: null,
            showInstructions: true,
            instructionsAccepted: false,
            restrictedCountries: ['russia', 'iran', 'north korea', 'syria', 'cuba', 'belarus', 'venezuela'],
            
            form: {
                trip_id: urlTripId || '',
                country: '',
                passenger_count: 1,
                trip_type: 'one_way',
                payment_method: 'card',
                departure_date: ''
            },

            init() {
                if (this.form.trip_id) {
                    this.updateSelectedTrip();
                } else if (this.trips.length > 0) {
                    this.form.trip_id = this.trips[0].id;
                    this.updateSelectedTrip();
                }
            },

            updateSelectedTrip() {
                this.selectedTrip = this.trips.find(t => t.id == this.form.trip_id) || null;
            },

            get isRestrictedCountry() {
                const lower = this.form.country.toLowerCase();
                return this.restrictedCountries.some(c => lower.includes(c));
            },

            get paymentMethod() {
                return this.isRestrictedCountry ? 'cash' : this.form.payment_method;
            },

            get totalAmount() {
                if(!this.selectedTrip) return 4.00;
                let amount = parseFloat(this.selectedTrip.flat_price) * this.form.passenger_count;
                return this.form.trip_type === 'round_trip' ? amount * 1.75 : amount; // Applying 25% discount for round trips on full fare
            },

            get depositTotal() {
                let deposit = 1.00 * this.form.passenger_count;
                return this.form.trip_type === 'round_trip' ? deposit * 2 : deposit; 
            },

            get remainingAmount() {
                return this.paymentMethod === 'card' ? (this.totalAmount - this.depositTotal) : this.totalAmount;
            }
        }));
    });
</script>
@endsection