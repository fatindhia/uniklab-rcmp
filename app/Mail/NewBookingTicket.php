<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewBookingTicket extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
        $this->booking->loadMissing('rooms.lab');
    }

    public function build(): self
    {
        return $this->subject('New booking ticket — '.$this->booking->ref)
            ->view('emails.bookings.new-ticket-admin');
    }
}
