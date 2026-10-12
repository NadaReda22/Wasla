<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DriverManifestController;




// =========================================================
// 1. Static Pages
// =========================================================
Route::view('/about', 'pages.about')->name('about');
Route::view('/policies', 'pages.policies')->name('policies');
Route::get('/instructions', [HomeController::class, 'instructions'])->name('instructions');
Route::view('/terms', 'pages.terms')->name('terms');
Route::view('/refund-policy', 'pages.refund-policy')->name('refund-policy');
Route::view('/privacy-policy', 'pages.privacy')->name('privacy');

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
Route::get('/book/active-hotels', [BookingController::class, 'getActiveHotels'])->name('booking.hotels');

// =========================================================
// 4. Payment Checkout Placeholder
// =========================================================
// Redirect target for tourists after paying on Kashier
Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');

// Server-to-server webhook notification 
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');

// =========================================================
// 5. Show Driver Manifest Page
// =========================================================
Route::get('/manifest/{vehicle}/{date}', [DriverManifestController::class, 'show'])
    ->name('driver.manifest')
    ->middleware('signed');

// =========================================================
// 6. Translation
// =========================================================
Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'ru'])) {
        session(['locale' => $locale]);
    }
    return back();
})->name('lang.switch');



Route::get('/admin/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar'])) {
        Session::put('filament_locale', $locale);
    }
    return redirect()->back();
})->name('admin.lang.switch');


