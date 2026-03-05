<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollType extends Model
{
    protected $fillable = ['name', 'code', 'active'];
}
