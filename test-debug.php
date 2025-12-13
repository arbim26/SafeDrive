<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test JWT
$user = \App\Models\User::factory()->create();
$token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

echo "User ID: " . $user->id . "\n";
echo "Token: " . $token . "\n";

// Test token validity
try {
    $payload = \Tymon\JWTAuth\Facades\JWTAuth::setToken($token)->getPayload();
    echo "Token valid, expires at: " . $payload['exp'] . "\n";
} catch (\Exception $e) {
    echo "Token invalid: " . $e->getMessage() . "\n";
}