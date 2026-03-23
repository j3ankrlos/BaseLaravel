<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    protected $fillable = [
        // Core
        'type', 'primera', 'cola', 'saman',
        'quantity', 'entry_date', 'pic_cycle', 'pic_day',
        'source', 'management_lot', 'internal_id', 'identifier', 'parent_animal_id',
        // Physical & Genetic
        'genetic_id', 'sex', 'weight',
        // Locations
        'barn_id', 'barn_section_id', 'pen_id',
        // SAP & Status
        'lote_sap', 'activo_excel', 'status', 'order_number', 'feed_type',
        // App logic
        'stage_id', 'farm', 'age_days',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'weight'     => 'decimal:2',
    ];

    // Relationships
    public function genetic()
    {
        return $this->belongsTo(Genetic::class);
    }

    public function barn()
    {
        return $this->belongsTo(Barn::class);
    }

    public function barnSection()
    {
        return $this->belongsTo(BarnSection::class);
    }

    public function pen()
    {
        return $this->belongsTo(Pen::class);
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    public function parentAnimal()
    {
        return $this->belongsTo(Animal::class, 'parent_animal_id');
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
