<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeathCause extends Model
{
    protected $fillable = ['name', 'death_system_id'];

    public function system()
    {
        return $this->belongsTo(DeathSystem::class, 'death_system_id');
    }
}
