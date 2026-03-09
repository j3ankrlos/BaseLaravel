<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            [
                'code' => 'T1',
                'name' => 'Turno Diurno',
                'working_day' => 'D',
                'start_time' => '07:30:00',
                'end_time' => '16:00:00',
                'total_hours' => 7.5,
                'break_schedule' => '12:00 pm 1:00 pm'
            ],
            [
                'code' => 'T2',
                'name' => 'Turno Mixto',
                'working_day' => 'DN',
                'start_time' => '15:00:00',
                'end_time' => '23:00:00',
                'total_hours' => 7.0,
                'break_schedule' => '7:00 pm 8:00 pm'
            ],
            [
                'code' => 'T3',
                'name' => 'Turno Nocturno',
                'working_day' => 'N',
                'start_time' => '23:00:00',
                'end_time' => '07:00:00',
                'total_hours' => 7.0,
                'break_schedule' => '2:00 am 3:00 am'
            ]
        ];

        foreach ($shifts as $shift) {
            \App\Models\Shift::firstOrCreate(['code' => $shift['code']], $shift);
        }
    }
}
