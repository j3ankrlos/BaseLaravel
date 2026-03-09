<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Veterinarian extends Model
{
    protected $fillable = [
        'medical_college_code',
        'ministry_code',
        'registration_status',
        'unit_id',
        'employee_id',
        'initials'
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
}
