<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseA006 extends Model
{
    protected $fillable = [
        'IdCodigo',
        'Codigo',
        'Producto',
        'UMB',
        'Clasificacion',
        'Stock',
        'StockMin',
        'SolicitudMin',
    ];
}
