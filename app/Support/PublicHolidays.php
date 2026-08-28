<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

/**
 * Malaysian public holidays (federal + Perak state) shown on every calendar and
 * warned about on the booking form.
 *
 * Backed by the `settings` key/value table (see Setting) rather than its own
 * table, the same way Maintenance is — the list is a few dozen dates a year
 * refreshed by a scheduled command, not relational data anyone queries against.
 *
 * The feed is a third-party community API: there is no official government one,
 * so it can be slow or simply disappear. Nothing a page render touches is
 * allowed to depend on it — only sync() makes an HTTP call, and it only ever
 * runs from the holidays:sync command. Reads fall back to the snapshot
 * committed at resources/data/public-holidays.json, so a wiped settings row or
 * a dead API costs accuracy, never a broken calendar or booking form.
 */
class PublicHolidays
{
    private const SETTING_KEY = 'public_holidays';

    public const SNAPSHOT_PATH = 'data/public-holidays.json';

    /**
     * Date-keyed map: ['2026-08-31' => ['name' => '...', 'subject_to_change' => false], ...]
     *
     * Never makes an HTTP call.
     */
    public static function map(): array
    {
        $stored = static::decode(Setting::get(self::SETTING_KEY));

        return $stored ?: static::snapshot();
    }

    public static function isHoliday(string $date): bool
    {
        return isset(static::map()[$date]);
    }

    public static function name(string $date): ?string
    {
        return static::map()[$date]['name'] ?? null;
    }

    /**
     * Every holiday between two dates inclusive, in date order — what the
     * booking form warns about, since an extended booking can straddle one
     * without either end date landing on it.
     *
     * @return array<string, array{name: string, subject_to_change: bool}>
     */
    public static function between(string $from, string $to): array
    {
        [$from, $to] = $from <= $to ? [$from, $to] : [$to, $from];

        return array_filter(
            static::map(),
            fn ($date) => $date >= $from && $date <= $to,
            ARRAY_FILTER_USE_KEY,
        );
    }

    /**
     * Fetches the given years and merges them into the stored map.
     *
     * A year that fails or comes back empty leaves that year's stored dates
     * untouched instead of deleting them: the API drops past years (2025
     * already returns count: 0), and a transient outage must not silently
     * empty the calendars.
     *
     * @param  array<int>  $years
     * @return array{map: array, fetched: array<int, int>, failed: array<int, string>}
     */
    public static function sync(array $years): array
    {
        $existing = static::decode(Setting::get(self::SETTING_KEY)) ?: static::snapshot();
        $merged = $existing;
        $fetched = [];
        $failed = [];

        foreach ($years as $year) {
            try {
                $holidays = static::fetchYear((int) $year);
            } catch (\Throwable $e) {
                report($e);
                $failed[(int) $year] = $e->getMessage();

                continue;
            }

            if ($holidays === []) {
                $failed[(int) $year] = 'the feed returned no holidays for this year';

                continue;
            }

            // Replace this year wholesale so a date the government moved is
            // dropped, while every other year stays as it was.
            $merged = array_filter(
                $merged,
                fn ($date) => ! str_starts_with((string) $date, $year.'-'),
                ARRAY_FILTER_USE_KEY,
            );
            $merged += $holidays;
            $fetched[(int) $year] = count($holidays);
        }

        if ($fetched !== []) {
            ksort($merged);
            Setting::set(self::SETTING_KEY, json_encode($merged));

            // The calendar payload is cached for 5 minutes; flush so admins see
            // a re-sync immediately rather than on the next TTL expiry.
            BookingCalendar::flush();
        }

        return ['map' => $merged, 'fetched' => $fetched, 'failed' => $failed];
    }

    /**
     * @return array<string, array{name: string, subject_to_change: bool}>
     */
    private static function fetchYear(int $year): array
    {
        $response = Http::timeout(10)
            ->retry(2, 500, throw: false)
            ->acceptJson()
            ->get(config('booking.holidays.api_url'), [
                'year' => $year,
                'state' => config('booking.holidays.state'),
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException("holiday feed returned HTTP {$response->status()}");
        }

        $holidays = [];

        foreach ($response->json('data') ?? [] as $entry) {
            $date = $entry['date'] ?? null;
            $name = $entry['name'] ?? null;

            if (! $date || ! $name) {
                continue;
            }

            $holidays[$date] = [
                'name' => $name,
                'subject_to_change' => (bool) ($entry['is_subject_to_change'] ?? false),
            ];
        }

        return $holidays;
    }

    /**
     * The copy committed to the repo — what the app uses before the first sync
     * has run, and what it falls back to if the feed ever dies for good.
     */
    public static function snapshot(): array
    {
        $path = resource_path(self::SNAPSHOT_PATH);

        return is_readable($path) ? static::decode(file_get_contents($path)) : [];
    }

    private static function decode(mixed $json): array
    {
        if (! is_string($json) || $json === '') {
            return [];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }
}
