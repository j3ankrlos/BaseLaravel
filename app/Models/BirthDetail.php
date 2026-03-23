<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BirthDetail extends Model
{
    protected $fillable = [
        'birth_id', 'generated_id', 'ear_id', 'weight',
        'teats_total', 'teats_left',
        'teats_behind_shoulder_left', 'teats_behind_shoulder_right',
        'sex', 'status', 'animal_id'
    ];

    public function birth()
    {
        return $this->belongsTo(Birth::class);
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}

