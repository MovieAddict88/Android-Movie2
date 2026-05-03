<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'type',
        'title',
        'body',
        'data',
        'status',
        'sent_at',
        'delivered_at',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    /**
     * Notification types.
     */
    public const TYPE_JOB_ASSIGNMENT = 'job_assignment';
    public const TYPE_JOB_COMPLETED = 'job_completed';
    public const TYPE_SYNC_REMINDER = 'sync_reminder';
    public const TYPE_LOCATION_UPDATE = 'location_update';

    /**
     * Status constants.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_FAILED = 'failed';

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that owns the notification.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope for unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for pending notifications.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Mark notification as delivered.
     */
    public function markAsDelivered(): void
    {
        $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivered_at' => now(),
        ]);
    }

    /**
     * Mark notification as failed.
     */
    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'data' => array_merge($this->data ?? [], ['error' => $reason]),
        ]);
    }

    /**
     * Create a job assignment notification.
     */
    public static function createJobAssignment(int $userId, int $companyId, int $locationId, string $locationName): self
    {
        return self::create([
            'user_id' => $userId,
            'company_id' => $companyId,
            'type' => self::TYPE_JOB_ASSIGNMENT,
            'title' => 'New Job Assignment',
            'body' => "You have been assigned to: {$locationName}",
            'data' => [
                'location_id' => $locationId,
                'location_name' => $locationName,
            ],
            'status' => self::STATUS_PENDING,
        ]);
    }

    /**
     * Create a sync reminder notification.
     */
    public static function createSyncReminder(int $userId, int $companyId, int $pendingCount): self
    {
        return self::create([
            'user_id' => $userId,
            'company_id' => $companyId,
            'type' => self::TYPE_SYNC_REMINDER,
            'title' => 'Pending Jobs to Sync',
            'body' => "You have {$pendingCount} job(s) waiting to be synced.",
            'data' => [
                'pending_count' => $pendingCount,
            ],
            'status' => self::STATUS_PENDING,
        ]);
    }
}