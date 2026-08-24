<?php

namespace App\Models;

use App\Support\BookingSpan;
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
        'is_continuous',
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
            'is_continuous' => 'boolean',
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
     * Human-readable booking date(s): a single date, a "from – to" range for a
     * multi-day (extended) booking, or — for a continuous run, where the dates
     * and times are one unbroken span — both ends written out in full, since
     * "20/08/2026 – 21/08/2026" plus a separate "12:30" row is exactly the
     * reading that hid a 24-hour booking behind a one-hour label.
     */
    public function getDateRangeLabelAttribute(): string
    {
        $from = $this->booking_date_from;
        $to = $this->booking_date_to;

        if (! $from) {
            return (string) ($to ?? '');
        }

        if ($to && ! $to->isSameDay($from)) {
            if ($this->is_continuous) {
                return $from->format('d/m/Y').' '.BookingSpan::timeLabel($this->start_time)
                    .' → '.$to->format('d/m/Y').' '.BookingSpan::timeLabel($this->end_time);
            }

            return $from->format('d/m/Y').' – '.$to->format('d/m/Y');
        }

        return $from->format('d/m/Y');
    }

    /**
     * The time half of the same story. A continuous run has no per-day window
     * to show — its length is the useful fact — while a multi-day daily
     * booking says so out loud, so nobody reads two dates and one window as a
     * single stretch again.
     */
    public function getTimeRangeLabelAttribute(): string
    {
        if (! $this->start_time || ! $this->end_time) {
            return '';
        }

        if ($this->is_continuous) {
            return 'Continuous run · '.BookingSpan::humanDuration(BookingSpan::totalMinutes(BookingSpan::fromBooking($this)));
        }

        $label = BookingSpan::timeLabel($this->start_time).' - '.BookingSpan::timeLabel($this->end_time);

        if ($this->booking_date_to && $this->booking_date_from && ! $this->booking_date_to->isSameDay($this->booking_date_from)) {
            $label .= ' (each day)';
        }

        return $label;
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

    /**
     * When the booking actually finishes. For a continuous run that is the end
     * date's end time; for a daily one it is the last day's window closing.
     */
    public function getEndsAtAttribute(): ?\Illuminate\Support\Carbon
    {
        if (! $this->end_time) {
            return null;
        }

        $lastDay = $this->booking_date_to ?: $this->booking_date_from;

        return $lastDay?->copy()->setTimeFrom($this->end_time);
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