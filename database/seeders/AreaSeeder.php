<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            ['name' => 'BIOSEGURIDAD', 'cost_center' => 'P210040002'],
            ['name' => 'CEBA PORCINA', 'cost_center' => 'P210040008'],
            ['name' => 'CRIA Y LEVANTE PORCINO', 'cost_center' => 'P210040006'],
            ['name' => 'MATERNIDAD PORCINA', 'cost_center' => 'P210040005'],
            ['name' => 'PRECEBA PORCINA', 'cost_center' => 'P210040007'],
            ['name' => 'PRODUCCION DE SEMEN', 'cost_center' => 'P210040003'],
            ['name' => 'REPRODUCCION PORCINA', 'cost_center' => 'P210040004'],
            ['name' => 'SANIDAD ANIMAL', 'cost_center' => '0'],
        ];

        foreach ($areas as $area) {
            \App\Models\Area::firstOrCreate(
                ['name' => $area['name']],
                ['cost_center' => $area['cost_center']]
            );
        }
    }
}
