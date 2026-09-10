<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        // Registro público deshabilitado por CU25 / HU-05: solo Directivo/Administrador crean usuarios
        $response = $this->get('/register');

        $response->assertNotFound();
    }

    public function test_new_users_can_register(): void
    {
        // Intento de registro público debe fallar con 404 y no autenticar
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertNotFound();
        $this->assertGuest();
    }
}
