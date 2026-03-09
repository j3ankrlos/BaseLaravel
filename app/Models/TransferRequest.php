<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferRequest extends Model
{
    protected $fillable = [
        'folio',
        'estado',
        'comentarios',
        'user_id_solicitante',
        'user_id_aprobador',
    ];

    public function details()
    {
        return $this->hasMany(TransferRequestDetail::class);
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'user_id_solicitante');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'user_id_aprobador');
    }
}
