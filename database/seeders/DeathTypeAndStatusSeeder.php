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
            DB::table('death_types')->insert([
                'id' => $id,
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $animalStatuses = [
            1 => 'CELADOR',
            2 => 'DESCARTE',
            3 => 'DESTETADA',
            4 => 'GESTANTE',
            5 => 'LECHONA',
            6 => 'REPRODUCTOR',
        ];

        foreach ($animalStatuses as $id => $name) {
            DB::table('animal_statuses')->insert([
                'id' => $id,
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
