<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('documentos')
            ->whereNull('categoria_id')
            ->select(['id', 'tipo_documento'])
            ->orderBy('id')
            ->chunkById(500, function (Collection $documentos): void {
                $nombres = $documentos
                    ->pluck('tipo_documento')
                    ->filter(fn (?string $nombre): bool => $nombre !== null && $nombre !== '')
                    ->unique()
                    ->all();

                if ($nombres === []) {
                    return;
                }

                $categoriasPorNombre = DB::table('categorias_documento')
                    ->whereIn('nombre', $nombres)
                    ->pluck('id', 'nombre');

                foreach ($documentos as $documento) {
                    $categoriaId = $categoriasPorNombre->get($documento->tipo_documento);

                    if ($categoriaId !== null) {
                        DB::table('documentos')
                            ->where('id', $documento->id)
                            ->whereNull('categoria_id')
                            ->update(['categoria_id' => $categoriaId]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Keep category associations: the original tipo_documento value remains intact.
    }
};
