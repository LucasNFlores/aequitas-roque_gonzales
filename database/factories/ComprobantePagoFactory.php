<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\ComprobantePago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComprobantePago>
 */
class ComprobantePagoFactory extends Factory
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
            'archivo_path' => 'comprobantes/'.fake()->uuid().'.pdf',
            'fecha_subida' => fake()->date(),
            'descripcion' => fake()->optional()->sentence(),
        ];
    }
}
