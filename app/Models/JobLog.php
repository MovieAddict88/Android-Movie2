<?php

namespace App\Models;

use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class JobLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'location_id',
        'worker_id',
        'status',
        'started_at',
        'completed_at',
        'latitude_start',
        'longitude_start',
        'latitude_end',
        'longitude_end',
        'photo_path',
        'notes',
        'signature_path',
        'sync_id',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => JobStatus::class,
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Storage path for photos.
     */
    public const PHOTO_PATH = 'job-photos';
    public const SIGNATURE_PATH = 'signatures';

    /**
     * Get the company that owns the job log.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the location associated with this job log.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the worker who completed this job.
     */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    /**
     * Get the photo URL.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->photo_path);
    }

    /**
     * Get the signature URL.
     */
    public function getSignatureUrlAttribute(): ?string
    {
        if (!$this->signature_path) {
            return null;
        }

        return Storage::disk('public')->url($this->signature_path);
    }

    /**
     * Check if job was completed at the correct location.
     */
    public function wasCompletedAtLocation(): bool
    {
        if (!$this->location || !$this->latitude_end || !$this->longitude_end) {
            return false;
        }

        return $this->location->isWithinGeofence(
            $this->latitude_end,
            $this->longitude_end
        );
    }

    /**
     * Get the duration in minutes.
     */
    public function getDurationInMinutesAttribute(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->completed_at);
    }

    /**
     * Scope for completed jobs.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', JobStatus::COMPLETED);
    }

    /**
     * Scope for jobs by worker.
     */
    public function scopeByWorker($query, int $userId)
    {
        return $query->where('worker_id', $userId);
    }

    /**
     * Scope for jobs within date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('completed_at', [$startDate, $endDate]);
    }

    /**
     * Scope for jobs synced from mobile.
     */
    public function scopeSynced($query)
    {
        return $query->whereNotNull('sync_id');
    }
}
