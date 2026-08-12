<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lab extends Model
{
    use HasFactory;

    protected $table = 'labs';

    protected $fillable = [
        'name',
        'lab_type',
        'lab_block',
        'room_code',
        'location',
        'capacity',
        'status',
        'is_iso_certified',
        'is_room_only',
        'requires_special_conditions',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'is_iso_certified' => 'boolean',
            'is_room_only' => 'boolean',
            'requires_special_conditions' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function equipment()
    {
        return $this->hasMany(LabEquipment::class);
    }

    public function bookingRooms()
    {
        return $this->hasMany(BookingRoom::class);
    }
}