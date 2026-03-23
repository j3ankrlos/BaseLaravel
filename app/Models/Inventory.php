<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory'; // Pluralization bypass
    
    protected $fillable = [
        'type', 'identifier', 'management_lot', 'quantity', 'status',
        'parent_inventory_id', 'barn_section_id', 'pen_id', 'stage_id', 'genetic_id', 'sex',
        'entry_date', 'entry_pic_cycle', 'entry_pic_day', 'current_weight'
    ];

    protected $casts = [
        'entry_date' => 'date',
        'current_weight' => 'decimal:2',
    ];

    public function parent()
    {
        return $this->belongsTo(Inventory::class, 'parent_inventory_id');
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

    public function genetic()
    {
        return $this->belongsTo(Genetic::class);
    }
}
