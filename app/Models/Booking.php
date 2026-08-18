<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    const CREATED_AT = null;

    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'ref',
        'user_staff_id',
        'applicant_name',
        'applicant_id',
        'applicant_email',
        'applicant_phone',
        'applicant_department',
        'applicant_role',
        'applicant_group',
        'lab_type',
        'booking_date_from',
        'booking_date_to',
        'start_time',
        'end_time',
        'research_pax',
        'has_special_conditions',
        'csl_session_type',
        'csl_discipline',
        'csl_procedure',
        'csl_num_students',
        'pharma_primary_lab',
        'pharma_num_students',
        'pharma_tc_accepted',
        'purpose',
        'applicant_remark',
        'status',
        'admin_remark',
        'processed_by',
        'processed_at',
        'submitted_at',
        'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'booking_date_from' => 'date',
            'booking_date_to' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'has_special_conditions' => 'boolean',
            'pharma_tc_accepted' => 'boolean',
            'processed_at' => 'datetime',
            'submitted_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'ref';
    }

    /**
     * Human-readable booking date(s): a single date, or a "from – to" range
     * for multi-day (extended) bookings.
     */
    public function getDateRangeLabelAttribute(): string
    {
        $from = $this->booking_date_from;
        $to = $this->booking_date_to;

        if (! $from) {
            return (string) ($to ?? '');
        }

        if ($to && ! $to->isSameDay($from)) {
            return $from->format('d/m/Y').' – '.$to->format('d/m/Y');
        }

        return $from->format('d/m/Y');
    }

    /**
     * When the booking actually starts. The date and the time live in separate
     * columns, so anything comparing a booking against "now" — the pending
     * reminder, most obviously — has to recombine them first.
     */
    public function getStartsAtAttribute(): ?\Illuminate\Support\Carbon
    {
        if (! $this->booking_date_from || ! $this->start_time) {
            return null;
        }

        return $this->booking_date_from->copy()->setTimeFrom($this->start_time);
    }

    public function rooms()
    {
        return $this->hasMany(BookingRoom::class);
    }

    public function equipment()
    {
        return $this->hasMany(BookingEquipment::class);
    }

    public function students()
    {
        return $this->hasMany(BookingStudent::class)->orderBy('sort_order');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by', 'staff_id');
    }

    public function applicantUser()
    {
        return $this->belongsTo(User::class, 'user_staff_id', 'staff_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(BookingAuditLog::class)->orderBy('created_at');
    }
}