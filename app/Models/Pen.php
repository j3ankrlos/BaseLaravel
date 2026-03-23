<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pen extends Model
{
    protected $fillable = [
        'barn_section_id',
        'name',
        'capacity',
    ];

    public function barnSection()
    {
        return $this->belongsTo(BarnSection::class);
    }
}
