<?php

namespace Database\Seeders;

use App\Models\EstadoProceso;
use Illuminate\Database\Seeder;

class EstadoProcesoSeeder extends Seeder
{
    /**
     * @var list<array{nombre: string, slug: string, posicion: int}>
     */
    private const DEFAULT_STATES = [
        ['nombre' => 'Pendiente', 'slug' => 'pendiente', 'posicion' => 1],
        ['nombre' => 'Admitido', 'slug' => 'admitido', 'posicion' => 2],
        ['nombre' => 'Iniciado', 'slug' => 'iniciado', 'posicion' => 3],
        ['nombre' => 'En proceso', 'slug' => 'en_proceso', 'posicion' => 4],
        ['nombre' => 'Finalizado', 'slug' => 'finalizado', 'posicion' => 5],
        ['nombre' => 'En espera', 'slug' => 'en_espera', 'posicion' => 6],
        ['nombre' => 'Rechazado', 'slug' => 'rechazado', 'posicion' => 7],
    ];

    public function run(): void
    {
        foreach (self::DEFAULT_STATES as $state) {
            $estado = EstadoProceso::withTrashed()->firstOrNew(['slug' => $state['slug']]);

            if (! $estado->exists) {
                $estado->fill([...$state, 'activo' => true])->save();
            } elseif ($estado->trashed()) {
                $estado->restore();
            }
        }
    }
}
