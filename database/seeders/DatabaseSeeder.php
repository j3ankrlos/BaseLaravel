<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PositionSeeder::class,
            AreaSeeder::class,
            AssignedPostSeeder::class,
            ShiftSeeder::class,
            ContractTypeSeeder::class,
            PayrollTypeSeeder::class,
            UnitSeeder::class,
            // VeterinarianSeeder y EmployeeSeeder se pueden añadir si se requiere data inicial
        ]);
    }
}
