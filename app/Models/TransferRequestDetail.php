<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferRequestDetail extends Model
{
    protected $fillable = [
        'transfer_request_id',
        'IdCodigo',
        'Codigo',
        'Producto',
        'UMB',
        'cantidad_solicitada',
        'cantidad_aprobada',
    ];

    public function transferRequest()
    {
        return $this->belongsTo(TransferRequest::class);
    }
}
