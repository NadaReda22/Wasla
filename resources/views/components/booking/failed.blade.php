@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-16 flex justify-center">
    <div class="max-w-md w-full bg-white shadow-xl rounded-2xl p-8 text-center border-t-4 border-red-500">
        
        <!-- Red Cross Icon -->
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </div>
        
        <h2 class="text-3xl font-extrabold text-gray-900 mb-3">Payment Failed</h2>
        <p class="text-gray-600 mb-8">{{ $error ?? 'Your bank declined the transaction or it was canceled.' }}</p>

        <div class="bg-gray-50 rounded-xl p-5 mb-8 text-left border border-gray-100">
            <p class="text-sm text-gray-500 uppercase tracking-wider mb-1">Booking Reference</p>
            <p class="text-xl font-bold text-gray-800">{{ $booking->booking_reference }}</p>
        </div>

        <!-- Optional: If you have a route to retry payment, link it here -->
        <a href="{{ route('booking.create') }}" class="block w-full bg-gray-800 text-white font-bold text-lg py-4 rounded-xl hover:bg-gray-900 transition shadow-md mb-4">
            Try Booking Again
        </a>
        
        <p class="text-sm text-gray-500">
            If you continue to have issues, please contact your bank or try using a different credit card.
        </p>
    </div>
</div>
@endsection