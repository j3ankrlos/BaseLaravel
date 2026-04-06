<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuarantineBatch extends Model
{
    protected $fillable = [
        'batch_type',
        'genetic_id',
        'entry_date',
        'origin',
        'provider',
        'document_number',
        'total_quantity',
        'status'
    ];

    public function genetic()
    {
        return $this->belongsTo(Genetic::class);
    }

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(QuarantineItem::class, 'quarantine_batch_id');
    }

    public function animals()
    {
        return $this->hasMany(Animal::class, 'quarantine_batch_id');
    }
}
