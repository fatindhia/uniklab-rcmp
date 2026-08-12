<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeBlock extends Model
{
    use HasFactory;

    protected $table = 'time_blocks';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'group_id',
        'lab_type',
        'purpose',
        'title',
        'pic',
        'block_date',
        'start_time',
        'end_time',
        'rooms',
        'equipment',
        'recurring',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'block_date' => 'date',
            'rooms' => 'array',
            'equipment' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'staff_id');
    }
}