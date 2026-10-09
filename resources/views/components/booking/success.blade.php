@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-16 flex justify-center">
    <div class="max-w-md w-full bg-white shadow-xl rounded-2xl p-8 text-center border-t-4 border-green-500">
        
        <!-- Green Checkmark Icon -->
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        
        <h2 class="text-3xl font-extrabold text-gray-900 mb-3">Booking Confirmed!</h2>
        <p class="text-gray-600 mb-8">{{ $message ?? 'Your deposit was paid successfully. We look forward to your trip!' }}</p>

        <!-- Booking Details Card -->
        <div class="bg-gray-50 rounded-xl p-5 mb-8 text-left border border-gray-100">
            <p class="text-sm text-gray-500 uppercase tracking-wider mb-1">Booking Reference</p>
            <p class="text-2xl font-bold text-gray-800">{{ $booking->booking_reference }}</p>
        </div>

        <a href="{{ route('home') }}" class="block w-full bg-blue-600 text-white font-bold text-lg py-4 rounded-xl hover:bg-blue-700 transition shadow-md">
            Return to Homepage
        </a>
    </div>
</div>
@endsection