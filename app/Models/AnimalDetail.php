<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnimalDetail extends Model
{
    protected $fillable = [
        'animal_id',
        'source',
        'management_lot',
        'lote_sap',
        'act_curso',
        'order_number',
        'evento',
        'weight',
        'feed_type',
        'inbreeding',
        'breed_composition',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'inbreeding' => 'decimal:6',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}
