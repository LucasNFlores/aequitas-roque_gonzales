<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $permisoEditarRoles = Permission::create(['name' => 'editar_roles']);

        // Creamos los roles base del sistema
        $rol_admin = Role::create(['name' => 'Administrador']);
        $rol_profesional = Role::create(['name' => 'Profesional']);
        $rol_coordinador = Role::create(['name' => 'Coordinador']);
        $rol_directivo = Role::create(['name' => 'Directivo']);

        // Asignamos permisos a los roles
        $rol_admin->givePermissionTo($permisoEditarRoles);

    }
}
