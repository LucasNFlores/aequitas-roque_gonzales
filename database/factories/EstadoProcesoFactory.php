<?php

namespace Database\Factories;

use App\Models\EstadoProceso;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EstadoProceso>
 */
class EstadoProcesoFactory extends Factory
{
    protected $model = EstadoProceso::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'nombre' => $name,
            'slug' => Str::slug($name, '_'),
            'activo' => true,
            'posicion' => fake()->numberBetween(1, 100),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'activo' => false,
        ]);
    }
}
