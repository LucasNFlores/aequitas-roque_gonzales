<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cliente>
 */
class ClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'dni' => fake()->unique()->numerify('########'),
            'telefono' => fake()->phoneNumber(),
            'correo' => fake()->safeEmail(),
            'domicilio' => fake()->address(),
            'fecha_nacimiento' => fake()->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
        ];
    }
}
