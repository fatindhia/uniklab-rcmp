<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingAuditLog extends Model
{
    protected $table = 'booking_audit_logs';

    const UPDATED_AT = null;

    protected $fillable = [
        'booking_id',
        'action',
        'performed_by',
        'detail',
        'is_late',
    ];

    protected function casts(): array
    {
        return [
            'is_late' => 'boolean',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by', 'staff_id');
    }
}
