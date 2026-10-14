<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
            DB::table('categorias_documento')->updateOrInsert(
                ['nombre' => $row['nombre']],
                ['descripcion' => $row['descripcion'], 'activo' => true,
                 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
