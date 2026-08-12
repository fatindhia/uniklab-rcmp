<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
Route::get('/bookings/{booking:ref}', [BookingController::class, 'show'])->name('bookings.show');
Route::get('/check-booking', [BookingController::class, 'lookup'])->name('bookings.lookup');

Route::get('/my-bookings', function () {
    return redirect()->route('bookings.index');
});
