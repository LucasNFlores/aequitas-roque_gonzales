<?php

namespace Database\Factories;

use App\Models\Proceso;
use App\Models\Reporte;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reporte>
 */
class ReporteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'proceso_id' => Proceso::factory(),
            'profesional_id' => User::factory(),
            'contenido' => fake()->paragraph(),
            'fecha' => fake()->date(),
        ];
    }
}
