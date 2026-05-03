<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\DeviceTokenRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Cache\RateLimiter;

class AuthController extends Controller
{
    public function __construct(
        private RateLimiter $limiter
    ) {}

    /**
     * Login worker and return Sanctum token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $email = $request->email;
        $key = 'login:' . $email;

        // Rate limiting: 5 attempts per minute per email
        if ($this->limiter->tooManyAttempts($key, 5)) {
            $seconds = $this->limiter->availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Too many login attempts. Please try again in {$seconds} seconds.",
                'retry_after' => $seconds,
            ], 429);
        }

        $this->limiter->hit($key, 60);

        $user = User::with('company')->where('email', $email)->where('is_active', true)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials are incorrect.',
                'errors' => [
                    'email' => ['The provided credentials are incorrect.'],
                ],
            ], 401);
        }

        // Revoke existing tokens for this user (optional - for single device)
        // $user->tokens()->delete();

        $token = $user->createToken('worker-' . now()->timestamp)->plainTextToken;

        if ($request->device_token) {
            $user->deviceTokens()->updateOrCreate(
                ['token' => $request->device_token],
                [
                    'company_id' => $user->company_id,
                    'device_name' => $request->device_name,
                    'device_type' => $request->device_type ?? 'android',
                    'is_active' => true,
                    'last_used_at' => now(),
                ]
            );
        }

        $user->update(['last_active_at' => now()]);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'company_id' => $user->company_id,
                'company_name' => $user->company?->name,
                'role' => $user->role,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * Logout worker and revoke token.
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
            ], 500);
        }
    }

    /**
     * Get authenticated user info.
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user()->load('company');

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'company_id' => $user->company_id,
                    'company_name' => $user->company?->name,
                    'role' => $user->role,
                    'last_active_at' => $user->last_active_at?->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user information',
            ], 500);
        }
    }

    /**
     * Update device token for push notifications.
     */
    public function updateDeviceToken(DeviceTokenRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            $user->deviceTokens()->updateOrCreate(
                ['token' => $request->device_token],
                [
                    'company_id' => $user->company_id,
                    'device_name' => $request->device_name,
                    'device_type' => $request->device_type ?? 'android',
                    'is_active' => true,
                    'last_used_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Device token updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update device token',
            ], 500);
        }
    }
}