<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    protected $fillable = [
        'quantity',
        'entry_date',
        'internal_id',
        'birth_date',
        'father_id',
        'mother_id',
        'father_tag',
        'mother_tag',
        'genetic_id',
        'sex',
        'nave_id',
        'seccion_id',
        'corral',
        'stage_id',
        'status',
        'semen_id',
        'quarantine_batch_id',
        'official_id',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'birth_date' => 'date',
    ];

    // Relationships
    public function quarantineBatch()
    {
        return $this->belongsTo(QuarantineBatch::class);
    }
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

    public function detail() // Single detail record
    {
        return $this->hasOne(AnimalDetail::class);
    }

    public function semen()
    {
        return $this->belongsTo(Semen::class);
    }

    public function getAgeDaysAttribute()
    {
        if (!$this->birth_date) return 0;
        return now()->diffInDays($this->birth_date);
    }

    /**
     * Helper to create or update an ancestor record for pedigree purposes.
     */
    public static function ensureAncestor($tag, $geneticId, $sex, $motherId = null, $fatherId = null, $birthDate = null)
    {
        if (empty($tag)) return null;

        return self::updateOrCreate(
            ['internal_id' => mb_strtoupper($tag)],
            [
                'genetic_id' => $geneticId ?: null,
                'sex'        => $sex,
                'status'     => 'REFERENCIA',
                'mother_id'  => $motherId,
                'father_id'  => $fatherId,
                'birth_date' => $birthDate,
                'quantity'   => 0,
            ]
        );
    }
}
