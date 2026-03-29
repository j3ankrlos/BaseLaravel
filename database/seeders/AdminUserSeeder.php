<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrador del Sistema',
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin123'),
                'status_id' => 1,
            ]
        );

        // Si existe un rol de Administrador, se lo asignamos
        $role = Role::where('name', 'Administrador')->orWhere('name', 'Admin')->first();
        if ($role) {
            $user->assignRole($role);
        }
    }
}
