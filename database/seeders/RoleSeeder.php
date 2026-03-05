<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * 1. LIMPIEZA DE CACHÉ
         * Spatie almacena los permisos en caché por rendimiento. 
         * Al sembrar la base de datos, reseteamos la caché para evitar conflictos.
         */
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        /**
         * 2. DEFINICIÓN DE PERMISOS BASE
         * Usamos una estructura de 'acción + recurso' para mantener el control granular.
         * Esto permite que el sistema crezca sin necesidad de refactorizar la lógica de seguridad.
         */
        $permissions = [
            // Permisos de Navegación y Dashboard
            'ver dashboard',
            
            // Permisos de Seguridad y Usuarios
            'ver usuarios',
            'crear usuarios',
            'editar usuarios',
            'eliminar usuarios',
            
            // Espacio para futuros módulos (Escalabilidad)
            // 'gestionar cerdos', 'ver reportes', 'gestionar inventario'
        ];

        // Creamos cada permiso en la base de datos
        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission]);
        }

        /**
         * 3. CREACIÓN Y CONFIGURACIÓN DE ROLES
         * Los roles agrupan permisos. El 'Super Admin' se maneja como un rol de acceso total.
         */
        $roleAdmin = Role::updateOrCreate(['name' => 'Super Admin']);
        // El Super Admin siempre obtiene todos los permisos existentes dinámicamente
        $roleAdmin->syncPermissions(Permission::all());

        /**
         * 4. GENERACIÓN DE USUARIO ADMINISTRADOR INICIAL
         * Creamos el primer acceso al sistema. updateOrCreate evita duplicados al re-ejecutar seeds.
         */
        $admin = User::updateOrCreate(
            ['email' => 'admin@granja.com'],
            [
                'name' => 'Administrador Sistema',
                'password' => Hash::make('password123!'), // Encriptación BCrypt robusta
                'email_verified_at' => now(),
            ]
        );

        // Asignamos el rol al usuario (Spatie se encarga de las relaciones en las tablas pivote)
        $admin->assignRole($roleAdmin);
    }
}
