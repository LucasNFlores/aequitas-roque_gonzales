<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin', 'email' => 'admin@example.com', 'role' => 'Administrador'],
            ['name' => 'Carlos Villalba', 'email' => 'carlos@example.com', 'role' => 'Secretario'],
            ['name' => 'Juan Perez', 'email' => 'juanperez@example.com', 'role' => 'Profesional'],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                ['name' => $userData['name'], 'password' => '1234'],
            );

            $user->syncRoles([$userData['role']]);
        }
    }
}
