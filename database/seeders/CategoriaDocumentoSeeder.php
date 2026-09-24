<?php

namespace Database\Seeders;

use App\Models\CategoriaDocumento;
use Illuminate\Database\Seeder;

class CategoriaDocumentoSeeder extends Seeder
{
    public function run(): void
    {
        $base = [
            ['nombre' => 'Identidad', 'descripcion' => 'DNI, pasaporte y documentación personal'],
            ['nombre' => 'Comprobante ARCA', 'descripcion' => 'Comprobantes descargados de ARCA'],
            ['nombre' => 'Informe profesional', 'descripcion' => 'Reportes del profesional asignado'],
            ['nombre' => 'Contrato/Honorarios', 'descripcion' => 'Contratos y fijación de honorarios'],
            ['nombre' => 'Judicial', 'descripcion' => 'Escritos y resoluciones judiciales'],
        ];

        foreach ($base as $row) {
            CategoriaDocumento::firstOrCreate(
                ['nombre' => $row['nombre']],
                ['descripcion' => $row['descripcion'], 'activo' => true]
            );
        }
    }
}
