<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeathTypeAndStatusSeeder extends Seeder
{
    public function run(): void
    {
        $deathTypes = [
            1 => 'CESAREA',
            2 => 'NATURAL',
            3 => 'SACRIFICIO',
        ];

        foreach ($deathTypes as $id => $name) {
            DB::table('death_types')->updateOrInsert(
                ['id' => $id],
                ['name' => $name, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        $animalStatuses = [
            1 => 'CELADOR',
            2 => 'DESCARTE',
            3 => 'DESTETADA',
            4 => 'GESTANTE',
            5 => 'LECHONA',
            6 => 'REPRODUCTOR',
            7 => 'REFERENCIA',
            8 => 'REEMPLAZO',
        ];

        foreach ($animalStatuses as $id => $name) {
            DB::table('animal_statuses')->updateOrInsert(
                ['id' => $id],
                ['name' => $name, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
