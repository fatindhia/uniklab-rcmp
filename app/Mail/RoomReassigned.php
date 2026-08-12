<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RoomReassigned extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking, public string $oldRoomName, public string $newRoomName)
    {
        $this->booking->loadMissing('rooms.lab');
    }

    public function build(): self
    {
        return $this->subject('Room reassigned — '.$this->booking->ref)
            ->view('emails.bookings.room-reassigned');
    }
}
