<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin', 'apellido' => 'Sistema', 'email' => 'admin@example.com', 'role' => 'Administrador'],
            ['name' => 'Carlos', 'apellido' => 'Villalba', 'email' => 'carlos@example.com', 'role' => 'Secretario'],
            ['name' => 'Juan', 'apellido' => 'Perez', 'email' => 'juanperez@example.com', 'role' => 'Profesional'],
            ['name' => 'Coordinador', 'apellido' => 'Demo', 'email' => 'coordinador@example.com', 'role' => 'Coordinador'],
            ['name' => 'Directivo', 'apellido' => 'Demo', 'email' => 'directivo@example.com', 'role' => 'Directivo'],
        ];

        foreach ($users as $userData) {
            $user = User::withTrashed()->firstOrNew(['email' => $userData['email']]);

            if ($user->trashed()) {
                $user->restore();
            }

            $attributes = [
                'name' => $userData['name'],
                'apellido' => $userData['apellido'],
                'password' => '1234',
            ];

            if (! $user->exists) {
                $attributes['fecha_ingreso'] = today()->toDateString();
            }

            $user->fill($attributes)->save();
            $user->syncRoles([$userData['role']]);
        }
    }
}
