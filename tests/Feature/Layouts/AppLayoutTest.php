<?php

namespace Tests\Feature\Layouts;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_layout_renders_a_vertical_sidebar(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('id="app-sidebar"', false);
        $response->assertSee('md:flex', false);
        $response->assertSee('flex-1 flex-col', false);
        $response->assertSee('md:w-64', false);
        $response->assertSee('h-screen w-64', false);
        $response->assertSee('md:sticky', false);
        $response->assertSee(route('profile.edit'), false);
        $response->assertSee(route('logout'), false);
    }
}
