<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function __construct(
        private FcmService $fcmService
    ) {}

    /**
     * Send job assignment notification to a user.
     */
    public function sendJobAssignmentNotification(
        User $user,
        int $locationId,
        string $locationName,
        string $address,
        string $scheduledDate,
        ?string $scheduledTime = null
    ): bool {
        try {
            // Create notification record
            $notification = Notification::create([
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'type' => Notification::TYPE_JOB_ASSIGNMENT,
                'title' => 'New Job Assignment',
                'body' => "You have been assigned to: {$locationName}",
                'data' => [
                    'location_id' => $locationId,
                    'location_name' => $locationName,
                    'address' => $address,
                    'scheduled_date' => $scheduledDate,
                    'scheduled_time' => $scheduledTime,
                ],
                'status' => Notification::STATUS_PENDING,
            ]);

            // Send push notification
            $result = $this->fcmService->sendJobAssignment(
                $user,
                $locationId,
                $locationName,
                $address,
                $scheduledDate,
                $scheduledTime
            );

            if ($result['success'] > 0) {
                $notification->update([
                    'status' => Notification::STATUS_SENT,
                    'sent_at' => now(),
                ]);
                return true;
            }

            $notification->markAsFailed('FCM send failed');
            return false;
        } catch (\Exception $e) {
            Log::error('Failed to send job assignment notification', [
                'user_id' => $user->id,
                'location_id' => $locationId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send sync reminder notification.
     */
    public function sendSyncReminder(int $userId, int $companyId, int $pendingJobs): bool
    {
        try {
            $notification = Notification::create([
                'user_id' => $userId,
                'company_id' => $companyId,
                'type' => Notification::TYPE_SYNC_REMINDER,
                'title' => 'Pending Jobs to Sync',
                'body' => "You have {$pendingJobs} job(s) waiting to be synced.",
                'data' => [
                    'pending_count' => $pendingJobs,
                ],
                'status' => Notification::STATUS_PENDING,
            ]);

            $user = User::find($userId);
            if (!$user) {
                return false;
            }

            $result = $this->fcmService->sendSyncReminder($user, $pendingJobs);

            if ($result['success'] > 0) {
                $notification->update([
                    'status' => Notification::STATUS_SENT,
                    'sent_at' => now(),
                ]);
                return true;
            }

            $notification->markAsFailed('FCM send failed');
            return false;
        } catch (\Exception $e) {
            Log::error('Failed to send sync reminder', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Notify user of location status update.
     */
    public function sendLocationUpdateNotification(
        User $user,
        int $locationId,
        string $locationName,
        string $newStatus,
        string $message
    ): bool {
        try {
            $notification = Notification::create([
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'type' => Notification::TYPE_LOCATION_UPDATE,
                'title' => 'Location Updated',
                'body' => $message,
                'data' => [
                    'location_id' => $locationId,
                    'location_name' => $locationName,
                    'new_status' => $newStatus,
                ],
                'status' => Notification::STATUS_PENDING,
            ]);

            $result = $this->fcmService->sendToUser(
                $user,
                'Location Updated',
                $message,
                [
                    'type' => 'location_update',
                    'location_id' => (string) $locationId,
                    'new_status' => $newStatus,
                    'click_action' => 'OPEN_LOCATION',
                ]
            );

            if ($result['success'] > 0) {
                $notification->update([
                    'status' => Notification::STATUS_SENT,
                    'sent_at' => now(),
                ]);
                return true;
            }

            $notification->markAsFailed('FCM send failed');
            return false;
        } catch (\Exception $e) {
            Log::error('Failed to send location update notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get unread notification count for a user.
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)->unread()->count();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Cleanup old notifications.
     */
    public function cleanupOldNotifications(int $daysOld = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($daysOld))
            ->whereNotNull('read_at')
            ->delete();
    }
}