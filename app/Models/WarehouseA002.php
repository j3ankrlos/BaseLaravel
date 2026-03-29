<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseA002 extends Model
{
    protected $fillable = [
        'Codigo',
        'Producto',
        'UMB',
        'Clasificacion',
        'Stock',
        'StockMin',
        'SolicitudMin',
    ];
}
