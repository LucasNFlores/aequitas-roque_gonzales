<?php

namespace Database\Seeders;

use App\Models\Servicio;
use Illuminate\Database\Seeder;

class ServicioSeeder extends Seeder
{
    public function run(): void
    {
        $servicios = [
            ['nombre' => 'Consulta legal inicial', 'costo_servicio' => 2500.00],
            ['nombre' => 'Redacción de contrato', 'costo_servicio' => 8000.00],
            ['nombre' => 'Representación en juicio civil', 'costo_servicio' => 15000.00],
            ['nombre' => 'Asesoría en derecho familiar', 'costo_servicio' => 5000.00],
            ['nombre' => 'Trámite de sucesión', 'costo_servicio' => 12000.00],
        ];

        foreach ($servicios as $servicio) {
            Servicio::firstOrCreate(['nombre' => $servicio['nombre']], $servicio);
        }
    }
}
