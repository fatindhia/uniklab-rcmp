<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use App\Support\BookingCalendar;

class HomeController extends Controller
{
    public function index()
    {
        $labCounts = Lab::query()
            ->where('status', 'active')
            ->selectRaw('lab_type, count(*) as total')
            ->groupBy('lab_type')
            ->pluck('total', 'lab_type');

        $calendarEvents = BookingCalendar::events();

        return view('home', compact(
            'labCounts',
            'calendarEvents',
        ));
    }
}
