<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Answers "why is nobody getting email?" without having to submit a booking.
 *
 * Booking mail is sent best-effort — BookingController::sendBookingSubmittedEmails()
 * and the admin decision paths all swallow the exception via report() so a dead
 * mail server can't roll back a committed booking. That's the right trade-off,
 * but it means a bad SMTP setup looks exactly like "nothing happened". This
 * command makes the failure loud instead.
 */
class TestMail extends Command
{
    protected $signature = 'mail:test {to? : Address to send to; omit to only check the connection}';

    protected $description = 'Check the SMTP connection and optionally send a test email';

    public function handle(): int
    {
        $this->table(['Setting', 'Value'], [
            ['mailer', config('mail.default')],
            ['host', config('mail.mailers.smtp.host')],
            ['port', config('mail.mailers.smtp.port')],
            ['scheme', config('mail.mailers.smtp.scheme') ?: '(auto)'],
            ['username', config('mail.mailers.smtp.username') ?: '(none)'],
            ['password', config('mail.mailers.smtp.password') ? '(set)' : '(none)'],
            ['from', config('mail.from.address').' — '.config('mail.from.name')],
        ]);

        // Connect and authenticate without sending anything, so a credential
        // problem is reported on its own rather than mixed up with a bad
        // recipient address.
        try {
            $transport = Mail::mailer()->getSymfonyTransport();
            $transport->start();
            $transport->stop();
            $this->info('✓ Connected and authenticated.');
        } catch (\Throwable $e) {
            $this->error('✗ Could not authenticate:');
            $this->line('  '.$e->getMessage());
            $this->newLine();
            $this->warn('Nothing will send until this is fixed. Booking mail fails silently by design.');

            return self::FAILURE;
        }

        $to = $this->argument('to');

        if (! $to) {
            $this->comment('Pass an address to also send a test email: php artisan mail:test you@example.com');

            return self::SUCCESS;
        }

        try {
            Mail::raw(
                'Test email from '.config('app.name').'. If you can read this, SMTP is working.',
                fn ($m) => $m->to($to)->subject('UniKLAB RCMP — SMTP test')
            );
            $this->info("✓ Test email sent to {$to}.");
        } catch (\Throwable $e) {
            $this->error('✗ Connected, but sending failed:');
            $this->line('  '.$e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
