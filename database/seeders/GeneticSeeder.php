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
            ['name' => 'YORKSHIRE', 'code' => 'Y'],
            ['name' => 'YORKSHIRE-T', 'code' => 'YT'],
            ['name' => 'DUROC', 'code' => 'D'],
            ['name' => 'DUROC-T', 'code' => 'DT'],
            ['name' => 'LANDRACE', 'code' => 'L'],
            ['name' => 'LANDRACE-T', 'code' => 'LT'],
            ['name' => 'F1', 'code' => ''],
            ['name' => 'F1-T', 'code' => 'FT'],
            ['name' => 'F2', 'code' => ''],
        ];

        foreach ($genetics as $genetic) {
            \App\Models\Genetic::updateOrCreate(['name' => $genetic['name']], $genetic);
        }
    }
}
