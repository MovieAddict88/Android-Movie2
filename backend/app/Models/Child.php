<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    protected $fillable = [
        'family_id', 'name', 'avatar', 'age', 'reading_level',
        'interests', 'gems', 'ai_enabled', 'daily_ai_limit_minutes'
    ];

    protected $casts = [
        'interests' => 'json',
        'ai_enabled' => 'boolean',
    ];

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function chores()
    {
        return $this->belongsToMany(Chore::class)->withPivot('status', 'completed_at')->withTimestamps();
    }
}
