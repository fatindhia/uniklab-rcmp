<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingEquipment extends Model
{
    use HasFactory;

    protected $table = 'booking_equipment';

    public $timestamps = false;

    protected $fillable = [
        'booking_id',
        'lab_id',
        'equipment_name',
        'is_alt_lab',
    ];

    protected function casts(): array
    {
        return [
            'is_alt_lab' => 'boolean',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function lab()
    {
        return $this->belongsTo(Lab::class);
    }
}