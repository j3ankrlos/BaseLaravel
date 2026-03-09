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
        'state_id',
        'municipality_id',
        'parish_id',
        'city',
        'address',
        'entry_date',
        'file_number',
        'cost_center_code',
        'area_id',
        'assigned_post_id',
        'unit_id',
        'position_id',
        'payroll_type_id',
        'shift_id',
        'status'
    ];

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }

    public function assignedPost()
    {
        return $this->belongsTo(AssignedPost::class, 'assigned_post_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function payrollType()
    {
        return $this->belongsTo(PayrollType::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
