<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private const FCM_URL = 'https://fcm.googleapis.com/fcm/send';
    private const MAX_BATCH_SIZE = 500; // FCM limit

    /**
     * Send push notification to a specific device token.
     */
    public function sendToToken(
        DeviceToken $deviceToken,
        string $title,
        string $body,
        array $data = []
    ): array {
        $serverKey = config('services.fcm.server_key');

        if (!$serverKey) {
            Log::warning('FCM server key not configured');
            return $this->errorResult('FCM server key not configured');
        }

        $payload = $this->buildPayload(
            tokens: [$deviceToken->token],
            title: $title,
            body: $body,
            data: $data
        );

        return $this->sendWithRetry($payload, $serverKey);
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
        if (empty($tokens)) {
            return $this->successResult(0);
        }

        $serverKey = config('services.fcm.server_key');

        if (!$serverKey) {
            Log::warning('FCM server key not configured');
            return $this->errorResult('FCM server key not configured');
        }

        // Split into batches due to FCM limits
        $batches = array_chunk($tokens, self::MAX_BATCH_SIZE);
        $totalSuccess = 0;
        $totalFailure = 0;

        foreach ($batches as $batchTokens) {
            $payload = $this->buildPayload(
                tokens: $batchTokens,
                title: $title,
                body: $body,
                data: $data
            );

            $result = $this->sendWithRetry($payload, $serverKey);
            $totalSuccess += $result['success'];
            $totalFailure += $result['failure'];
        }

        return [
            'success' => $totalSuccess,
            'failure' => $totalFailure,
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
            return ['success' => 0, 'failure' => 0, 'message' => 'No active devices'];
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
    ): array {
        $deviceTokens = $user->deviceTokens()->active()->pluck('token')->toArray();

        if (empty($deviceTokens)) {
            return ['success' => 0, 'failure' => 0, 'message' => 'No active devices'];
        }

        return $this->sendToTokens($deviceTokens, $title, $body, $data);
    }

    /**
     * Send job assignment notification.
     */
    public function sendJobAssignment(
        User $user,
        int $locationId,
        string $locationName,
        string $address,
        string $scheduledDate,
        ?string $scheduledTime = null
    ): array {
        $title = 'New Job Assignment';
        $body = "You have been assigned to: {$locationName}";
        
        $data = [
            'type' => 'job_assignment',
            'location_id' => (string) $locationId,
            'location_name' => $locationName,
            'address' => $address,
            'scheduled_date' => $scheduledDate,
            'scheduled_time' => $scheduledTime ?? '',
            'click_action' => 'OPEN_LOCATION',
        ];

        return $this->sendToUser($user, $title, $body, $data);
    }

    /**
     * Send sync reminder notification.
     */
    public function sendSyncReminder(User $user, int $pendingJobs): array
    {
        $title = 'Pending Jobs to Sync';
        $body = "You have {$pendingJobs} job(s) waiting to be synced.";
        
        $data = [
            'type' => 'sync_reminder',
            'pending_count' => (string) $pendingJobs,
            'click_action' => 'OPEN_HOME',
        ];

        return $this->sendToUser($user, $title, $body, $data);
    }

    /**
     * Build FCM payload.
     */
    private function buildPayload(
        array $tokens,
        string $title,
        string $body,
        array $data
    ): array {
        $isMulticast = count($tokens) > 1;

        $payload = [
            $isMulticast ? 'registration_ids' : 'to' => $isMulticast ? $tokens : $tokens[0],
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
                'priority' => 'high',
                'icon' => 'notification_icon',
                'color' => '#3B82F6',
            ],
            'data' => array_merge($data, [
                'click_action' => $data['click_action'] ?? 'OPEN_APP',
                'timestamp' => now()->toIso8601String(),
            ]),
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'channel_id' => 'field_service_jobs',
                    'sound' => 'default',
                    'priority' => 'high',
                    'icon' => 'notification_icon',
                    'color' => '#3B82F6',
                ],
            ],
            'apns' => [
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'badge' => 1,
                        'content-available' => 1,
                    ],
                ],
            ],
        ];

        return $payload;
    }

    /**
     * Send with automatic retry.
     */
    private function sendWithRetry(array $payload, string $serverKey, int $maxRetries = 3): array
    {
        $attempt = 0;
        $lastError = null;

        while ($attempt < $maxRetries) {
            $attempt++;
            
            $result = $this->send($payload, $serverKey);
            
            if ($result['success'] > 0) {
                return $result;
            }

            // Check if it's a retryable error
            if ($this->isRetryableError($result)) {
                $delay = pow(2, $attempt); // Exponential backoff
                usleep($delay * 100000);
                $lastError = $result;
                continue;
            }

            return $result;
        }

        return $lastError ?? $this->errorResult('Max retries exceeded');
    }

    /**
     * Send HTTP request to FCM.
     */
    private function send(array $payload, string $serverKey): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post(self::FCM_URL, $payload);

            if ($response->successful()) {
                $result = $response->json();
                
                Log::info('FCM notification sent', [
                    'success' => $result['success'] ?? 0,
                    'failure' => $result['failure'] ?? 0,
                ]);

                return [
                    'success' => $result['success'] ?? 0,
                    'failure' => $result['failure'] ?? 0,
                    'message' => 'Notification sent',
                ];
            }

            Log::error('FCM HTTP request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return $this->errorResult("HTTP error: {$response->status()}");
        } catch (\Exception $e) {
            Log::error('FCM notification error', [
                'message' => $e->getMessage(),
            ]);

            return $this->errorResult($e->getMessage());
        }
    }

    /**
     * Check if error is retryable.
     */
    private function isRetryableError(array $result): bool
    {
        // Check for server errors (5xx)
        if (isset($result['status']) && $result['status'] >= 500) {
            return true;
        }

        // Check for specific retryable FCM errors
        $retryableErrors = [
            'UNAVAILABLE',
            'INTERNAL',
            'QUOTA_EXCEEDED',
        ];

        if (isset($result['results'])) {
            foreach ($result['results'] as $r) {
                if (isset($r['error']) && in_array($r['error'], $retryableErrors)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Validate a device token with FCM.
     */
    public function validateToken(string $token): array
    {
        $payload = [
            'to' => $token,
            'dry_run' => true,
        ];

        $serverKey = config('services.fcm.server_key');

        if (!$serverKey) {
            return ['valid' => false, 'message' => 'FCM not configured'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->timeout(10)->post(self::FCM_URL, $payload);

            if ($response->successful()) {
                $result = $response->json();
                $isValid = !isset($result['results'][0]['error']);
                
                return [
                    'valid' => $isValid,
                    'error' => $result['results'][0]['error'] ?? null,
                ];
            }

            return ['valid' => false, 'message' => 'Request failed'];
        } catch (\Exception $e) {
            return ['valid' => false, 'message' => $e->getMessage()];
        }
    }

    private function successResult(int $count): array
    {
        return ['success' => $count, 'failure' => 0, 'message' => 'Success'];
    }

    private function errorResult(string $message): array
    {
        return ['success' => 0, 'failure' => 1, 'message' => $message];
    }
}