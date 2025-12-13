<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogFatigueRequest;
use App\Models\FatigueLog;
use App\Models\Alert;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class FatigueDetectionController extends Controller
{
    public function logFatigue(LogFatigueRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            // Cari trip aktif
            $trip = Trip::where('driver_id', $user->id)
                       ->where('status', 'active')
                       ->first();
            
            if (!$trip) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No active trip found'
                ], 400);
            }
            
            // Hitung fatigue score jika tidak disediakan
            $fatigueScore = $request->fatigue_level ?? $this->calculateFatigueScore($request);
            
            // Buat fatigue log
            $fatigueLog = FatigueLog::create([
                'trip_id' => $trip->id,
                'driver_id' => $user->id,
                'ear' => $request->ear,
                'mar' => $request->mar,
                'eye_status' => $request->eye_status,
                'yawn_detected' => $request->yawn_detected,
                'seatbelt_on' => $request->seatbelt_on,
                'fatigue_score' => $fatigueScore,
                'accuracy' => $request->accuracy,
                'confidence_level' => $request->confidence_level,
                'location' => [
                    'lat' => $request->latitude,
                    'lng' => $request->longitude
                ],
                'detection_data' => $request->detection_data
            ]);
            
            // Update trip route coordinates
            $this->updateTripRoute($trip, $request);
            
            // Cek dan buat alerts
            $alerts = $this->checkAndCreateAlerts($fatigueLog, $trip, $request);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Fatigue data logged successfully',
                'data' => [
                    'fatigue_log' => $fatigueLog,
                    'alerts_created' => count($alerts),
                    'alerts' => $alerts,
                    'fatigue_score' => $fatigueScore,
                    'recommendation' => $this->getRecommendation($fatigueScore, $request)
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to log fatigue data', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to log fatigue data'
            ], 500);
        }
    }
    
    private function calculateFatigueScore($request): float
    {
        $score = 0.0;
        
        // Berdasarkan algoritma Python Anda
        if ($request->eye_status === 'closed') {
            $score += 0.6;
        } elseif ($request->eye_status === 'partial') {
            $score += 0.3;
        }
        
        if ($request->yawn_detected) {
            $score += 0.4;
        }
        
        if ($request->mar > 0.6) {
            $score += ($request->mar - 0.6) * 0.5;
        }
        
        if (!$request->seatbelt_on) {
            $score += 0.2;
        }
        
        // Tambahkan dari EAR jika perlu
        if ($request->ear < 0.25) {
            $score += 0.3;
        }
        
        return min($score, 1.0);
    }
    
    private function checkAndCreateAlerts($fatigueLog, $trip, $request): array
    {
        $alerts = [];
        
        // High fatigue alert
        if ($fatigueLog->fatigue_score > 0.8) {
            $alert = $this->createAlert(
                $fatigueLog->driver_id,
                $trip->id,
                'fatigue_high',
                'High fatigue level detected: ' . round($fatigueLog->fatigue_score * 100) . '%',
                'high',
                [
                    'fatigue_score' => $fatigueLog->fatigue_score,
                    'eye_status' => $request->eye_status,
                    'yawn_detected' => $request->yawn_detected
                ]
            );
            $alerts[] = $alert;
        }
        
        // Eyes closed alert
        if ($request->eye_status === 'closed') {
            // Cek apakah ini closure yang berkepanjangan
            $recentClosures = FatigueLog::where('driver_id', $fatigueLog->driver_id)
                ->where('eye_status', 'closed')
                ->where('created_at', '>=', now()->subMinutes(5))
                ->count();
                
            if ($recentClosures >= 3) {
                $alert = $this->createAlert(
                    $fatigueLog->driver_id,
                    $trip->id,
                    'eyes_closed',
                    'Multiple eye closures detected (' . $recentClosures . ' in 5 minutes)',
                    $recentClosures >= 5 ? 'critical' : 'high',
                    [
                        'consecutive_closures' => $recentClosures,
                        'time_window' => '5 minutes'
                    ]
                );
                $alerts[] = $alert;
            }
        }
        
        // No seatbelt alert
        if (!$request->seatbelt_on) {
            $alert = $this->createAlert(
                $fatigueLog->driver_id,
                $trip->id,
                'no_seatbelt',
                'Driver is not wearing seatbelt',
                'medium',
                ['seatbelt_status' => false]
            );
            $alerts[] = $alert;
        }
        
        // Yawning frequently alert
        if ($request->yawn_detected) {
            $recentYawns = FatigueLog::where('driver_id', $fatigueLog->driver_id)
                ->where('yawn_detected', true)
                ->where('created_at', '>=', now()->subMinutes(10))
                ->count();
                
            if ($recentYawns >= 5) {
                $alert = $this->createAlert(
                    $fatigueLog->driver_id,
                    $trip->id,
                    'yawn_frequent',
                    'Frequent yawning detected (' . $recentYawns . ' times in 10 minutes)',
                    'medium',
                    ['yawn_count' => $recentYawns, 'time_window' => '10 minutes']
                );
                $alerts[] = $alert;
            }
        }
        
        return $alerts;
    }
    
    private function createAlert($driverId, $tripId, $type, $message, $severity, $metadata = []): Alert
    {
        return Alert::create([
            'driver_id' => $driverId,
            'trip_id' => $tripId,
            'type' => $type,
            'message' => $message,
            'severity' => $severity,
            'metadata' => array_merge($metadata, [
                'timestamp' => now()->toISOString()
            ])
        ]);
    }
    
    private function updateTripRoute($trip, $request): void
    {
        $route = $trip->route_coordinates ?? [];
        $route[] = [
            'lat' => $request->latitude,
            'lng' => $request->longitude,
            'timestamp' => now()->toISOString(),
            'fatigue_score' => $request->fatigue_level ?? 0,
            'eye_status' => $request->eye_status
        ];
        
        $trip->route_coordinates = $route;
        $trip->save();
    }
    
    private function getRecommendation($fatigueScore, $request): string
    {
        if ($fatigueScore > 0.8) {
            return 'Immediate rest recommended. Pull over safely.';
        }
        
        if ($fatigueScore > 0.6) {
            return 'Take a break soon. Consider stopping at next rest area.';
        }
        
        if ($request->eye_status === 'closed') {
            return 'Eyes closed detected. Ensure driver is alert.';
        }
        
        if (!$request->seatbelt_on) {
            return 'Please wear your seatbelt for safety.';
        }
        
        return 'Drive safely. Stay alert.';
    }
}