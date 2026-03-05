<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContractTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['Fijo', 'Contratado'];
        foreach ($types as $name) {
            \App\Models\ContractType::firstOrCreate(['name' => $name]);
        }
    }
}
