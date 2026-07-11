<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;

// Pages that don't need a lot of dynamic logic
Route::view('/about', 'pages.about')->name('about');
Route::view('/faq', 'pages.faq')->name('faq');
Route::view('/policies', 'pages.policies')->name('policies');

// Pages requiring DB data (Handled by Controllers)
Route::get('/', [PageController::class, 'landing'])->name('home');
Route::get('/trip/{id}', [PageController::class, 'tripDetail'])->name('trip.detail');

Route::get('/booking/{tripId?}', [BookingController::class, 'create'])->name('booking.create');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');