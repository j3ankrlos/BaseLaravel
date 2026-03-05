<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'first_names',
        'last_names',
        'national_id',
        'phone_fixed',
        'phone_mobile',
        'state',
        'municipality',
        'parish',
        'city',
        'address',
        'entry_date',
        'file_number',
        'cost_center_code',
        'area_id',
        'unit_id',
        'veterinarian_id',
        'status'
    ];
}
