<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    protected $fillable = [
        'quantity',
        'entry_date',
        'source',
        'age_days',
        'management_lot',
        'internal_id',
        'birth_date',
        'father_id',
        'mother_id',
        'genetic_id',
        'sex',
        'lote_sap',
        'act_curso',
        'order_number',
        'evento',
        'weight',
        'nave_id',
        'seccion_id',
        'corral',
        'stage_id',
        'feed_type',
        'status',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'birth_date' => 'date',
        'weight'     => 'decimal:2',
    ];

    // Relationships
    public function genetic()
    {
        return $this->belongsTo(Genetic::class);
    }

    public function nave()
    {
        return $this->belongsTo(Barn::class, 'nave_id');
    }

    public function seccion()
    {
        return $this->belongsTo(BarnSection::class, 'seccion_id');
    }

    public function pen()
    {
        return $this->belongsTo(Pen::class);
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    public function mother()
    {
        return $this->belongsTo(Animal::class, 'mother_id');
    }

    public function father()
    {
        return $this->belongsTo(Animal::class, 'father_id');
    }

    public function movements()
    {
        return $this->hasMany(Movement::class);
    }

    public function birthDetails()
    {
        return $this->hasMany(BirthDetail::class);
    }
}
