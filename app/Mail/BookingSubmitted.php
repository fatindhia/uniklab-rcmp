<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
        $this->booking->loadMissing('rooms.lab');
    }

    public function build(): self
    {
        return $this->subject('Booking submitted — '.$this->booking->ref)
            ->view('emails.bookings.submitted');
    }
}
