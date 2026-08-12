<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BookingStatusUpdated;
use App\Mail\RoomReassigned;
use App\Models\Booking;
use App\Models\BookingAuditLog;
use App\Models\BookingRoom;
use App\Models\Lab;
use App\Support\BookingCalendar;
use App\Support\BookingModalPayload;
use App\Support\RoomAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    /**
     * Columns the bookings table can be sorted by, keyed by the value used in
     * the ?sort= query string.
     */
    protected const SORTABLE = [
        'ref' => 'ref',
        'applicant' => 'applicant_name',
        'type' => 'lab_type',
        'date' => 'booking_date_from',
        'status' => 'status',
    ];

    /**
     * One (status, lab_type) grouped query replaces ten identical-shape
     * COUNT()s — every number below is derived from the same result set.
     */
    protected function stats(): array
    {
        $rows = Booking::query()
            ->selectRaw('status, lab_type, count(*) as total')
            ->groupBy('status', 'lab_type')
            ->get();

        $byStatus = $rows->groupBy('status')->map->sum('total');
        $byLabType = $rows->groupBy('lab_type')->map->sum('total');
        $pendingByLabType = $rows->where('status', 'pending')->pluck('total', 'lab_type');

        return [
            'total' => (int) $rows->sum('total'),
            'approved' => (int) ($byStatus['approved'] ?? 0),
            'pending' => (int) ($byStatus['pending'] ?? 0),
            'rejected' => (int) ($byStatus['rejected'] ?? 0),
            'research' => (int) ($byLabType['research'] ?? 0),
            'csl' => (int) ($byLabType['csl'] ?? 0),
            'pharma' => (int) ($byLabType['pharma'] ?? 0),
            'pending_research' => (int) ($pendingByLabType['research'] ?? 0),
            'pending_csl' => (int) ($pendingByLabType['csl'] ?? 0),
            'pending_pharma' => (int) ($pendingByLabType['pharma'] ?? 0),
        ];
    }

    protected function withRelations()
    {
        return Booking::query()->with(['rooms.lab', 'equipment', 'students', 'auditLogs.performedBy', 'processedBy']);
    }

    /**
     * The bookings list only surfaces what still needs attention: every pending
     * request, plus approved bookings whose date hasn't passed yet. Past
     * approved, rejected and cancelled bookings live in History instead.
     */
    protected function activeBookings()
    {
        return $this->withRelations()->where(function ($q) {
            $q->where('status', 'pending')
                ->orWhere(function ($q2) {
                    $q2->where('status', 'approved')
                        ->whereRaw('COALESCE(booking_date_to, booking_date_from) >= ?', [today()->toDateString()]);
                });
        });
    }

    /**
     * Apply the header-driven sort (defaulting to soonest booking date first),
     * always tie-breaking on start time so same-day rows stay chronological.
     */
    protected function applySort($query, Request $request)
    {
        $sort = $request->query('sort', 'date');
        $column = self::SORTABLE[$sort] ?? 'booking_date_from';
        $dir = $request->query('dir') === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($column, $dir)->orderBy('start_time');
    }

    public function index(Request $request)
    {
        $bookings = $this->applySort($this->activeBookings(), $request)
            ->paginate(20)->appends($request->except('open'));

        return view('admin.bookings.index', [
            'pageTitle' => 'All Bookings',
            'tab' => 'all',
            'bookings' => $bookings,
            'stats' => $this->stats(),
            'showType' => true,
            'openBooking' => $this->resolveOpenBooking($request),
        ]);
    }

    public function pending(Request $request)
    {
        return $this->index($request);
    }

    public function research(Request $request)
    {
        return $this->byType('research', 'Research Labs', $request);
    }

    public function csl(Request $request)
    {
        return $this->byType('csl', 'CSL Labs', $request);
    }

    public function pharma(Request $request)
    {
        return $this->byType('pharma', 'Pharma Labs', $request);
    }

    protected function byType(string $labType, string $pageTitle, Request $request)
    {
        $bookings = $this->applySort($this->activeBookings()->where('lab_type', $labType), $request)
            ->paginate(20)->appends($request->except('open'));

        return view('admin.bookings.index', [
            'pageTitle' => $pageTitle,
            'tab' => $labType,
            'bookings' => $bookings,
            'stats' => $this->stats(),
            'showType' => false,
            'openBooking' => $this->resolveOpenBooking($request),
        ]);
    }

    /**
     * Resolves ?open=REF (used by the "new booking ticket" admin email's
     * deep link) to a booking with the modal's full relations loaded — the
     * target might not be on the current paginated page/tab, so the table's
     * own BOOKING_MAP (built only from that page) won't have it; the view
     * adds this one in separately and auto-opens its modal.
     */
    private function resolveOpenBooking(Request $request): ?Booking
    {
        $ref = $request->query('open');

        if (! $ref) {
            return null;
        }

        return $this->withRelations()->where('ref', $ref)->first();
    }

    public function update(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'admin_remark' => ['required', 'string'],
        ]);

        // A room reassigned while still pending doesn't get its own email
        // (see reassignRoom()) — fold that into this decision email instead
        // of sending the applicant two messages back to back.
        $roomReassignedFrom = $booking->status === 'pending' ? $this->pendingReassignmentFrom($booking) : null;
        $isLate = $this->isLateResponse($booking);

        $booking->update([
            'status' => $data['status'],
            'admin_remark' => $data['admin_remark'],
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        BookingAuditLog::create([
            'booking_id' => $booking->id,
            'action' => $data['status'],
            'performed_by' => auth()->id(),
            'detail' => $data['admin_remark'],
            'is_late' => $isLate,
        ]);

        BookingCalendar::flush();

        try {
            Mail::to($booking->applicant_email)->send(new BookingStatusUpdated($booking, $data['status'], $roomReassignedFrom));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('status', "Booking {$booking->ref} marked as {$data['status']}.");
    }

    public function cancel(Request $request, Booking $booking)
    {
        if ($booking->status === 'cancelled') {
            return back()->withErrors(['booking' => 'This booking is already cancelled.']);
        }

        $data = $request->validate([
            'reason' => ['required', 'string'],
        ]);

        // Same fold-in as update() — only relevant when cancelling straight
        // from pending (skipping approve/reject); a reassignment on an
        // already-approved booking already got its own immediate email.
        $roomReassignedFrom = $booking->status === 'pending' ? $this->pendingReassignmentFrom($booking) : null;
        $isLate = $this->isLateResponse($booking);

        $booking->update([
            'status' => 'cancelled',
            'admin_remark' => $data['reason'],
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        BookingAuditLog::create([
            'booking_id' => $booking->id,
            'action' => 'cancelled',
            'performed_by' => auth()->id(),
            'detail' => $data['reason'],
            'is_late' => $isLate,
        ]);

        BookingCalendar::flush();

        try {
            Mail::to($booking->applicant_email)->send(new BookingStatusUpdated($booking, 'cancelled', $roomReassignedFrom));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('status', "Booking {$booking->ref} cancelled.");
    }

    /**
     * True when the admin is only now answering a request whose booking date
     * has already gone by — i.e. it sat pending past the day it was for. Call
     * this *before* the status update, since it reads the pre-decision status.
     * The resulting flag is stamped on the audit log entry, which renders it
     * in red as a "Late response".
     */
    private function isLateResponse(Booking $booking): bool
    {
        if ($booking->status !== 'pending') {
            return false;
        }

        $lastDay = $booking->booking_date_to ?? $booking->booking_date_from;

        return $lastDay && $lastDay->lt(today());
    }

    /**
     * The original room name (before any reassignment) if this booking was
     * reassigned at least once while still pending — that reassignment is
     * deliberately not emailed on its own (see reassignRoom()), only ever
     * folded into the decision email that takes the booking out of
     * "pending". Only meaningful to call while the booking is still pending:
     * past that point, every reassignment already got its own immediate
     * email, so there's nothing left to fold in. Multiple reassignments
     * before a decision collapse to "original room → current room", skipping
     * any intermediate rooms — the applicant only cares about the net change.
     */
    private function pendingReassignmentFrom(Booking $booking): ?string
    {
        $first = $booking->auditLogs()->where('action', 'reassigned')->oldest()->first();

        if (! $first || ! preg_match('/^Changed from "(.*)" to ".*"$/', $first->detail, $matches)) {
            return null;
        }

        return $matches[1];
    }

    /**
     * Live Available / Not Available list for the room-reassign picker, using
     * the booking's own date and time.
     */
    public function roomAvailability(Booking $booking)
    {
        $booking->load('rooms');

        return response()->json(['rooms' => RoomAvailability::forBooking($booking)]);
    }

    /**
     * Reassigns the booking to a different set of rooms. CSL bookings can hold
     * several rooms at once, so the whole selection is replaced (first pick
     * becomes primary); other lab types stay single-room, since their rooms
     * carry equipment selections that a wholesale swap would orphan.
     *
     * Availability is re-checked here even though the picker already greys out
     * taken rooms — the list can go stale while the modal sits open.
     */
    public function reassignRoom(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'lab_ids' => ['required', 'array', 'min:1'],
            'lab_ids.*' => ['integer', 'exists:labs,id'],
        ]);

        $labIds = collect($data['lab_ids'])->map(fn ($id) => (int) $id)->unique()->values();
        $fail = fn (string $message) => $request->wantsJson()
            ? response()->json(['message' => $message], 422)
            : back()->withErrors(['lab_ids' => $message]);

        if ($booking->lab_type !== 'csl' && $labIds->count() > 1) {
            return $fail('Only CSL bookings can be reassigned to more than one room.');
        }

        $labs = Lab::whereIn('id', $labIds)->get();

        if ($labs->count() !== $labIds->count() || $labs->contains(fn (Lab $lab) => $lab->lab_type !== $booking->lab_type || $lab->status !== 'active')) {
            return $fail('Select active rooms of the same lab type as this booking.');
        }

        $currentLabIds = $booking->rooms->pluck('lab_id')->map(fn ($id) => (int) $id)->sort()->values();

        if ($currentLabIds->all() === $labIds->sort()->values()->all()) {
            return $fail('Select at least one different room.');
        }

        $unavailable = RoomAvailability::reasons(
            $labs,
            $booking->booking_date_from->format('Y-m-d'),
            $booking->booking_date_to->format('Y-m-d'),
            \Illuminate\Support\Carbon::parse($booking->start_time)->format('H:i'),
            \Illuminate\Support\Carbon::parse($booking->end_time)->format('H:i'),
            $booking->id,
            $booking->lab_type === 'csl' ? (int) config('booking.csl.buffer_minutes') : 0,
        );

        if ($unavailable) {
            $names = $labs->whereIn('id', array_keys($unavailable))
                ->map(fn (Lab $lab) => $lab->name.' — '.$unavailable[$lab->id])
                ->implode('; ');

            return $fail('Not available: '.$names.'.');
        }

        $oldLabName = $booking->rooms->sortByDesc('is_primary')->map(fn ($room) => $room->lab?->name)->filter()->implode(', ');
        $newLabName = $labs->sortBy(fn (Lab $lab) => $labIds->search($lab->id))->pluck('name')->implode(', ');

        DB::transaction(function () use ($booking, $labIds) {
            $booking->rooms()->delete();

            foreach ($labIds as $index => $labId) {
                BookingRoom::create([
                    'booking_id' => $booking->id,
                    'lab_id' => $labId,
                    'is_primary' => $index === 0,
                ]);
            }
        });

        BookingAuditLog::create([
            'booking_id' => $booking->id,
            'action' => 'reassigned',
            'performed_by' => auth()->id(),
            'detail' => "Changed from \"{$oldLabName}\" to \"{$newLabName}\"",
        ]);

        BookingCalendar::flush();

        // Only fire the standalone "room reassigned" email for a booking
        // that's already been decided — a pending booking's reassignment
        // gets folded into the approve/reject/cancel email instead (see
        // hasUnnotifiedReassignment()), so the applicant isn't emailed twice
        // in the same admin session.
        if ($booking->status !== 'pending') {
            try {
                Mail::to($booking->applicant_email)->send(new RoomReassigned($booking, $oldLabName ?: 'Unknown room', $newLabName));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        if ($request->wantsJson()) {
            // Reload so the response reflects the new lab_id/building instead
            // of whatever was cached on $booking's relations before the update
            // — the modal stays open after a reassign (see _modal.blade.php),
            // so its redraw needs fresh data, not a page reload.
            $booking->load(['rooms.lab', 'equipment', 'students', 'auditLogs.performedBy', 'processedBy']);

            return response()->json([
                'status' => $labIds->count() > 1
                    ? "Booking reassigned to {$newLabName}."
                    : "Room reassigned to {$newLabName}.",
                'booking' => BookingModalPayload::build($booking),
            ]);
        }

        return back()->with('status', "Room reassigned to {$newLabName}.");
    }
}
