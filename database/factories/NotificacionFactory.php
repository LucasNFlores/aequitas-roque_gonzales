<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notificacion>
 */
class NotificacionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'cliente_id' => Cliente::factory(),
            'canal' => 'email',
            'mensaje' => fake()->sentence(),
            'fecha_envio' => fake()->dateTimeBetween('-30 days', 'now'),
            'estado' => 'enviado',
        ];
    }
}
