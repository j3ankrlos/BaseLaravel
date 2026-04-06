<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FeedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $feeds = [
            ['name' => 'CRECIMIENTO', 'provider' => 'TUNAL', 'code' => '3206007', 'cost_center' => 'PRO7002020'],
            ['name' => 'GESTANTE', 'provider' => 'TUNAL', 'code' => '3206019', 'cost_center' => 'PRO7002030'],
            ['name' => 'GESTANTE MEDICADO', 'provider' => 'TUNAL', 'code' => '3206021', 'cost_center' => 'PRO7002030'],
            ['name' => 'GESTANTE MEDICADO MN', 'provider' => 'TUNAL', 'code' => '3206035', 'cost_center' => null],
            ['name' => 'GESTANTE MN', 'provider' => 'TUNAL', 'code' => '3206037', 'cost_center' => null],
            ['name' => 'INICIADOR I MEDICADO MNL', 'provider' => 'TUNAL', 'code' => '3206032', 'cost_center' => null],
            ['name' => 'INICIADOR II MEDICADO MNL', 'provider' => 'TUNAL', 'code' => '3206033', 'cost_center' => null],
            ['name' => 'INICIADOR I', 'provider' => 'TUNAL', 'code' => '3206003', 'cost_center' => 'PRO7002020'],
            ['name' => 'INICIADOR II', 'provider' => 'TUNAL', 'code' => '3206005', 'cost_center' => 'PRO7002020'],
            ['name' => 'LACTANTE', 'provider' => 'TUNAL', 'code' => '3206023', 'cost_center' => 'PRO7002050'],
            ['name' => 'LACTANTE MEDICADA MNL', 'provider' => 'TUNAL', 'code' => '3206034', 'cost_center' => null],
            ['name' => 'LECHONA I', 'provider' => 'TUNAL', 'code' => '3206017', 'cost_center' => 'PRO7002020'],
            ['name' => 'LECHONA II', 'provider' => 'TUNAL', 'code' => '3206018', 'cost_center' => 'PRO7002020'],
            ['name' => 'PRE INICIADOR', 'provider' => 'TUNAL', 'code' => '3206001', 'cost_center' => 'PRO7002020'],
            ['name' => 'PRE INICIADOR FASE 0', 'provider' => 'TUNAL', 'code' => '3206030', 'cost_center' => 'PRO7002020'],
            ['name' => 'PRE INICIADOR FASE 0 MEDICADO MNL', 'provider' => 'TUNAL', 'code' => '3206036', 'cost_center' => null],
            ['name' => 'PRE INICIADOR FASE 1 MEDICADO MNL', 'provider' => 'TUNAL', 'code' => '3206031', 'cost_center' => null],
            ['name' => 'REPRODUCTOR VERRACO', 'provider' => 'TUNAL', 'code' => '3206027', 'cost_center' => 'PRO7002060'],
        ];

        foreach ($feeds as $feed) {
            \App\Models\Feed::updateOrCreate(['code' => $feed['code']], $feed);
        }
    }
}
