<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    protected $fillable = [
        'ref',
        'user_staff_id',
        'applicant_name',
        'applicant_id',
        'applicant_email',
        'applicant_phone',
        'applicant_department',
        'applicant_role',
        'lab_type',
        'lab_block',
        'booking_date_from',
        'booking_date_to',
        'start_time',
        'end_time',
        'research_pax',
        'purpose_of_use',
        'has_special_conditions',
        'csl_session_type',
        'csl_discipline',
        'csl_num_students',
        'pharma_primary_lab',
        'pharma_group_number',
        'pharma_num_students',
        'pharma_tc_accepted',
        'purpose',
        'applicant_remark',
        'status',
        'admin_remark',
        'processed_by',
        'processed_at',
        'submitted_at',
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
            'updated_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'ref';
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
}