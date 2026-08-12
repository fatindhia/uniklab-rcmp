<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Lab;
use App\Models\TimeBlock;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        $stats = [
            'bookings' => Booking::count(),
            'pending' => Booking::where('status', 'pending')->count(),
            'active_labs' => Lab::where('status', 'active')->count(),
            'upcoming_blocks' => TimeBlock::whereDate('block_date', '>=', today())->count(),
        ];

        $bookingsByType = Booking::query()
            ->selectRaw('lab_type, count(*) as total')
            ->groupBy('lab_type')
            ->pluck('total', 'lab_type');

        $recentBookings = Booking::query()
            ->with(['rooms.lab', 'equipment'])
            ->orderByDesc('submitted_at')
            ->limit(6)
            ->get();

        $activeLabs = Lab::query()
            ->with(['equipment' => fn ($query) => $query->orderBy('sort_order')])
            ->where('status', 'active')
            ->orderBy('lab_type')
            ->orderBy('name')
            ->limit(6)
            ->get();

        return view('home', compact('stats', 'bookingsByType', 'recentBookings', 'activeLabs'));
    }
}