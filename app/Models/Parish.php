<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parish extends Model
{
    protected $fillable = ['municipality_id', 'name'];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }
}
