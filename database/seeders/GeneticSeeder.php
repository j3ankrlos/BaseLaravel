<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GeneticSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genetics = [
            ['name' => 'YORK', 'code' => 'Y'],
            ['name' => 'YORK-T', 'code' => 'YT'],
            ['name' => 'DUROC', 'code' => 'D'],
            ['name' => 'DUROC-T', 'code' => 'DT'],
            ['name' => 'LANDRACE', 'code' => 'L'],
            ['name' => 'LANDRA-T', 'code' => 'LT'],
            ['name' => 'F1', 'code' => ''],
            ['name' => 'F1-T', 'code' => 'F'],
        ];

        foreach ($genetics as $genetic) {
            \App\Models\Genetic::updateOrCreate(['name' => $genetic['name']], $genetic);
        }
    }
}
