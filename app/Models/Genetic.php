<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Genetic extends Model
{
    protected $fillable = ['name', 'code', 'last_id_counter'];

    public function births()
    {
        return $this->hasMany(Birth::class);
    }}
