<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\TimeBlock;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class BookingCalendar
{
    /**
     * Every ?type= variant events() is ever called with — used to know which
     * cache entries flush() needs to clear.
     */
    private const LAB_TYPES = [null, 'research', 'csl', 'pharma'];

    private const CACHE_TTL_SECONDS = 300;

    /**
     * Date-keyed map of bookings/blocks, mirroring the legacy admin mockup's
     * ALL_BOOKINGS/ALL_BLOCKS-derived event structure.
     *
     * This is called on nearly every page (public home, admin dashboard,
     * admin calendar, schedule-block) and does a full table scan + eager
     * load + per-day PHP expansion every time — expensive and, since none of
     * those pages depend on each other's timing, almost always recomputing
     * an answer some other request already produced seconds ago. Cached for
     * 5 minutes and explicitly flushed by every write path that can change
     * the result (booking store/update/cancel/reassign, time block
     * store/destroy — see flush()), so admins still see changes immediately;
     * the TTL is only a safety net.
     */
    public static function events(?string $labType = null): array
    {
        return Cache::remember(static::cacheKey($labType), self::CACHE_TTL_SECONDS, fn () => static::computeEvents($labType));
    }

    /**
     * Clears every cached events() variant — call after any write that
     * changes booking status/rooms or time blocks.
     */
    public static function flush(): void
    {
        foreach (self::LAB_TYPES as $labType) {
            Cache::forget(static::cacheKey($labType));
        }
    }

    private static function cacheKey(?string $labType): string
    {
        return 'booking_calendar_events_'.($labType ?? 'all');
    }

    private static function computeEvents(?string $labType): array
    {
        // The calendar only surfaces live bookings — pending (awaiting a
        // decision) and approved (confirmed). Rejected/cancelled ones are not
        // real occupancy, so they never appear on any calendar.
        $bookings = Booking::query()
            ->select(['id', 'ref', 'applicant_name', 'purpose', 'lab_type', 'status', 'booking_date_from', 'booking_date_to', 'start_time', 'end_time'])
            ->with('rooms.lab:id,name')
            ->whereIn('status', ['pending', 'approved'])
            ->when($labType, fn ($q) => $q->where('lab_type', $labType))
            ->get();

        $blocks = TimeBlock::query()
            ->when($labType, fn ($q) => $q->where('lab_type', $labType))
            ->get();

        $events = [];

        foreach ($bookings as $booking) {
            $from = $booking->booking_date_from;
            if (! $from) {
                continue;
            }

            // Multi-day (extended) bookings occupy every day in their range, so
            // they appear on each of those days in the calendar — not just day
            // one. The guard caps runaway loops from malformed data.
            $to = $booking->booking_date_to && $booking->booking_date_to->gte($from)
                ? $booking->booking_date_to
                : $from;

            $event = [
                'ref' => $booking->ref,
                'name' => $booking->applicant_name,
                'subject' => $booking->purpose,
                'type' => $booking->lab_type,
                'status' => $booking->status,
                'start' => $booking->start_time->format('H:i'),
                'end' => $booking->end_time->format('H:i'),
                'rooms' => $booking->rooms->map(fn ($r) => $r->lab?->name)->filter()->implode(', '),
            ];

            for ($day = $from->copy(), $guard = 0; $day->lte($to) && $guard < 366; $day->addDay(), $guard++) {
                $events[$day->format('Y-m-d')]['bookings'][] = $event;
            }
        }

        foreach ($blocks as $block) {
            foreach (static::recurDates($block->block_date, $block->recurring) as $date) {
                $events[$date]['blocks'][] = [
                    'title' => $block->title,
                    'purpose' => $block->purpose,
                    'type' => $block->lab_type,
                    'start' => substr((string) $block->start_time, 0, 5),
                    'end' => substr((string) $block->end_time, 0, 5),
                    'rooms' => implode(', ', $block->rooms ?? []),
                    'pic' => $block->pic,
                    'id' => $block->id,
                ];
            }
        }

        return $events;
    }

    /**
     * Mirrors the legacy mockup's getRecurDates(): expands a block into up to 4 date occurrences.
     */
    public static function recurDates($baseDate, string $recurring, int $max = 4): array
    {
        $base = $baseDate instanceof Carbon ? $baseDate : Carbon::parse($baseDate);
        $dates = [$base->format('Y-m-d')];

        if ($recurring === 'none') {
            return $dates;
        }

        $step = $recurring === 'weekly' ? 7 : 14;

        for ($i = 1; $i < $max; $i++) {
            $dates[] = $base->copy()->addDays($step * $i)->format('Y-m-d');
        }

        return $dates;
    }
}
