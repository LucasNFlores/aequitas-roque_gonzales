<?php

namespace Database\Factories;

use App\Models\HistorialEstadoProceso;
use App\Models\Proceso;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HistorialEstadoProceso>
 */
class HistorialEstadoProcesoFactory extends Factory
{
    protected $model = HistorialEstadoProceso::class;

    public function definition(): array
    {
        return [
            'proceso_id' => Proceso::factory(),
            'usuario_id' => User::factory(),
            'estado_anterior' => 'pendiente',
            'estado_nuevo' => 'iniciado',
            'motivo' => fake()->sentence(),
            'fecha_cambio' => now(),
        ];
    }
}
