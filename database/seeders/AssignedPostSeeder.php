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
            'MATERNIDAD',
            'MATERNIDAD EST',
            'MATERNIDAD EXP',
            'NOCHERO GRUPO A',
            'NOCHERO GRUPO B',
            'NOCHERO GRUPO C',
            'OFICINA',
            'REEMPLAZO',
            'REEMPLAZO EST',
            'REEMPLAZO EXP',
            'REPRODUCCION EST',
            'REPRODUCCION EXP',
            'SITIO I',
            'STUD DE MACHOS',
        ];

        foreach ($posts as $post) {
            \App\Models\AssignedPost::firstOrCreate(['name' => $post]);
        }
    }
}
