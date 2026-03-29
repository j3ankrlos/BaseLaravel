<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Employee::whereNull('estatus')->update(['estatus' => 'Fijo']);
        \App\Models\Employee::whereNull('estadonomina')->update(['estadonomina' => 'Activo']);
    }
}
