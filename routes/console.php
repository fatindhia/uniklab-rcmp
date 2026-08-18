<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Hourly rather than daily so a booking submitted late — already inside its own
// 24-hour window — still gets chased within the hour. Each booking is reminded
// once; bookings.reminder_sent_at is what keeps the repeat runs quiet.
Schedule::command('bookings:remind-pending')
    ->hourly()
    ->withoutOverlapping();
