<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FatigueLog extends Model
{
    use HasFactory;

    protected $table = 'fatigue_logs';

    protected $fillable = [
        'trip_id',
        'driver_id',
        'ear',
        'mar',
        'eye_status',
        'yawn_detected',
        'seatbelt_on',
        'fatigue_score',
        'accuracy',
        'confidence_level',
        'detection_data',
        'location'
    ];

    protected $casts = [
        'ear' => 'decimal:3',
        'mar' => 'decimal:3',
        'yawn_detected' => 'boolean',
        'seatbelt_on' => 'boolean',
        'fatigue_score' => 'decimal:2',
        'accuracy' => 'decimal:2',
        'detection_data' => 'array',
        'location' => 'array'
    ];

    // Relationships
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}