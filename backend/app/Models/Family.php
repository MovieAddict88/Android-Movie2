<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    protected $fillable = ['name', 'join_code', 'owner_id'];

    public function children()
    {
        return $this->hasMany(Child::class);
    }

    public function chores()
    {
        return $this->hasMany(Chore::class);
    }
}
