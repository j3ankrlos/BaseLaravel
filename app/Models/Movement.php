<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    protected $fillable = [
        'animal_id', 'movement_date', 'pic_cycle', 'pic_day',
        'movement_type', 'quantity', 'weight',
        'from_barn_section_id', 'from_pen_id',
        'to_barn_section_id', 'to_pen_id',
        'from_stage_id', 'to_stage_id',
        'reference_id', 'user_id', 'note', 'death_cause_id',
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

    public function fromBarnSection()
    {
        return $this->belongsTo(BarnSection::class, 'from_barn_section_id');
    }

    public function toBarnSection()
    {
        return $this->belongsTo(BarnSection::class, 'to_barn_section_id');
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

