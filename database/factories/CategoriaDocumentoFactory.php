<?php

namespace Database\Factories;

use App\Models\CategoriaDocumento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CategoriaDocumento>
 */
class CategoriaDocumentoFactory extends Factory
{
    protected $model = CategoriaDocumento::class;

    public function definition(): array
    {
        return [
            'nombre' => ucfirst(fake()->unique()->words(2, true)),
            'descripcion' => fake()->sentence(6),
            'activo' => true,
        ];
    }

    public function inactiva(): static
    {
        return $this->state(fn () => ['activo' => false]);
    }
}
