<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PayrollTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Operario', 'code' => '1'],
            ['name' => 'Confidencial', 'code' => '3'],
            ['name' => 'Empleado', 'code' => '4'],
        ];

        foreach ($types as $type) {
            \App\Models\PayrollType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
