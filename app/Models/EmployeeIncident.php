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

    protected $appends = ['dynamic_status'];

    public function getDynamicStatusAttribute()
    {
        // Si el usuario ya lo marcó como cumplido en base de datos, siempre es Cumplido.
        if ($this->status === 'Cumplido') {
            return 'Cumplido';
        }

        // Si hoy es mayor que la fecha de fin (ya caducó) y no se ha marcado como cumplido, es Pendiente.
        if (\Carbon\Carbon::today()->gt(\Carbon\Carbon::parse($this->end_date))) {
            return 'Pendiente';
        }

        // Si la fecha la cubre o es futura (incluyendo inicio), está En Curso.
        return 'En Curso';
    }

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
