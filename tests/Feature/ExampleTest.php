<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_root_redirects_guests_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_the_root_redirects_authenticated_users_to_dashboard(): void
    {
        $this->actingAs(User::factory()->make())
            ->get('/')
            ->assertRedirect(route('dashboard'));
    }
}
