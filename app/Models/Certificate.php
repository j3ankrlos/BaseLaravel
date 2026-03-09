<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fecha_registro',
        'vet_cedula',
        'vet_nombre',
        'vet_apellido',
        'vet_colegio_medico_codigo',
        'vet_ministerio_codigo',
        'vet_area_reproduccion',
        'animal_id',
        'lote',
        'raza',
        'estatus',
        'peso',
        'sexo',
        'nave',
        'seccion',
        'corral',
        'tipo_muerte',
        'causa_muerte',
        'sistema_involucrado',
        'reportado_por',
        'fecha_muerte',
        'evaluacion_externa',
        'evaluacion_interna',
        'arete_photo_path',
        'tatuaje_photo_path',
        'otra_photo_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
