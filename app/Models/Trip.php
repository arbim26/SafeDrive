<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'start_time',
        'end_time',
        'start_location',
        'end_location',
        'distance_km',
        'status',
        'route_coordinates'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'route_coordinates' => 'array'
    ];

    // Relationships
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function fatigueLogs()
    {
        return $this->hasMany(FatigueLog::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    // Methods
    public function calculateFatigueStats()
    {
        $logs = $this->fatigueLogs;
        
        return [
            'avg_fatigue' => $logs->avg('fatigue_score'),
            'total_yawns' => $logs->where('yawn_detected', true)->count(),
            'eye_closures' => $logs->where('eye_status', 'closed')->count(),
            'seatbelt_violations' => $logs->where('seatbelt_on', false)->count(),
            'high_fatigue_percentage' => ($logs->where('fatigue_score', '>', 0.7)->count() / max($logs->count(), 1)) * 100
        ];
    }
}