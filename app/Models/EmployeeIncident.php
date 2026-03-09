<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeIncident extends Model
{
    protected $fillable = [
        'employee_id',
        'attendance_status_id',
        'start_date',
        'end_date',
        'total_days',
        'observation',
        'status',
        'created_by'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendanceStatus()
    {
        return $this->belongsTo(AttendanceStatus::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
