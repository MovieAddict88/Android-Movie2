<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redemption extends Model
{
    protected $fillable = [
        'child_id', 'reward_type', 'reward_name', 'cost_gems', 'status', 'meta'
    ];

    protected $casts = [
        'meta' => 'json',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}
