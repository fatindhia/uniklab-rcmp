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
        'lab_type',
        'category',
        'title',
        'pic',
        'block_date',
        'start_time',
        'end_time',
        'rooms',
        'recurring',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'block_date' => 'date',
            'rooms' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'staff_id');
    }
}