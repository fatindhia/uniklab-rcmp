<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingAuditLog;
use App\Models\TimeBlock;

class ReportController extends Controller
{
    public function index()
    {
        $pageTitle = 'System Report';

        // Every headline figure is also reported per lab type, so each KPI card
        // can show its research / CSL / pharma split. One grouped query covers
        // status × type; the three date-bound figures get one aggregate each.
        $byStatusType = Booking::selectRaw('status, lab_type, count(*) as total')
            ->groupBy('status', 'lab_type')
            ->get();

        $countsFor = fn (?string $status) => $this->perType(
            $status === null ? $byStatusType : $byStatusType->where('status', $status)
        );

        $totalByType = $countsFor(null);
        $approvedByType = $countsFor('approved');
        $pendingByType = $countsFor('pending');
        $rejectedByType = $countsFor('rejected');
        $cancelledByType = $countsFor('cancelled');

        $blocksByType = $this->perType(
            TimeBlock::selectRaw('lab_type, count(*) as total')->groupBy('lab_type')->get()
        );

        $upcomingByType = $this->perType(
            Booking::where('status', '!=', 'rejected')
                ->whereBetween('booking_date_from', [today(), today()->addDays(14)])
                ->selectRaw('lab_type, count(*) as total')
                ->groupBy('lab_type')
                ->get()
        );

        // A late response is always an approved / rejected / cancelled booking —
        // the decision only landed after the last booked day had passed (the
        // same rule the audit trail flags in red). So it's reported under those
        // outcome cards rather than as a figure of its own.
        $lateByStatus = $this->lateDecisionsByStatus();

        $summary = [
            'total' => array_sum($totalByType),
            'approved' => array_sum($approvedByType),
            'pending' => array_sum($pendingByType),
            'rejected' => array_sum($rejectedByType),
            'cancelled' => array_sum($cancelledByType),
            'active_blocks' => array_sum($blocksByType),
            'upcoming_14_days' => array_sum($upcomingByType),
        ];

        $breakdown = [
            'total' => $totalByType,
            'approved' => $approvedByType,
            'pending' => $pendingByType,
            'rejected' => $rejectedByType,
            'cancelled' => $cancelledByType,
            'active_blocks' => $blocksByType,
            'upcoming_14_days' => $upcomingByType,
        ];

        $responseHealth = $this->responseHealth();

        $recentActivities = BookingAuditLog::with('booking')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn ($log) => [
                'time' => $log->created_at,
                'action' => $log->action,
                'late' => (bool) $log->is_late,
                'title' => ucfirst($log->action).': '.($log->booking?->applicant_name ?? 'Unknown'),
                'meta' => ($log->booking ? ucfirst($log->booking->lab_type).' · '.($log->booking->booking_date_from?->format('d/m/Y') ?? '') : ''),
            ])
            ->concat(
                TimeBlock::latest('created_at')->limit(5)->get()->map(fn ($block) => [
                    'time' => $block->created_at,
                    'action' => 'block',
                    'late' => false,
                    'title' => 'Block: '.$block->title,
                    'meta' => ucfirst($block->lab_type),
                ])
            )
            ->sortByDesc('time')
            ->take(5)
            ->values();

        $monthlyStats = $this->monthlyBreakdown();

        $labUsage = Booking::query()
            ->join('booking_rooms', 'bookings.id', '=', 'booking_rooms.booking_id')
            ->join('labs', 'booking_rooms.lab_id', '=', 'labs.id')
            ->selectRaw('labs.name, count(*) as sessions')
            ->groupBy('labs.id', 'labs.name')
            ->orderByDesc('sessions')
            ->limit(5)
            ->get();

        return view('admin.report', compact('pageTitle', 'summary', 'breakdown', 'lateByStatus', 'responseHealth', 'recentActivities', 'monthlyStats', 'labUsage'));
    }

    /**
     * Which decisions were answered late — the decision landed after the
     * booking's last day had already gone by. Same rule as the audit trail's
     * is_late flag, split per final status *and* lab type so each outcome card
     * can name the labs the late answers came from.
     *
     * @return array<string, array<string, int>>
     */
    private function lateDecisionsByStatus(): array
    {
        $counts = [
            'approved' => ['research' => 0, 'csl' => 0, 'pharma' => 0],
            'rejected' => ['research' => 0, 'csl' => 0, 'pharma' => 0],
            'cancelled' => ['research' => 0, 'csl' => 0, 'pharma' => 0],
        ];

        $rows = Booking::whereNotNull('processed_at')
            ->whereRaw('date(processed_at) > COALESCE(booking_date_to, booking_date_from)')
            ->selectRaw('status, lab_type, count(*) as total')
            ->groupBy('status', 'lab_type')
            ->get();

        foreach ($rows as $row) {
            if (isset($counts[$row->status][$row->lab_type])) {
                $counts[$row->status][$row->lab_type] = (int) $row->total;
            }
        }

        return $counts;
    }

    /**
     * Folds rows carrying a lab_type + total into a research/csl/pharma map,
     * always with all three keys present (0 where a type has no rows) so the
     * cards render a consistent three-line split.
     *
     * @return array<string, int>
     */
    private function perType($rows): array
    {
        $counts = ['research' => 0, 'csl' => 0, 'pharma' => 0];

        foreach ($rows as $row) {
            if (array_key_exists($row->lab_type, $counts)) {
                $counts[$row->lab_type] += (int) $row->total;
            }
        }

        return $counts;
    }

    /**
     * Month-by-month activity: booking outcomes alongside the blocks scheduled
     * that month. Both timelines are merged on the month key, so a month that
     * only has blocks (or only bookings) still gets a column. Blocks are
     * counted one per saved row — a recurring block counts once, on the month
     * of its first date.
     *
     * @return \Illuminate\Support\Collection<string, array<string, int>>
     */
    private function monthlyBreakdown()
    {
        $bookingMonths = Booking::query()
            ->selectRaw("DATE_FORMAT(booking_date_from, '%Y-%m') as month, status, count(*) as total")
            ->groupBy('month', 'status')
            ->get()
            ->groupBy('month');

        $blockMonths = TimeBlock::query()
            ->selectRaw("DATE_FORMAT(block_date, '%Y-%m') as month, count(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        return $bookingMonths->keys()
            ->merge($blockMonths->keys())
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->mapWithKeys(function ($month) use ($bookingMonths, $blockMonths) {
                $rows = $bookingMonths[$month] ?? collect();

                return [$month => [
                    'approved' => (int) ($rows->firstWhere('status', 'approved')->total ?? 0),
                    'pending' => (int) ($rows->firstWhere('status', 'pending')->total ?? 0),
                    'rejected' => (int) ($rows->firstWhere('status', 'rejected')->total ?? 0),
                    'cancelled' => (int) ($rows->firstWhere('status', 'cancelled')->total ?? 0),
                    'blocked' => (int) ($blockMonths[$month] ?? 0),
                ]];
            });
    }

    /**
     * How quickly requests actually get answered, measured against the same
     * "late" rule used everywhere else: a decision counts as late when it
     * landed after the booking's last day had already passed. Decided
     * bookings are counted once each (by their final decision), so this
     * agrees with the per-decision is_late flags in the audit trail.
     */
    private function responseHealth(): array
    {
        $lastDay = 'COALESCE(booking_date_to, booking_date_from)';

        $decided = Booking::whereNotNull('processed_at')
            ->selectRaw("count(*) as decided")
            ->selectRaw("sum(case when date(processed_at) > {$lastDay} then 1 else 0 end) as late")
            ->selectRaw('avg(timestampdiff(hour, submitted_at, processed_at)) as avg_hours')
            ->first();

        $decidedTotal = (int) ($decided->decided ?? 0);
        $late = (int) ($decided->late ?? 0);

        // The request that has been waiting longest without an answer — the
        // one most likely to turn into the next late response.
        $oldestPending = Booking::where('status', 'pending')
            ->orderBy('submitted_at')
            ->first(['ref', 'applicant_name', 'submitted_at']);

        return [
            'decided' => $decidedTotal,
            'late' => $late,
            'on_time' => $decidedTotal - $late,
            'on_time_rate' => $decidedTotal ? round(($decidedTotal - $late) / $decidedTotal * 100) : 0,
            'avg_hours' => $decided->avg_hours !== null ? (float) $decided->avg_hours : null,
            'oldest_pending_days' => $oldestPending?->submitted_at?->diffInDays(now()),
            'oldest_pending_ref' => $oldestPending?->ref,
            'oldest_pending_name' => $oldestPending?->applicant_name,
        ];
    }
}
