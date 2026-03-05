<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssignedPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = [
            'BIOSEGURIDAD',
            'COORDINACION',
            'MATERNIDAD',
            'MATERNIDAD EST',
            'MATERNIDAD EXP',
            'MATERNIDAD NOCHERO',
            'OFICINA',
            'REEMPLAZO',
            'REEMPLAZO EST',
            'REEMPLAZO EXP',
            'REPRODUCCION',
            'REPRODUCCION EST',
            'REPRODUCCION EXP',
            'STUD DE MACHOS',
        ];

        foreach ($posts as $post) {
            \App\Models\AssignedPost::firstOrCreate(['name' => $post]);
        }
    }
}
