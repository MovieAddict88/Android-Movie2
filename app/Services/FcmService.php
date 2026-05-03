<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * FCM API URL.
     */
    private const FCM_URL = 'https://fcm.googleapis.com/fcm/send';

    /**
     * Send push notification to a specific device token.
     */
    public function sendToToken(
        DeviceToken $deviceToken,
        string $title,
        string $body,
        array $data = []
    ): bool {
        $serverKey = config('services.fcm.server_key');

        if (!$serverKey) {
            Log::warning('FCM server key not configured');
            return false;
        }

        $payload = [
            'to' => $deviceToken->token,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
                'priority' => 'high',
            ],
            'data' => array_merge($data, [
                'click_action' => 'OPEN_APP',
            ]),
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'channel_id' => 'field_service_jobs',
                    'sound' => 'default',
                    'priority' => 'high',
                ],
            ],
        ];

        return $this->send($payload, $serverKey);
    }

    /**
     * Send push notification to multiple device tokens.
     */
    public function sendToTokens(
        array $tokens,
        string $title,
        string $body,
        array $data = []
    ): array {
        $serverKey = config('services.fcm.server_key');

        if (!$serverKey) {
            Log::warning('FCM server key not configured');
            return ['success' => 0, 'failure' => count($tokens)];
        }

        $payload = [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
                'priority' => 'high',
            ],
            'data' => array_merge($data, [
                'click_action' => 'OPEN_APP',
            ]),
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'channel_id' => 'field_service_jobs',
                    'sound' => 'default',
                    'priority' => 'high',
                ],
            ],
        ];

        $result = $this->send($payload, $serverKey);

        return [
            'success' => $result ? count($tokens) : 0,
            'failure' => $result ? 0 : count($tokens),
        ];
    }

    /**
     * Send to all active devices for a company.
     */
    public function sendToCompany(
        int $companyId,
        string $title,
        string $body,
        array $data = []
    ): array {
        $tokens = DeviceToken::where('company_id', $companyId)
            ->active()
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return ['success' => 0, 'failure' => 0];
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send to all active devices for a user.
     */
    public function sendToUser(
        User $user,
        string $title,
        string $body,
        array $data = []
    ): bool {
        $deviceToken = $user->deviceTokens()->active()->first();

        if (!$deviceToken) {
            return false;
        }

        return $this->sendToToken($deviceToken, $title, $body, $data);
    }

    /**
     * Send HTTP request to FCM.
     */
    private function send(array $payload, string $serverKey): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post(self::FCM_URL, $payload);

            if ($response->successful()) {
                $result = $response->json();
                
                if (isset($result['success']) && $result['success'] > 0) {
                    Log::info('FCM notification sent successfully', [
                        'success' => $result['success'],
                        'failure' => $result['failure'] ?? 0,
                    ]);
                    return true;
                }

                Log::warning('FCM notification failed', [
                    'response' => $result,
                ]);
                return false;
            }

            Log::error('FCM HTTP request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('FCM notification error', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Validate a device token with FCM.
     */
    public function validateToken(string $token): bool
    {
        $payload = [
            'to' => $token,
            'dry_run' => true,
        ];

        $serverKey = config('services.fcm.server_key');

        if (!$serverKey) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post(self::FCM_URL, $payload);

            if ($response->successful()) {
                $result = $response->json();
                return !isset($result['results'][0]['error']);
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
