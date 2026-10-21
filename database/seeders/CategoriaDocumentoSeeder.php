<?php

namespace Database\Seeders;

use App\Models\CategoriaDocumento;
use Illuminate\Database\Seeder;

class CategoriaDocumentoSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nombre' => 'Identidad', 'descripcion' => 'DNI, pasaporte y documentación personal'],
            ['nombre' => 'Comprobante ARCA', 'descripcion' => 'Comprobantes descargados de ARCA'],
            ['nombre' => 'Informe profesional', 'descripcion' => 'Reportes del profesional asignado'],
            ['nombre' => 'Contrato/Honorarios', 'descripcion' => 'Contratos y fijación de honorarios'],
            ['nombre' => 'Judicial', 'descripcion' => 'Escritos y resoluciones judiciales'],
        ];

        foreach ($categories as $categoryData) {
            $category = CategoriaDocumento::withTrashed()->firstOrNew(['nombre' => $categoryData['nombre']]);

            if (! $category->exists) {
                $category->fill([...$categoryData, 'activo' => true])->save();
            } elseif ($category->trashed()) {
                $category->restore();
            }
        }
    }
}
