<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class EquipmentConditions
{
    protected const DAY_SETS = [
        'mon-thu' => ['Mon', 'Tue', 'Wed', 'Thu'],
        'mon-fri' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
        'weekdays' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
    ];

    /**
     * Parse a lab_equipment.special_conditions_note like:
     *   "Mon-Thu only * 10:00-16:00 * No Friday / Weekend"
     *   "Weekdays only * 08:30-16:30 * Min. 60 min"
     *   "Mon-Thu only * Full-day booking * 1-day buffer required before next booking"
     * into a structured rule set. Unrecognised clauses are ignored (informational only).
     */
    public static function parse(string $note): array
    {
        $rules = [
            'days' => null,
            'time_start' => null,
            'time_end' => null,
            'min_minutes' => null,
            'full_day' => false,
            'buffer_days' => null,
        ];

        foreach (array_map('trim', explode('*', $note)) as $clause) {
            if ($clause === '') {
                continue;
            }

            if (preg_match('/^(mon-thu|mon-fri|weekdays)\s+only$/i', $clause, $m)) {
                $rules['days'] = self::DAY_SETS[strtolower($m[1])] ?? null;

                continue;
            }

            if (preg_match('/^(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})$/', $clause, $m)) {
                $rules['time_start'] = $m[1];
                $rules['time_end'] = $m[2];

                continue;
            }

            if (preg_match('/^min\.?\s*(\d+)\s*min/i', $clause, $m)) {
                $rules['min_minutes'] = (int) $m[1];

                continue;
            }

            if (preg_match('/^full-day booking$/i', $clause)) {
                $rules['full_day'] = true;

                continue;
            }

            if (preg_match('/^(\d+)[- ]day buffer required/i', $clause, $m)) {
                $rules['buffer_days'] = (int) $m[1];

                continue;
            }
        }

        return $rules;
    }

    /**
     * Validate a requested booking date/time against a parsed rule set.
     * Returns an array of human-readable violation messages (empty = OK).
     */
    public static function violations(array $rules, string $date, string $startTime, string $endTime): array
    {
        $violations = [];
        $day = Carbon::parse($date)->format('D');

        if ($rules['days'] && ! in_array($day, $rules['days'], true)) {
            $violations[] = 'is only available on '.implode(', ', $rules['days']).'.';
        }

        if ($rules['time_start'] && $rules['time_end']) {
            if ($startTime < $rules['time_start'] || $endTime > $rules['time_end']) {
                $violations[] = 'is only available between '.$rules['time_start'].' and '.$rules['time_end'].'.';
            }
        }

        if ($rules['min_minutes']) {
            $minutes = Carbon::parse($startTime)->diffInMinutes(Carbon::parse($endTime));
            if ($minutes < $rules['min_minutes']) {
                $violations[] = 'requires a minimum booking of '.$rules['min_minutes'].' minutes.';
            }
        }

        if ($rules['full_day']) {
            if ($startTime > config('booking.work_start') || $endTime < config('booking.work_end')) {
                $violations[] = 'requires a full-day booking ('.config('booking.work_start').'–'.config('booking.work_end').').';
            }
        }

        return $violations;
    }
}
