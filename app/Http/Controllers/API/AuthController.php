<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login worker and return Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_token' => 'nullable|string',
            'device_name' => 'nullable|string',
            'device_type' => 'nullable|in:android,ios',
        ]);

        $user = User::where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('worker-token')->plainTextToken;

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
                'company_id' => $user->company_id,
                'company_name' => $user->company->name,
                'role' => $user->role,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Logout worker and revoke token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get authenticated user info.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('company');

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'company_id' => $user->company_id,
                'company_name' => $user->company->name,
                'role' => $user->role,
                'last_active_at' => $user->last_active_at,
            ],
        ]);
    }

    /**
     * Update device token for push notifications.
     */
    public function updateDeviceToken(Request $request): JsonResponse
    {
        $request->validate([
            'device_token' => 'required|string',
            'device_name' => 'nullable|string',
            'device_type' => 'nullable|in:android,ios',
        ]);

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
            'message' => 'Device token updated',
        ]);
    }
}
