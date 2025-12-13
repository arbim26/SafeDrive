<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Company;
use App\Models\DriverDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'phone' => $request->phone,
                'subscription' => 'free'
            ];
            
            // Jika user mendaftar sebagai company
            if ($request->role === 'company') {
                $company = Company::create([
                    'name' => $request->company_name,
                    'email' => $request->company_email,
                    'phone' => $request->phone,
                ]);
                
                $userData['company_id'] = $company->id;
            }
            
            // Buat user
            $user = User::create($userData);
            
            // Jika user adalah driver, buat driver detail
            if ($user->role === 'driver') {
                DriverDetail::create([
                    'user_id' => $user->id,
                    'license_number' => $request->license_number,
                    'license_expiry' => $request->license_expiry,
                    'emergency_contacts' => $request->emergency_contacts ?? [],
                ]);
            }
            
            // Generate token
            $token = auth()->login($user);
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'user' => new UserResource($user),
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                    'expires_in' => auth()->factory()->getTTL() * 60
                ]
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Registration failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed. Please try again.'
            ], 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');
            
            if (!$token = auth()->attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email atau password salah'
                ], 401);
            }
            
            $user = auth()->user();
            
            // Update device token jika ada (untuk push notification)
            if ($request->has('device_token')) {
                $user->update(['device_token' => $request->device_token]);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'user' => new UserResource($user),
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                    'expires_in' => auth()->factory()->getTTL() * 60
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Login failed', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Login failed. Please try again.'
            ], 500);
        }
    }

    public function logout(): JsonResponse
    {
        try {
            auth()->logout();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Logout error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Logout failed'
            ], 500);
        }
    }

    public function refresh(): JsonResponse
    {
        try {
            return response()->json([
                'status' => 'success',
                'authorization' => [
                    'token' => auth()->refresh(),
                    'type' => 'bearer',
                    'expires_in' => auth()->factory()->getTTL() * 60
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token refresh failed'
            ], 401);
        }
    }

    public function me(): JsonResponse
    {
        try {
            $user = auth()->user();
            
            return response()->json([
                'status' => 'success',
                'user' => new UserResource($user->load(['driverDetail', 'company']))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get user data'
            ], 500);
        }
    }
}