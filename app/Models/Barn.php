<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barn extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'farm'];

    public function sections()
    {
        return $this->hasMany(BarnSection::class);
    }
}
