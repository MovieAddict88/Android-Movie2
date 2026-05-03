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
            self::PENDING => 'gray',
            self::ASSIGNED => 'blue',
            self::IN_PROGRESS => 'yellow',
            self::COMPLETED => 'green',
            self::CANCELLED => 'red',
            self::FAILED => 'red',
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
}
