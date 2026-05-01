<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chore extends Model
{
    protected $fillable = [
        'family_id', 'name', 'description', 'gem_reward',
        'recurrence', 'requires_approval', 'due_date'
    ];

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function children()
    {
        return $this->belongsToMany(Child::class)->withPivot('status', 'completed_at')->withTimestamps();
    }
}
