<?php

namespace App\Console\Commands;

use App\Support\PublicHolidays;
use Illuminate\Console\Command;

/**
 * Refreshes the public holiday dates the calendars and booking form use.
 * Scheduled weekly in routes/console.php — this is the only place that talks to
 * the holiday feed, so a slow or dead API never reaches a page render.
 */
class SyncPublicHolidays extends Command
{
    protected $signature = 'holidays:sync
        {--year=* : Years to fetch (defaults to the current and next year)}
        {--dump : Also rewrite the bundled resources/data/public-holidays.json snapshot}';

    protected $description = 'Fetch Malaysian public holidays (federal + state) for the calendars';

    public function handle(): int
    {
        // The feed prunes past years, so the current one plus the next is the
        // whole useful window; anything older is already stored.
        $years = $this->option('year') ?: [now()->year, now()->year + 1];

        $result = PublicHolidays::sync(array_map('intval', $years));

        foreach ($result['failed'] as $year => $reason) {
            $this->warn("{$year}: {$reason} — keeping the stored dates for that year.");
        }

        if ($result['fetched'] === []) {
            $this->error('No holidays fetched; nothing was written.');

            return self::FAILURE;
        }

        foreach ($result['fetched'] as $year => $count) {
            $this->info("{$year}: {$count} holiday(s).");
        }

        $this->table(
            ['Date', 'Holiday', 'Subject to change'],
            collect($result['map'])
                ->map(fn ($holiday, $date) => [$date, $holiday['name'], $holiday['subject_to_change'] ? 'yes' : ''])
                ->values()
                ->all(),
        );

        if ($this->option('dump')) {
            $path = resource_path(PublicHolidays::SNAPSHOT_PATH);
            @mkdir(dirname($path), 0755, true);
            file_put_contents($path, json_encode($result['map'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
            $this->info("Snapshot written to {$path}.");
        }

        return self::SUCCESS;
    }
}
