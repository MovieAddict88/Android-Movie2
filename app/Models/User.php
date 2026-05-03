<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'phone',
        'role',
        'avatar_path',
        'is_active',
        'last_active_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_active_at' => 'datetime',
    ];

    /**
     * Role constants
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_WORKER = 'worker';
    public const ROLE_SUPERVISOR = 'supervisor';

    /**
     * Get the company that owns the user.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the locations assigned to this worker.
     */
    public function assignedLocations(): HasMany
    {
        return $this->hasMany(Location::class, 'assigned_to');
    }

    /**
     * Get the job logs for this user.
     */
    public function jobLogs(): HasMany
    {
        return $this->hasMany(JobLog::class, 'worker_id');
    }

    /**
     * Get the device tokens for this user.
     */
    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    /**
     * Get the primary device token for this user.
     */
    public function primaryDeviceToken(): HasOne
    {
        return $this->hasOne(DeviceToken::class)->latestOfMany();
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is worker.
     */
    public function isWorker(): bool
    {
        return $this->role === self::ROLE_WORKER;
    }

    /**
     * Check if user is supervisor.
     */
    public function isSupervisor(): bool
    {
        return $this->role === self::ROLE_SUPERVISOR;
    }

    /**
     * Get active device token for push notifications.
     */
    public function getActiveToken(): ?string
    {
        return $this->deviceTokens()
            ->where('is_active', true)
            ->latest()
            ->value('token');
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include workers.
     */
    public function scopeWorkers($query)
    {
        return $query->where('role', self::ROLE_WORKER);
    }

    /**
     * Get role label.
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_WORKER => 'Field Worker',
            self::ROLE_SUPERVISOR => 'Supervisor',
            default => ucfirst($this->role),
        };
    }
}
