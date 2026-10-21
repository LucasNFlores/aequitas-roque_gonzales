<?php

namespace Database\Seeders;

use App\Models\CategoriaDocumento;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

        $this->backfillUnambiguousDocumentCategories();
    }

    private function backfillUnambiguousDocumentCategories(): void
    {
        DB::table('documentos')
            ->whereNull('categoria_id')
            ->select(['id', 'tipo_documento'])
            ->orderBy('id')
            ->chunkById(500, function (Collection $documentos): void {
                $categoryIdsByName = CategoriaDocumento::withTrashed()
                    ->whereIn('nombre', $documentos->pluck('tipo_documento')->filter()->unique())
                    ->pluck('id', 'nombre');

                foreach ($documentos as $documento) {
                    $categoryId = $categoryIdsByName->get($documento->tipo_documento);

                    if ($categoryId !== null) {
                        DB::table('documentos')
                            ->where('id', $documento->id)
                            ->whereNull('categoria_id')
                            ->update(['categoria_id' => $categoryId]);
                    }
                }
            });
    }
}
