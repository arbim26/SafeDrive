<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Artisan;

Route::get('/ping', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'udah konek',
        'timestamp' => now()->toISOString(),
        'service' => 'SafeDrive API'
    ]);
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
});

Route::get('/__migrate_fresh', function () {
    Artisan::call('migrate:fresh', ['--force' => true]);
    return response()->json([
        'status' => 'ok',
        'output' => Artisan::output()
    ]);
});
