<?php

use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\HistoryController as AdminHistoryController;
use App\Http\Controllers\Admin\LabController as AdminLabController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Admin\TimeBlockController as AdminTimeBlockController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::post('/booking/availability', [BookingController::class, 'checkAvailability'])->name('booking.availability');
Route::post('/booking/pharma-labs', [BookingController::class, 'pharmaEligibleLabs'])->name('booking.pharma-labs');
Route::post('/booking/equipment-availability', [BookingController::class, 'equipmentAvailability'])->name('booking.equipment-availability');
Route::get('/bookings/{booking:ref}', [BookingController::class, 'show'])->name('bookings.show');
Route::get('/check-booking', [BookingController::class, 'lookup'])->name('bookings.lookup');

Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/admin/login/quick', [AuthController::class, 'quickLogin'])->name('login.quick');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('logout');

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/calendar', [AdminDashboardController::class, 'calendar'])->name('calendar');

    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/pending', [AdminBookingController::class, 'pending'])->name('bookings.pending');
    Route::get('/bookings/research', [AdminBookingController::class, 'research'])->name('bookings.research');
    Route::get('/bookings/csl', [AdminBookingController::class, 'csl'])->name('bookings.csl');
    Route::get('/bookings/pharma', [AdminBookingController::class, 'pharma'])->name('bookings.pharma');
    Route::patch('/bookings/{booking:ref}', [AdminBookingController::class, 'update'])->name('bookings.update');
    Route::get('/bookings/{booking:ref}/room-availability', [AdminBookingController::class, 'roomAvailability'])->name('bookings.room-availability');
    Route::patch('/bookings/{booking:ref}/reassign', [AdminBookingController::class, 'reassignRoom'])->name('bookings.reassign');
    Route::patch('/bookings/{booking:ref}/cancel', [AdminBookingController::class, 'cancel'])->name('bookings.cancel');

    Route::get('/labs', [AdminLabController::class, 'index'])->name('labs.index');
    Route::post('/labs', [AdminLabController::class, 'store'])->name('labs.store');
    Route::patch('/labs/{lab}', [AdminLabController::class, 'update'])->name('labs.update');
    Route::delete('/labs/{lab}', [AdminLabController::class, 'destroy'])->name('labs.destroy');

    Route::get('/schedule-block', [AdminTimeBlockController::class, 'index'])->name('time-blocks.index');
    Route::post('/schedule-block', [AdminTimeBlockController::class, 'store'])->name('time-blocks.store');
    Route::delete('/schedule-block/{timeBlock}', [AdminTimeBlockController::class, 'destroy'])->name('time-blocks.destroy');

    // Admin and above only — plain lab staff get every other page but not
    // these two.
    Route::middleware('admin')->group(function () {
        Route::get('/staff', [AdminStaffController::class, 'index'])->name('staff.index');
        Route::post('/staff', [AdminStaffController::class, 'store'])->name('staff.store');
        Route::patch('/staff/{user}', [AdminStaffController::class, 'update'])->name('staff.update');

        Route::get('/report', [AdminReportController::class, 'index'])->name('report');
    });

    Route::middleware('super_admin')->group(function () {
        Route::get('/activity-log', [AdminActivityLogController::class, 'index'])->name('activity-log');

        Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::patch('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    });

    Route::get('/history', [AdminHistoryController::class, 'index'])->name('history');
});
