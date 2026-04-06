<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semen extends Model
{
    protected $table = 'semens';

    protected $fillable = [
        'animal_id',
        'date',
        'semen_code',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}
