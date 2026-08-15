<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BookingController;

// =========================================================
// 1. Static Pages
// =========================================================
Route::view('/about', 'pages.about')->name('about');
Route::view('/policies', 'pages.policies')->name('policies');
Route::view('/instructions', 'pages.instructions')->name('instructions');

// =========================================================
// 2. Dynamic Pages (Handled by HomeController)
// =========================================================
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/faq', [HomeController::class, 'faq'])->name('faq');

// =========================================================
// 3. Booking Engine Flow
// =========================================================
Route::get('/book', [BookingController::class, 'create'])->name('booking.create');
Route::post('/book', [BookingController::class, 'store'])->name('booking.store');

// =========================================================
// 4. Payment Checkout Placeholder
// =========================================================
Route::get('/checkout/{reference}', function($reference) {
    return "Redirecting to payment gateway for $reference...";
})->name('payment.checkout');