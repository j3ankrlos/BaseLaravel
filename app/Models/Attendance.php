<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id', 
        'attendance_date', 
        'attendance_status_id', 
        'observation',
        'check_in',
        'lunch_break_start',
        'lunch_break_end',
        'check_out',
        'total_hours'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function status()
    {
        return $this->belongsTo(AttendanceStatus::class, 'attendance_status_id');
    }

    /**
     * Calcula las horas trabajadas basándose en las marcas de tiempo (Forma Inteligente).
     */
    public function calculateWorkedHours()
    {
        if (!$this->check_in || !$this->check_out) {
            return 0;
        }

        $in = \Carbon\Carbon::parse($this->check_in);
        $out = \Carbon\Carbon::parse($this->check_out);
        
        // Diferencia bruta de trabajo (Salida - Entrada)
        $totalMinutes = abs($out->diffInMinutes($in, false));
        
        // Descontar almuerzo solo si hay solapamiento con el horario laboral
        $lunchMinutes = 0;
        if ($this->lunch_break_start && $this->lunch_break_end) {
            $lStart = \Carbon\Carbon::parse($this->lunch_break_start);
            $lEnd = \Carbon\Carbon::parse($this->lunch_break_end);
            
            // Determinar el inicio y fin del solapamiento
            $overlapStart = $in->greaterThan($lStart) ? $in : $lStart;
            $overlapEnd = $out->lessThan($lEnd) ? $out : $lEnd;
            
            if ($overlapStart->lessThan($overlapEnd)) {
                $lunchMinutes = abs($overlapEnd->diffInMinutes($overlapStart));
            }
        }
        
        $workedMinutes = max(0, $totalMinutes - $lunchMinutes);
        return round(abs($workedMinutes) / 60, 2);
    }
}
