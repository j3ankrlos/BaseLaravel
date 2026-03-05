<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = ['Sitio I', 'Sitio II', 'Sitio III'];
        foreach ($units as $unit) {
            \App\Models\Unit::firstOrCreate(['name' => $unit]);
        }
    }
}
