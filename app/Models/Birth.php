<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Birth extends Model
{
    protected $fillable = [
        'calendar_date', 'pic_cycle', 'pic_day', 'room', 'cage', 'mother_tag', 'parity', 'father_tag', 
        'lnv', 'maternity_lot', 'quantity', 'genetic_id', 'responsible_id', 'estado', 'pic_destete'
    ];

    protected $casts = [
        'calendar_date' => 'date',
        'estado' => 'integer',
        'pic_destete' => 'integer',
    ];

    public function genetic()
    {
        return $this->belongsTo(Genetic::class);
    }

    public function responsible()
    {
        return $this->belongsTo(Employee::class, 'responsible_id');
    }

    public function details()
    {
        return $this->hasMany(BirthDetail::class);
    }}
