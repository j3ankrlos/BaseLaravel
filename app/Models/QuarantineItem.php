<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuarantineItem extends Model
{
    protected $fillable = [
        'quarantine_batch_id', 'internal_id', 'official_id', 'genetic_id',
        'birth_date', 'sex', 'lote', 'extra_status',
        
        // Location
        'barn_id', 'barn_section_id', 'pen_id',

        // Pedigree - Generation 1 (Parents)
        'f_tag', 'f_genetic_id', 'f_sex', 
        'm_tag', 'm_genetic_id', 'm_sex',

        // Pedigree - Generation 2 (Grandparents)
        'ff_tag', 'ff_genetic_id', 'ff_sex',
        'fm_tag', 'fm_genetic_id', 'fm_sex',
        'mf_tag', 'mf_genetic_id', 'mf_sex',
        'mm_tag', 'mm_genetic_id', 'mm_sex',

        // Pedigree - Generation 3 (Great Grandparents)
        'fff_tag', 'ffm_tag', 'fmf_tag', 'fmm_tag',
        'mff_tag', 'mfm_tag', 'mmf_tag', 'mmm_tag',

        'status', 'animal_id', 'quantity'
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function batch()
    {
        return $this->belongsTo(QuarantineBatch::class, 'quarantine_batch_id');
    }

    public function genetic()
    {
        return $this->belongsTo(Genetic::class, 'genetic_id');
    }

    public function fGenetic() { return $this->belongsTo(Genetic::class, 'f_genetic_id'); }
    public function mGenetic() { return $this->belongsTo(Genetic::class, 'm_genetic_id'); }
    public function ffGenetic() { return $this->belongsTo(Genetic::class, 'ff_genetic_id'); }
    public function fmGenetic() { return $this->belongsTo(Genetic::class, 'fm_genetic_id'); }
    public function mfGenetic() { return $this->belongsTo(Genetic::class, 'mf_genetic_id'); }
    public function mmGenetic() { return $this->belongsTo(Genetic::class, 'mm_genetic_id'); }

    public function barn() { return $this->belongsTo(Barn::class); }
    public function section() { return $this->belongsTo(BarnSection::class, 'barn_section_id'); }
    public function pen() { return $this->belongsTo(Pen::class); }

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}
