<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\Lab;
use App\Models\TimeBlock;
use Illuminate\Support\Collection;

/**
 * Is a room free for a given slot? Used by the admin room-reassign screen to
 * mark rooms Available / Not Available before an admin can pick one, and to
 * re-check the choice server-side on submit.
 *
 * A room is unavailable when another (non-rejected/cancelled) booking overlaps
 * the slot, when an admin time block covers it, or — for CSL, which requires a
 * turnaround gap between sessions in the same room — when another booking sits
 * inside that buffer window.
 */
class RoomAvailability
{
    /**
     * Why each of the given rooms can't take the slot, keyed by lab id.
     * Rooms that are free simply don't appear.
     *
     * @param  Collection<int, Lab>  $labs
     * @return array<int, string>
     */
    public static function reasons(
        Collection $labs,
        string $dateFrom,
        string $dateTo,
        string $startTime,
        string $endTime,
        ?int $ignoreBookingId = null,
        int $bufferMinutes = 0,
        bool $continuous = false
    ): array {
        if ($labs->isEmpty()) {
            return [];
        }

        $labIds = $labs->pluck('id');
        $reasons = [];

        // The slot as absolute intervals. A continuous run is one span that
        // crosses midnight, so it can't be matched by comparing a date range
        // and a time range separately — SQL narrows the candidates by date and
        // BookingSpan decides the rest.
        $intervals = BookingSpan::intervals($dateFrom, $dateTo, $startTime, $endTime, $continuous);
        $spanFrom = $intervals ? $intervals[0][0]->copy()->subMinutes($bufferMinutes)->toDateString() : $dateFrom;
        $spanTo = $intervals ? $intervals[array_key_last($intervals)][1]->copy()->addMinutes($bufferMinutes)->toDateString() : $dateTo;

        $candidates = BookingRoom::query()
            ->whereIn('lab_id', $labIds)
            ->whereHas('booking', function ($query) use ($spanFrom, $spanTo, $ignoreBookingId) {
                $query->whereNotIn('status', ['rejected', 'cancelled'])
                    ->where('booking_date_from', '<=', $spanTo)
                    ->where('booking_date_to', '>=', $spanFrom);

                if ($ignoreBookingId) {
                    $query->where('id', '!=', $ignoreBookingId);
                }
            })
            ->with('booking:id,ref,status,booking_date_from,booking_date_to,start_time,end_time,is_continuous')
            ->get()
            ->filter(fn ($row) => $row->booking !== null);

        foreach ($candidates as $row) {
            if (BookingSpan::overlaps($intervals, BookingSpan::fromBooking($row->booking))) {
                $reasons[$row->lab_id] ??= 'Booked by '.($row->booking->ref ?? 'another booking').' at this time';
            }
        }

        // CSL rooms need a gap between sessions — a booking that merely sits
        // inside the buffer window (not overlapping) still rules the room out.
        if ($bufferMinutes > 0) {
            foreach ($candidates as $row) {
                if (BookingSpan::overlaps($intervals, BookingSpan::fromBooking($row->booking), $bufferMinutes)) {
                    $reasons[$row->lab_id] ??= 'Another booking is within the '.$bufferMinutes.'-minute buffer';
                }
            }
        }

        // Admin time blocks reserve a room by name, and can repeat weekly or
        // biweekly — a block counts if any of its occurrences lands in range.
        $blocks = TimeBlock::query()
            ->whereDate('block_date', '>=', \Carbon\Carbon::parse($spanFrom)->subDays(42))
            ->whereDate('block_date', '<=', $spanTo)
            ->get()
            ->filter(function (TimeBlock $block) use ($intervals, $spanFrom, $spanTo) {
                foreach (BookingCalendar::recurDates($block->block_date, $block->recurring) as $date) {
                    if ($date < $spanFrom || $date > $spanTo) {
                        continue;
                    }

                    $blockIntervals = BookingSpan::intervals(
                        $date,
                        $date,
                        BookingSpan::timeLabel($block->start_time),
                        BookingSpan::timeLabel($block->end_time),
                        false,
                    );

                    if (BookingSpan::overlaps($intervals, $blockIntervals)) {
                        return true;
                    }
                }

                return false;
            });

        $labsByName = $labs->keyBy('name');

        foreach ($blocks as $block) {
            foreach (($block->rooms ?? []) as $roomName) {
                $lab = $labsByName->get($roomName);

                if ($lab) {
                    $reasons[$lab->id] ??= 'Blocked by admin: '.$block->title;
                }
            }
        }

        return $reasons;
    }

    /**
     * Every active room of the booking's lab type, each marked available or
     * not for that booking's own date and time (the booking's current rooms
     * are ignored, so keeping a room it already holds stays possible).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function forBooking(Booking $booking): array
    {
        $labs = Lab::query()
            ->where('lab_type', $booking->lab_type)
            ->where('status', 'active')
            ->orderBy('id')
            ->get();

        $reasons = self::reasons(
            $labs,
            $booking->booking_date_from->format('Y-m-d'),
            $booking->booking_date_to->format('Y-m-d'),
            \Carbon\Carbon::parse($booking->start_time)->format('H:i'),
            \Carbon\Carbon::parse($booking->end_time)->format('H:i'),
            $booking->id,
            $booking->lab_type === 'csl' ? (int) config('booking.csl.buffer_minutes') : 0,
            (bool) $booking->is_continuous,
        );

        $assigned = $booking->rooms->pluck('lab_id')->all();

        return $labs->map(fn (Lab $lab) => [
            'id' => $lab->id,
            'name' => $lab->name,
            'location' => $lab->location,
            'assigned' => in_array($lab->id, $assigned, true),
            'available' => ! isset($reasons[$lab->id]),
            'reason' => $reasons[$lab->id] ?? null,
        ])->values()->all();
    }
}
