<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Proceso;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Turno>
 */
class TurnoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'profesional_id' => User::factory(),
            'proceso_id' => fn (array $attributes): int => Proceso::factory()->create([
                'cliente_id' => $attributes['cliente_id'],
                'profesional_id' => $attributes['profesional_id'],
            ])->id,
            'fecha_hora' => fake()->dateTimeBetween('+1 hour', '+30 days'),
            'es_externo' => false,
            'detalle_externo' => null,
            'tipo' => 'consulta_inicial',
            'estado' => 'programado',
        ];
    }
}
