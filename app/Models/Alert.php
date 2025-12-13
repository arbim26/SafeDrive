<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'trip_id',
        'type',
        'message',
        'severity',
        'acknowledged',
        'acknowledged_at',
        'metadata'
    ];

    protected $casts = [
        'acknowledged' => 'boolean',
        'acknowledged_at' => 'datetime',
        'metadata' => 'array'
    ];

    // Relationships
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}