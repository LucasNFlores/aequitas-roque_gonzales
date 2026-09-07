<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = [
            [
                'nombre' => 'Juan',
                'apellido' => 'Pérez',
                'dni' => '30123456',
                'telefono' => '11-4567-8901',
                'correo' => 'juan.perez@example.com',
                'domicilio' => 'Av. Corrientes 1234, CABA',
                'fecha_nacimiento' => '1985-05-15',
            ],
            [
                'nombre' => 'María',
                'apellido' => 'González',
                'dni' => '31234567',
                'telefono' => '11-5678-9012',
                'correo' => 'maria.gonzalez@example.com',
                'domicilio' => 'Calle San Martín 567, CABA',
                'fecha_nacimiento' => '1990-08-22',
            ],
            [
                'nombre' => 'Carlos',
                'apellido' => 'Rodríguez',
                'dni' => '32345678',
                'telefono' => '11-6789-0123',
                'correo' => 'carlos.rodriguez@example.com',
                'domicilio' => 'Av. Santa Fe 890, CABA',
                'fecha_nacimiento' => '1978-11-30',
            ],
            [
                'nombre' => 'Ana',
                'apellido' => 'Martínez',
                'dni' => '33456789',
                'telefono' => '11-7890-1234',
                'correo' => 'ana.martinez@example.com',
                'domicilio' => 'Calle Belgrano 234, CABA',
                'fecha_nacimiento' => '1995-03-10',
            ],
            [
                'nombre' => 'Luis',
                'apellido' => 'Fernández',
                'dni' => '34567890',
                'telefono' => '11-8901-2345',
                'correo' => 'luis.fernandez@example.com',
                'domicilio' => 'Av. Rivadavia 456, CABA',
                'fecha_nacimiento' => '1982-07-18',
            ],
        ];

        foreach ($clientes as $cliente) {
            Cliente::firstOrCreate(['dni' => $cliente['dni']], $cliente);
        }
    }
}
