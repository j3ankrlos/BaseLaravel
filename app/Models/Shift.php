<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = ['code', 'name', 'working_day', 'start_time', 'end_time', 'total_hours', 'break_schedule', 'active'];
}
