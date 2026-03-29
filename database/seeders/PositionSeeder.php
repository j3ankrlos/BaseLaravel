<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            'ANALISTA DE GESTION DE LA OPERACION',
            'COORDINADOR DE GRANJA',
            'ENCARGADO DE PRODUCCION',
            'MEDICO VETERINARIO',
            'EXPERTO EN BUENAS PRACTICAS REPRODUCTIVAS',
            'OPERARIO DE ALIMENTACION',
            'OPERARIO DE SERVICIOS GENERALES',
            'OPERARIO GENERAL',
            'OPERARIO GENERAL CONTROL DE ROEDORES',
            'SUPERVISOR DE LABORATORIO I',
            'SUPERVISOR DE PRODUCCION',
            'SUPERVISOR DE PRODUCCION II',
            'ASISTENTE ADMINISTRATIVO',
            'PASANTE INCES',
            'PASANTE UNIVERSITARIO',

        ];

        foreach ($positions as $cargo) {
            \App\Models\Position::firstOrCreate(['name' => $cargo]);
        }
    }
}
