<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'license_number' => $this->license_number,
            'license_expiry' => $this->license_expiry,
            'vehicle_type' => $this->vehicle_type,
            'vehicle_plate' => $this->vehicle_plate,
            'emergency_contacts' => $this->emergency_contacts,
            'avg_fatigue_score' => $this->avg_fatigue_score,
            'total_trips' => $this->total_trips,
            'total_alerts' => $this->total_alerts,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}