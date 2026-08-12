<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lab extends Model
{
    use HasFactory;

    protected $table = 'labs';

    const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'lab_type',
        'location',
        'capacity',
        'status',
        'is_room_only',
        'weekends_allowed',
        'requires_special_conditions',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'is_room_only' => 'boolean',
            'weekends_allowed' => 'boolean',
            'requires_special_conditions' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    /**
     * The building a lab is in, derived from location's leading segment
     * (e.g. "Al-Zahrawi, Block A, Level 2" -> "Al-Zahrawi").
     */
    public function getBuildingAttribute(): string
    {
        return trim(explode(',', (string) $this->location, 2)[0]);
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