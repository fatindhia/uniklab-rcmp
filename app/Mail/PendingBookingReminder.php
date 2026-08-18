<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Chases the lab staff about a booking that is still sitting on "pending" with
 * less than 24 hours to go before the slot starts. Goes to the same recipients
 * as NewBookingTicket — see User::scopeBookingTicketRecipients().
 */
class PendingBookingReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
        $this->booking->loadMissing(['rooms.lab', 'equipment.lab']);
    }

    public function build(): self
    {
        return $this->subject('Reminder: booking still pending — '.$this->booking->ref)
            ->view('emails.bookings.pending-reminder');
    }
}
