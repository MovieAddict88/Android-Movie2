<?php

namespace App\Enums;

enum JobStatus: string
{
    case PENDING = 'pending';
    case ASSIGNED = 'assigned';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::ASSIGNED => 'Assigned',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::FAILED => 'Failed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => '#6B7280',
            self::ASSIGNED => '#3B82F6',
            self::IN_PROGRESS => '#F59E0B',
            self::COMPLETED => '#10B981',
            self::CANCELLED => '#EF4444',
            self::FAILED => '#DC2626',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'clock-outline',
            self::ASSIGNED => 'user-check',
            self::IN_PROGRESS => 'play-circle',
            self::COMPLETED => 'check-circle',
            self::CANCELLED => 'cancel',
            self::FAILED => 'alert-circle',
        };
    }

    public function canTransitionTo(JobStatus $newStatus): bool
    {
        return match ($this) {
            self::PENDING => in_array($newStatus, [self::ASSIGNED, self::CANCELLED]),
            self::ASSIGNED => in_array($newStatus, [self::IN_PROGRESS, self::CANCELLED]),
            self::IN_PROGRESS => in_array($newStatus, [self::COMPLETED, self::FAILED, self::CANCELLED]),
            self::COMPLETED, self::CANCELLED, self::FAILED => false,
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::PENDING, self::ASSIGNED, self::IN_PROGRESS]);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::COMPLETED, self::CANCELLED, self::FAILED]);
    }

    public static function activeStatuses(): array
    {
        return [
            self::PENDING->value,
            self::ASSIGNED->value,
            self::IN_PROGRESS->value,
        ];
    }

    public static function finalStatuses(): array
    {
        return [
            self::COMPLETED->value,
            self::CANCELLED->value,
            self::FAILED->value,
        ];
    }
}