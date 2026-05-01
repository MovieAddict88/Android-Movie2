<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    protected $fillable = [
        'child_id', 'feature', 'prompt_input', 'ai_response',
        'flagged_for_review', 'approved_by_parent'
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}
