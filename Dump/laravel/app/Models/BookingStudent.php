<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingStudent extends Model
{
    use HasFactory;

    protected $table = 'booking_students';

    public $timestamps = false;

    protected $fillable = [
        'booking_id',
        'student_name',
        'student_id',
        'student_year',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'student_year' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}