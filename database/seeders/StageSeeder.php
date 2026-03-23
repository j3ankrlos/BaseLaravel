<?php

namespace Database\Seeders;

use App\Models\Stage;
use Illuminate\Database\Seeder;

class StageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stages = [
            ['name' => 'Maternidad'],
            ['name' => 'Recría'],
            ['name' => 'Levante'],
            ['name' => 'Pubertad'],
        ];

        foreach ($stages as $stage) {
            Stage::updateOrCreate(['name' => $stage['name']], $stage);
        }
    }
}
