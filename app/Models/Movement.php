<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    protected $fillable = [
        'animal_id', 
        'movement_date',
        'movement_type', 
        'quantity', 
        'weight',
        'from_nave_id', 'to_nave_id',
        'from_seccion_id', 'to_seccion_id', 
        'from_corral', 'to_corral',
        'from_stage_id', 'to_stage_id',
        'reference_id', 
        'user_id', 
        'note', 
        'death_cause_id',
    ];

    protected $casts = [
        'movement_date' => 'datetime',
        'weight' => 'decimal:2',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromNave()
    {
        return $this->belongsTo(Barn::class, 'from_nave_id');
    }

    public function toNave()
    {
        return $this->belongsTo(Barn::class, 'to_nave_id');
    }

    public function fromSeccion()
    {
        return $this->belongsTo(BarnSection::class, 'from_seccion_id');
    }

    public function toSeccion()
    {
        return $this->belongsTo(BarnSection::class, 'to_seccion_id');
    }

    public function fromStage()
    {
        return $this->belongsTo(Stage::class, 'from_stage_id');
    }

    public function toStage()
    {
        return $this->belongsTo(Stage::class, 'to_stage_id');
    }

    public function deathCause()
    {
        return $this->belongsTo(DeathCause::class, 'death_cause_id');
    }
}

