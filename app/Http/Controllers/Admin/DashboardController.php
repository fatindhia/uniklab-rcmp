<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\TimeBlock;
use App\Support\BookingCalendar;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $pageTitle = 'Dashboard';
        $today = today();

        // One grouped query across both dimensions instead of seven identical-
        // shape COUNT()s — it carries every headline figure *and* the
        // research / CSL / pharma split each KPI card shows.
        $byStatusType = Booking::selectRaw('status, lab_type, count(*) as total')
            ->groupBy('status', 'lab_type')
            ->get();
        $blocksByType = $this->perType(
            TimeBlock::selectRaw('lab_type, count(*) as total')->groupBy('lab_type')->get()
        );

        $breakdown = [
            'total' => $this->perType($byStatusType),
            'pending' => $this->perType($byStatusType->where('status', 'pending')),
            'approved' => $this->perType($byStatusType->where('status', 'approved')),
            'rejected' => $this->perType($byStatusType->where('status', 'rejected')),
            'cancelled' => $this->perType($byStatusType->where('status', 'cancelled')),
            'blocked' => $blocksByType,
        ];

        $stats = [
            'total' => array_sum($breakdown['total']),
            'pending' => array_sum($breakdown['pending']),
            'approved' => array_sum($breakdown['approved']),
            'rejected' => array_sum($breakdown['rejected']),
            'cancelled' => array_sum($breakdown['cancelled']),
            'research' => $breakdown['total']['research'],
            'csl' => $breakdown['total']['csl'],
            'pharma' => $breakdown['total']['pharma'],
            'blocked' => array_sum($blocksByType),
        ];

        // Pending approvals — the admin's primary action queue.
        $pendingBookings = Booking::query()
            ->with(['rooms.lab', 'equipment', 'students', 'auditLogs.performedBy', 'processedBy'])
            ->where('status', 'pending')
            ->orderBy('submitted_at')
            ->limit(8)
            ->get();

        // Sessions taking place today (single-day or multi-day ranges covering today).
        $todayBookings = Booking::query()
            ->with('rooms.lab')
            ->whereIn('status', ['approved', 'pending'])
            ->whereDate('booking_date_from', '<=', $today)
            ->whereRaw('COALESCE(booking_date_to, booking_date_from) >= ?', [$today->toDateString()])
            ->orderBy('start_time')
            ->get();

        // Most recent submissions across every status.
        $recentBookings = Booking::query()
            ->with('rooms.lab')
            ->orderByDesc('submitted_at')
            ->limit(6)
            ->get();

        $upcomingBlocks = TimeBlock::query()
            ->whereDate('block_date', '>=', $today)
            ->orderBy('block_date')
            ->limit(6)
            ->get();

        $calendarEvents = BookingCalendar::events();
        $todayBlocks = collect($calendarEvents[$today->format('Y-m-d')]['blocks'] ?? []);

        return view('admin.dashboard', compact(
            'pageTitle', 'stats', 'breakdown', 'today',
            'pendingBookings', 'todayBookings', 'todayBlocks',
            'recentBookings', 'upcomingBlocks', 'calendarEvents'
        ));
    }

    /**
     * Folds rows carrying a lab_type + total into a research/csl/pharma map,
     * always with all three keys present (0 where a type has no rows) so the
     * KPI cards render a consistent three-line split.
     *
     * @return array<string, int>
     */
    private function perType($rows): array
    {
        $counts = ['research' => 0, 'csl' => 0, 'pharma' => 0];

        foreach ($rows as $row) {
            if (\array_key_exists($row->lab_type, $counts)) {
                $counts[$row->lab_type] += (int) $row->total;
            }
        }

        return $counts;
    }

    public function calendar(Request $request)
    {
        $pageTitle = 'Calendar View';

        $labType = in_array($request->query('type'), ['research', 'csl', 'pharma'], true)
            ? $request->query('type')
            : null;

        $bookings = Booking::query()
            ->when($labType, fn ($q) => $q->where('lab_type', $labType))
            ->get();

        $blocks = TimeBlock::query()
            ->when($labType, fn ($q) => $q->where('lab_type', $labType))
            ->get();

        $stats = [
            'total' => $bookings->count(),
            'approved' => $bookings->where('status', 'approved')->count(),
            'pending' => $bookings->where('status', 'pending')->count(),
            'blocked' => $blocks->count(),
        ];

        $calendarEvents = BookingCalendar::events($labType);

        return view('admin.calendar', compact('pageTitle', 'labType', 'stats', 'calendarEvents'));
    }
}
