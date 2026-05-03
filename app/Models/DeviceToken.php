<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceToken extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'company_id',
        'token',
        'device_name',
        'device_type',
        'app_version',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Device type constants
     */
    public const TYPE_ANDROID = 'android';
    public const TYPE_IOS = 'ios';

    /**
     * Get the user that owns the device token.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company that owns the device token.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Deactivate all other tokens for this user.
     */
    public function deactivateOthers(): void
    {
        $this->where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false]);
    }

    /**
     * Scope for active tokens.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if token is for Android device.
     */
    public function isAndroid(): bool
    {
        return $this->device_type === self::TYPE_ANDROID;
    }

    /**
     * Check if token is for iOS device.
     */
    public function isIos(): bool
    {
        return $this->device_type === self::TYPE_IOS;
    }
}
