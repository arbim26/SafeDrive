<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'license_number',
        'license_expiry',
        'vehicle_type',
        'vehicle_plate',
        'emergency_contacts',
        'avg_fatigue_score',
        'total_trips',
        'total_alerts'
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'emergency_contacts' => 'array',
        'avg_fatigue_score' => 'decimal:2',
    ];

    

    // Relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
