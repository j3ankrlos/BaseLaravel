<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeathSystem extends Model
{
    protected $fillable = ['name'];

    public function causes()
    {
        return $this->hasMany(DeathCause::class);
    }
}
