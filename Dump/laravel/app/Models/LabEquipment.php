<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabEquipment extends Model
{
    use HasFactory;

    protected $table = 'lab_equipment';

    public $timestamps = false;

    protected $fillable = [
        'lab_id',
        'equipment_name',
        'special_conditions_note',
        'sort_order',
    ];

    public function lab()
    {
        return $this->belongsTo(Lab::class);
    }
}