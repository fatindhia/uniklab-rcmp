<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Lab;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function create()
    {
        $labsByType = Lab::query()
            ->with(['equipment' => fn ($query) => $query->orderBy('sort_order')])
            ->where('status', 'active')
            ->orderBy('lab_type')
            ->orderBy('name')
            ->get()
            ->groupBy('lab_type');

        return view('bookings.create', compact('labsByType'));
    }

    public function index()
    {
        $bookings = Booking::query()
            ->with(['rooms.lab', 'equipment', 'processedBy'])
            ->orderByDesc('submitted_at')
            ->paginate(10);

        return view('bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['rooms.lab.equipment', 'equipment', 'students', 'processedBy', 'applicantUser']);

        return view('bookings.show', compact('booking'));
    }

    public function lookup(Request $request)
    {
        $ref = trim((string) $request->query('ref', ''));
        $booking = null;

        if ($ref !== '') {
            $booking = Booking::query()
                ->with(['rooms.lab', 'equipment'])
                ->where('ref', $ref)
                ->first();
        }

        return view('bookings.lookup', compact('ref', 'booking'));
    }
}