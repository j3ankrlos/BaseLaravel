<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarnSection extends Model
{
    use HasFactory;

    protected $fillable = ['barn_id', 'name'];

    public function barn()
    {
        return $this->belongsTo(Barn::class);
    }
}
