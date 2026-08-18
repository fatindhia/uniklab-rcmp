<?php

namespace App\Console\Commands;

use App\Mail\PendingBookingReminder;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Chases lab staff about bookings that are still "pending" with under 24 hours
 * to go. Scheduled hourly in routes/console.php.
 */
class SendPendingBookingReminders extends Command
{
    protected $signature = 'bookings:remind-pending {--dry-run : List what would be sent without sending or marking anything}';

    protected $description = 'Email lab staff about bookings still pending within 24 hours of their start time';

    public function handle(): int
    {
        $bookings = $this->dueForReminder();

        if ($bookings->isEmpty()) {
            $this->info('No pending bookings due a reminder.');

            return self::SUCCESS;
        }

        foreach ($bookings as $booking) {
            $recipients = User::bookingTicketRecipientEmails($booking->lab_type);

            if ($this->option('dry-run')) {
                $this->line("{$booking->ref} ({$booking->lab_type}, starts {$booking->starts_at->format('d/m/Y H:i')}) -> ".($recipients->join(', ') ?: 'nobody'));

                continue;
            }

            // Best-effort, exactly like the submit path: each send is isolated so
            // one unreachable address doesn't cost the rest of the team their
            // reminder.
            $sent = 0;

            foreach ($recipients as $email) {
                try {
                    Mail::to($email)->send(new PendingBookingReminder($booking));
                    $sent++;
                } catch (\Throwable $e) {
                    report($e);
                    $this->error("Failed to remind {$email} about {$booking->ref}: {$e->getMessage()}");
                }
            }

            // Only marked once something actually went out, so a total failure
            // (dead mail server) is retried on the next hourly run rather than
            // being silently written off.
            if ($sent > 0) {
                $booking->update(['reminder_sent_at' => now()]);
                $this->line("Reminded {$sent} recipient(s) about {$booking->ref}.");
            } else {
                $this->warn("No reminder sent for {$booking->ref}; will retry next run.");
            }
        }

        return self::SUCCESS;
    }

    /**
     * Bookings still awaiting a decision whose start time falls inside the next
     * 24 hours and which have not been chased already.
     *
     * The start is rebuilt in SQL from the separate date and time columns.
     * Anything that has already started is skipped: a reminder after the fact
     * is noise, and the booking shows up on the pending list regardless.
     */
    private function dueForReminder()
    {
        return Booking::with('rooms.lab')
            ->where('status', 'pending')
            ->whereNull('reminder_sent_at')
            ->whereRaw('TIMESTAMP(booking_date_from, start_time) BETWEEN ? AND ?', [
                now(),
                now()->addDay(),
            ])
            ->orderByRaw('TIMESTAMP(booking_date_from, start_time)')
            ->get();
    }
}
