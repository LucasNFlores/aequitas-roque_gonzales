<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Proceso;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proceso>
 */
class ProcesoFactory extends Factory
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
            'servicio_id' => Servicio::factory(),
            'coordinador_id' => User::factory(),
            'nombre' => fake()->sentence(4),
            'descripcion' => fake()->paragraph(),
            'fecha_inicio' => fake()->date(),
            'tipo' => fake()->randomElement(Proceso::TIPOS),
            'estado' => 'pendiente',
            'motivo_rechazo' => null,
        ];
    }
}
