@extends('layouts.app')

@section('content')
<div x-data="{ activeTab: 'overview', filterStatus: 'all', showTripForm: false }" class="min-h-screen pt-20 bg-sand-light">
    <div class="container-max px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-display font-bold text-navy mb-2">Dashboard</h1>
            <p class="text-navy/60">Manage trips and bookings</p>
        </div>

        <div class="flex gap-2 mb-6 bg-white p-1.5 rounded-xl shadow-sm overflow-x-auto scrollbar-hide">
            <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'bg-primary text-white shadow-md' : 'text-navy/60 hover:bg-sand-light'" class="flex items-center gap-2 px-5 py-3 rounded-lg font-semibold transition-all whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                Overview
            </button>
            <button @click="activeTab = 'trips'" :class="activeTab === 'trips' ? 'bg-primary text-white shadow-md' : 'text-navy/60 hover:bg-sand-light'" class="flex items-center gap-2 px-5 py-3 rounded-lg font-semibold transition-all whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16c0 .88.39 1.67 1 2.22V20a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-2c0-.55.45-1 1-1h1zm16 0c.55 0 1 .45 1 1v2a1 1 0 0 1-1 1h-1a1 1 0 0 1-1-1v-1.78c.61-.55 1-1.34 1-2.22h1zM12 2C7.58 2 4 5.58 4 10v6h16v-6c0-4.42-3.58-8-8-8z" /></svg>
                Trips
            </button>
            <button @click="activeTab = 'bookings'" :class="activeTab === 'bookings' ? 'bg-primary text-white shadow-md' : 'text-navy/60 hover:bg-sand-light'" class="flex items-center gap-2 px-5 py-3 rounded-lg font-semibold transition-all whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                Bookings
            </button>
        </div>

        <div x-show="activeTab === 'overview'" class="space-y-6 animate-fade-in" style="display: none;">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="card p-5">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16c0 .88.39 1.67 1 2.22V20a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-2c0-.55.45-1 1-1h1zm16 0c.55 0 1 .45 1 1v2a1 1 0 0 1-1 1h-1a1 1 0 0 1-1-1v-1.78c.61-.55 1-1.34 1-2.22h1zM12 2C7.58 2 4 5.58 4 10v6h16v-6c0-4.42-3.58-8-8-8z" /></svg>
                    </div>
                    <p class="text-3xl font-bold text-navy">{{ $stats['totalTrips'] }}</p>
                    <p class="text-navy/50 text-sm mt-1">Total Trips</p>
                </div>
                <div class="card p-5">
                    <div class="w-12 h-12 rounded-xl bg-turquoise/10 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-turquoise" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    </div>
                    <p class="text-3xl font-bold text-navy">{{ $stats['totalBookings'] }}</p>
                    <p class="text-navy/50 text-sm mt-1">Total Bookings</p>
                </div>
                <div class="card p-5">
                    <div class="w-12 h-12 rounded-xl bg-gold/10 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-gold" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.66 0-3 .9-3 2s1.34 2 3 2 3 .9 3 2-1.34 2-3 2m0-8c1.11 0 2.08.4 2.83 1.17" /></svg>
                    </div>
                    <p class="text-3xl font-bold text-navy">${{ number_format($stats['totalDeposits'], 2) }}</p>
                    <p class="text-navy/50 text-sm mt-1">Deposits Collected</p>
                </div>
                <div class="card p-5">
                    <div class="w-12 h-12 rounded-xl bg-coral/10 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-coral" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h16a3 3 0 003-3V8a3 3 0 00-3-3H5a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                    </div>
                    <p class="text-3xl font-bold text-navy">${{ number_format($stats['totalRevenue'], 2) }}</p>
                    <p class="text-navy/50 text-sm mt-1">Total Revenue</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="card p-6">
                    <h3 class="font-bold text-navy mb-4">Payment Methods</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between mb-1.5">
                                <span class="text-sm font-semibold text-navy">Card (Deposit)</span>
                                <span class="text-sm text-navy/60">{{ $stats['cardBookings'] }} bookings</span>
                            </div>
                            <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-primary rounded-full transition-all" style="width: {{ $stats['totalBookings'] ? ($stats['cardBookings'] / $stats['totalBookings']) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1.5">
                                <span class="text-sm font-semibold text-navy">Cash</span>
                                <span class="text-sm text-navy/60">{{ $stats['cashBookings'] }} bookings</span>
                            </div>
                            <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-turquoise rounded-full transition-all" style="width: {{ $stats['totalBookings'] ? ($stats['cashBookings'] / $stats['totalBookings']) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card p-6">
                    <h3 class="font-bold text-navy mb-4">Booking Status</h3>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="text-center p-4 bg-gold/10 rounded-xl">
                            <p class="text-2xl font-bold text-gold-dark">{{ $stats['pendingBookings'] }}</p>
                            <p class="text-xs text-navy/60 mt-1">Pending</p>
                        </div>
                        <div class="text-center p-4 bg-turquoise/10 rounded-xl">
                            <p class="text-2xl font-bold text-turquoise-dark">{{ $stats['confirmedBookings'] }}</p>
                            <p class="text-xs text-navy/60 mt-1">Confirmed</p>
                        </div>
                        <div class="text-center p-4 bg-primary/10 rounded-xl">
                            <p class="text-2xl font-bold text-primary">{{ $stats['totalBookings'] }}</p>
                            <p class="text-xs text-navy/60 mt-1">Total</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-6">
                <h3 class="font-bold text-navy mb-4">Recent Bookings</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-navy/50 text-left border-b border-navy/10">
                                <th class="pb-3 font-semibold">Ref</th>
                                <th class="pb-3 font-semibold">Name</th>
                                <th class="pb-3 font-semibold">Trip</th>
                                <th class="pb-3 font-semibold">Date</th>
                                <th class="pb-3 font-semibold">Deposit</th>
                                <th class="pb-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentBookings as $booking)
                                <tr class="border-b border-navy/5">
                                    <td class="py-3 font-mono text-primary text-xs">{{ $booking->booking_reference }}</td>
                                    <td class="py-3 font-semibold text-navy">{{ $booking->passenger_name }}</td>
                                    <td class="py-3 text-navy/60">{{ $booking->trip->title ?? '-' }}</td>
                                    <td class="py-3 text-navy/60">{{ $booking->departure_date }}</td>
                                    <td class="py-3 font-semibold text-coral">${{ $booking->deposit_amount ?? 0 }}</td>
                                    <td class="py-3">
                                        <span class="badge text-xs {{ $booking->status === 'pending' ? 'badge-gold' : ($booking->status === 'confirmed' ? 'badge-turquoise' : 'badge-primary') }}">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'trips'" class="animate-fade-in" style="display: none;">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-navy">Manage Trips</h2>
                <button @click="showTripForm = true" class="btn-primary flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Add Trip
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($trips as $trip)
                    <div class="card p-5">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="font-bold text-navy">{{ $trip->title }}</h3>
                                <p class="text-xs text-navy/50 mt-1">{{ $trip->origin }} → {{ $trip->destination }}</p>
                            </div>
                            <span class="badge {{ $trip->available ? 'badge-turquoise' : 'bg-gray-100 text-navy/40' }}">
                                {{ $trip->available ? 'Active' : 'Stopped' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-navy/60 mb-4">
                            <span class="font-bold text-primary">${{ $trip->flat_price ?? 4 }}</span>
                            <span>•</span>
                            <span>${{ $trip->deposit_amount ?? 1 }} deposit</span>
                            <span>•</span>
                            <span>{{ $trip->capacity }} seats</span>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.trips.edit', $trip->id) }}" class="flex-1 btn-ghost text-center text-sm py-2">Edit</a>
                            
                            <form action="{{ route('admin.trips.toggle', $trip->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full btn-ghost text-sm py-2">
                                    {{ $trip->available ? 'Stop' : 'Activate' }}
                                </button>
                            </form>

                            <form action="{{ route('admin.trips.destroy', $trip->id) }}" method="POST" onsubmit="return confirm('Delete this trip?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-3 py-2 rounded-lg text-coral hover:bg-coral/10 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div x-show="activeTab === 'bookings'" class="animate-fade-in" style="display: none;">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <h2 class="text-xl font-bold text-navy">Manage Bookings</h2>
                <div class="flex gap-2 flex-wrap">
                    @foreach(['all', 'pending', 'confirmed', 'completed', 'cancelled'] as $status)
                        <button @click="filterStatus = '{{ $status }}'" :class="filterStatus === '{{ $status }}' ? 'bg-primary text-white' : 'bg-white text-navy/60 hover:bg-sand-light'" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all capitalize">
                            {{ $status === 'all' ? 'All' : $status }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-sand-light">
                            <tr class="text-navy/60 text-left">
                                <th class="px-4 py-3 font-semibold">Ref</th>
                                <th class="px-4 py-3 font-semibold">Name</th>
                                <th class="px-4 py-3 font-semibold">Phone</th>
                                <th class="px-4 py-3 font-semibold">Trip</th>
                                <th class="px-4 py-3 font-semibold">Date</th>
                                <th class="px-4 py-3 font-semibold">Total</th>
                                <th class="px-4 py-3 font-semibold">Payment</th>
                                <th class="px-4 py-3 font-semibold">Status</th>
                                <th class="px-4 py-3 font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bookings as $booking)
                                <tr x-show="filterStatus === 'all' || filterStatus === '{{ $booking->status }}'" class="border-b border-navy/5 hover:bg-sand-light/50">
                                    <td class="px-4 py-3 font-mono text-primary text-xs">{{ $booking->booking_reference }}</td>
                                    <td class="px-4 py-3 font-semibold text-navy">{{ $booking->passenger_name }}</td>
                                    <td class="px-4 py-3 text-navy/60 text-xs">{{ $booking->passenger_phone }}</td>
                                    <td class="px-4 py-3 text-navy/60 text-xs">{{ $booking->trip->title ?? '-' }}</td>
                                    <td class="px-4 py-3 text-navy/60 text-xs">{{ $booking->departure_date }}</td>
                                    <td class="px-4 py-3 font-semibold text-navy">${{ $booking->total_price }}</td>
                                    <td class="px-4 py-3">
                                        <span class="badge text-xs {{ $booking->payment_method === 'cash' ? 'badge-turquoise' : 'badge-primary' }}">
                                            {{ ucfirst($booking->payment_method) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge text-xs {{ $booking->status === 'pending' ? 'badge-gold' : ($booking->status === 'confirmed' ? 'badge-turquoise' : 'badge-primary') }}">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <form action="{{ route('admin.bookings.updateStatus', $booking->id) }}" method="POST">
                                            @csrf
                                            <select name="status" onchange="this.form.submit()" class="text-xs px-2 py-1.5 rounded-lg border border-gray-200 focus:border-primary outline-none">
                                                <option value="pending" {{ $booking->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="confirmed" {{ $booking->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                <option value="completed" {{ $booking->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center py-12 text-navy/40">No bookings found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <template x-if="showTripForm">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy/50 backdrop-blur-sm animate-fade-in">
                <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="sticky top-0 bg-white border-b border-gray-100 p-5 flex items-center justify-between">
                        <h3 class="text-xl font-bold text-navy">Add New Trip</h3>
                        <button @click="showTripForm = false" class="p-2 rounded-lg hover:bg-sand-light">
                            <svg class="w-5 h-5 text-navy/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form action="{{ route('admin.trips.store') }}" method="POST" class="p-5 space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="label">Trip Title *</label>
                                <input type="text" name="title" class="input" required />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="label">Description</label>
                                <textarea name="description" class="input min-h-[80px]"></textarea>
                            </div>
                            <div>
                                <label class="label">Flat Price ($) *</label>
                                <input type="number" step="0.01" name="flat_price" class="input" required />
                            </div>
                            </div>
                        <div class="flex gap-3 pt-2">
                            <button type="submit" class="btn-primary flex-1">Save Trip</button>
                            <button type="button" @click="showTripForm = false" class="btn-ghost">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

    </div>
</div>
@endsection