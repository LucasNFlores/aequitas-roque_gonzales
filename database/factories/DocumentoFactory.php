<?php

namespace Database\Factories;

use App\Models\Proceso;
use App\Models\Documento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Documento>
 */
class DocumentoFactory extends Factory
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
            'archivo_path' => 'documentos/'.fake()->uuid().'.pdf',
            'tipo_documento' => fake()->randomElement(['demanda', 'poder', 'prueba']),
            'nombre' => fake()->sentence(3),
        ];
    }
}
