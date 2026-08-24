<?php

namespace App\Support;

use App\Models\Booking;
use Illuminate\Support\Carbon;

/**
 * Turns a booking's four columns (booking_date_from/to + start_time/end_time)
 * into the absolute time intervals it actually occupies, so every conflict
 * check can compare real points on the clock instead of comparing a date range
 * and a time range independently.
 *
 * That independent comparison is what made a 20 Aug 12:30 -> 21 Aug 12:30
 * booking read as "12:30-13:30 on both days" — one hour, twice — instead of a
 * single 24-hour run, and left the overnight hours free for someone else to
 * book. A booking now says which of the two it means (bookings.is_continuous)
 * and this class is the only place that knows what each one occupies:
 *
 *   daily      one interval per day in the range (unchanged, the old meaning)
 *   continuous exactly one interval, date_from+start_time -> date_to+end_time
 *
 * Intervals are half-open [start, end), which preserves the existing rule that
 * back-to-back 09:00-10:00 and 10:00-11:00 bookings do not conflict.
 */
class BookingSpan
{
    /**
     * Same runaway-data guard BookingCalendar uses when expanding a range.
     * Only ever reached by a daily booking, whose per-day expansion is
     * unbounded; a continuous booking is one interval however long it runs.
     */
    private const MAX_DAYS = 366;

    /**
     * How "runs to midnight" is written on a calendar. 24:00 rather than 23:59
     * so a run reads as unbroken across the day boundary — 23:59 followed by
     * the next day's 00:00 looks like a minute where the equipment is free,
     * which is the exact misreading this whole distinction exists to prevent.
     */
    private const END_OF_DAY = '24:00';

    /**
     * @return array<int, array{0: Carbon, 1: Carbon}>
     */
    public static function intervals(string $dateFrom, ?string $dateTo, string $startTime, string $endTime, bool $continuous): array
    {
        if ($dateFrom === '') {
            return [];
        }

        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = $dateTo ? Carbon::parse($dateTo)->startOfDay() : $from->copy();

        if ($to->lt($from)) {
            $to = $from->copy();
        }

        $startMinutes = self::minutesOfDay($startTime);
        $endMinutes = self::minutesOfDay($endTime);

        if ($continuous) {
            return [[
                $from->copy()->addMinutes($startMinutes),
                $to->copy()->addMinutes($endMinutes),
            ]];
        }

        $intervals = [];

        for ($day = $from->copy(), $guard = 0; $day->lte($to) && $guard < self::MAX_DAYS; $day->addDay(), $guard++) {
            $intervals[] = [
                $day->copy()->addMinutes($startMinutes),
                $day->copy()->addMinutes($endMinutes),
            ];
        }

        return $intervals;
    }

    /**
     * @return array<int, array{0: Carbon, 1: Carbon}>
     */
    public static function fromBooking(Booking $booking): array
    {
        if (! $booking->booking_date_from) {
            return [];
        }

        return self::intervals(
            $booking->booking_date_from->format('Y-m-d'),
            $booking->booking_date_to?->format('Y-m-d'),
            self::timeLabel($booking->start_time),
            self::timeLabel($booking->end_time),
            (bool) $booking->is_continuous,
        );
    }

    /**
     * Do any two of these intervals touch? $bufferMinutes widens every interval
     * in $b on both sides, which is how the CSL turnaround gap is enforced — a
     * booking that merely sits inside the gap still counts as a clash.
     *
     * @param  array<int, array{0: Carbon, 1: Carbon}>  $a
     * @param  array<int, array{0: Carbon, 1: Carbon}>  $b
     */
    public static function overlaps(array $a, array $b, int $bufferMinutes = 0): bool
    {
        foreach ($a as [$aStart, $aEnd]) {
            foreach ($b as [$bStart, $bEnd]) {
                $start = $bufferMinutes > 0 ? $bStart->copy()->subMinutes($bufferMinutes) : $bStart;

                // Both sides come out of intervals() in ascending order, so once
                // a candidate starts at or after this interval's end, so does
                // every candidate after it.
                if ($start->gte($aEnd)) {
                    break;
                }

                $end = $bufferMinutes > 0 ? $bEnd->copy()->addMinutes($bufferMinutes) : $bEnd;

                if ($aStart->lt($end) && $aEnd->gt($start)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Length of one session: the whole run for a continuous booking, or a
     * single day's window for a daily one. This is what the minimum-duration
     * rule means — a 3-day daily booking of 09:00-09:30 is three half-hour
     * sessions, not a 90-minute booking.
     *
     * @param  array<int, array{0: Carbon, 1: Carbon}>  $intervals
     */
    public static function sessionMinutes(array $intervals): int
    {
        if (! $intervals) {
            return 0;
        }

        [$start, $end] = $intervals[0];

        return (int) $start->diffInMinutes($end, false);
    }

    /**
     * Total occupied time across every interval.
     *
     * @param  array<int, array{0: Carbon, 1: Carbon}>  $intervals
     */
    public static function totalMinutes(array $intervals): int
    {
        $total = 0;

        foreach ($intervals as [$start, $end]) {
            $total += max(0, (int) $start->diffInMinutes($end, false));
        }

        return $total;
    }

    /**
     * How a booking looks on a day-by-day calendar. A daily booking shows the
     * same window on every day; a continuous one is sliced at midnight, so the
     * first day runs from its start time to 24:00, whole days in the middle
     * read 00:00-24:00, and the last day ends at its end time.
     *
     * 24:00 and the next day's 00:00 are the same instant, which is what makes
     * the run read as continuous across the boundary. These strings are for
     * display only — every conflict check works from intervals(), where that
     * boundary is a real Carbon midnight.
     *
     * @return array<int, array{date: string, start: string, end: string}>
     */
    public static function daySlices(string $dateFrom, ?string $dateTo, string $startTime, string $endTime, bool $continuous): array
    {
        if ($dateFrom === '') {
            return [];
        }

        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = $dateTo ? Carbon::parse($dateTo)->startOfDay() : $from->copy();

        if ($to->lt($from)) {
            $to = $from->copy();
        }

        $slices = [];

        for ($day = $from->copy(), $guard = 0; $day->lte($to) && $guard < self::MAX_DAYS; $day->addDay(), $guard++) {
            if (! $continuous) {
                $slices[] = ['date' => $day->format('Y-m-d'), 'start' => $startTime, 'end' => $endTime];

                continue;
            }

            $isFirst = $day->isSameDay($from);
            $isLast = $day->isSameDay($to);

            // A run ending at midnight has nothing left to show on its final
            // day — it finished as the previous day closed.
            if ($isLast && ! $isFirst && self::minutesOfDay($endTime) === 0) {
                continue;
            }

            $slices[] = [
                'date' => $day->format('Y-m-d'),
                'start' => $isFirst ? $startTime : '00:00',
                'end' => $isLast ? $endTime : self::END_OF_DAY,
            ];
        }

        return $slices;
    }

    /**
     * "45m", "25h 30m", "72h (3 days)" — hours stay the headline unit because
     * that is how the labs talk about a run ("it needs 24 hours"), with days
     * added once the raw hour count stops being readable at a glance.
     */
    public static function humanDuration(int $minutes): string
    {
        if ($minutes <= 0) {
            return '—';
        }

        $hours = intdiv($minutes, 60);
        $remainder = $minutes % 60;

        $label = implode(' ', array_filter([
            $hours ? $hours.'h' : '',
            $remainder ? $remainder.'m' : '',
        ]));

        if ($hours >= 48) {
            $days = round($hours / 24, 1);
            $label .= ' ('.rtrim(rtrim(number_format($days, 1), '0'), '.').' days)';
        }

        return $label;
    }

    /**
     * Minutes past midnight. "24:00" parses as the day's close rather than
     * overflowing, so a stored end time of that form still lands correctly.
     */
    private static function minutesOfDay(string $time): int
    {
        [$hours, $minutes] = array_pad(array_map('intval', explode(':', $time, 2)), 2, 0);

        return $hours * 60 + $minutes;
    }

    /**
     * start_time/end_time are cast to Carbon on the model but arrive as plain
     * strings straight from a query — accept either, and always hand back a
     * bare "HH:MM" for display and comparison.
     */
    public static function timeLabel(mixed $time): string
    {
        if ($time instanceof \DateTimeInterface) {
            return $time->format('H:i');
        }

        return substr((string) $time, 0, 5);
    }
}
