<?php

namespace App\Models;

use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'address',
        'latitude',
        'longitude',
        'radius',
        'assigned_to',
        'status',
        'notes',
        'metadata',
        'scheduled_date',
        'scheduled_time_start',
        'scheduled_time_end',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'radius' => 'integer',
        'status' => JobStatus::class,
        'metadata' => 'array',
        'scheduled_date' => 'date',
        'scheduled_time_start' => 'datetime:H:i',
        'scheduled_time_end' => 'datetime:H:i',
    ];

    /**
     * Default radius in meters.
     */
    public const DEFAULT_RADIUS = 100;

    /**
     * Get the company that owns the location.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the worker assigned to this location.
     */
    public function assignedWorker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the job logs for this location.
     */
    public function jobLogs(): HasMany
    {
        return $this->hasMany(JobLog::class);
    }

    /**
     * Get the latest job log for this location.
     */
    public function latestJobLog()
    {
        return $this->jobLogs()->latest()->first();
    }

    /**
     * Check if a given coordinate is within this location's geofence.
     */
    public function isWithinGeofence(float $lat, float $lng): bool
    {
        $distance = $this->calculateDistance(
            $this->latitude,
            $this->longitude,
            $lat,
            $lng
        );

        return $distance <= ($this->radius ?? self::DEFAULT_RADIUS);
    }

    /**
     * Calculate distance between two coordinates using Haversine formula.
     */
    public function calculateDistance(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $earthRadius = 6371000; // Earth's radius in meters

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lngDelta / 2) * sin($lngDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Scope for active locations.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            JobStatus::PENDING,
            JobStatus::ASSIGNED,
            JobStatus::IN_PROGRESS,
        ]);
    }

    /**
     * Scope for locations assigned to a specific worker.
     */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope for due today locations.
     */
    public function scopeDueToday($query)
    {
        return $query->whereDate('scheduled_date', now()->toDateString());
    }

    /**
     * Get Google Maps URL for this location.
     */
    public function getGoogleMapsUrlAttribute(): string
    {
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }
}
