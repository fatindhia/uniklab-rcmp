<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    protected const SUBJECTS = [
        'approved' => 'Booking approved',
        'rejected' => 'Booking rejected',
        'cancelled' => 'Booking cancelled',
    ];

    public function __construct(public Booking $booking, public string $status, public ?string $roomReassignedFrom = null)
    {
        $this->booking->loadMissing(['rooms.lab', 'equipment.lab']);
    }

    public function build(): self
    {
        $subject = self::SUBJECTS[$this->status] ?? 'Booking updated';

        return $this->subject($subject.' — '.$this->booking->ref)
            ->view('emails.bookings.status-updated')
            ->with([
                'status' => $this->status,
                'roomReassignedFrom' => $this->roomReassignedFrom,
            ]);
    }
}
