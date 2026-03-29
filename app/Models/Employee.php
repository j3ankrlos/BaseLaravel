<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Employee extends Model
{
    protected $fillable = [
        'first_names',       // Nombres
        'last_names',        // Apellidos
        'national_id',       // Cédula de Identidad
        'phone_fixed',       // Teléfono Fijo
        'phone_mobile',      // Teléfono Móvil
        'state_id',          // ID del Estado
        'municipality_id',   // ID del Municipio
        'parish_id',         // ID de la Parroquia
        'city',              // Ciudad
        'address',           // Dirección de habitación
        'entry_date',        // Fecha de ingreso
        'file_number',       // Número de Ficha (Expediente)
        'cost_center_code',  // Código del Centro de Costo
        'area_id',           // ID del Área (ej. Sanidad Animal)
        'assigned_post_id',  // ID del Puesto Asignado (ej. Maternidad)
        'unit_id',           // ID de la Unidad de Producción
        'position_id',       // ID del Cargo (ej. Médico Veterinario)
        'payroll_type_id',   // ID del Tipo de Nómina
        'shift_id',          // ID del Turno (Guardia)
        'status',            // Estatus (Legacy)
        'estatus',           // Fijo / Contratado
        'estadonomina',      // Activo / Inactivo
        'current_status'     // Estatus Actual (Reposo, Vacaciones, etc.)
    ];

    /**
     * Accesor para la Ficha (file_number).
     * Reemplaza valores nulos o vacíos por un guion para la UI.
     */
    protected function fileNumber(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ?: '-',
            set: fn (?string $value) => $value ? str_pad($value, 4, '0', STR_PAD_LEFT) : null,
        );
    }

    /**
     * Accesor para el nombre completo.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->first_names} {$this->last_names}",
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

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