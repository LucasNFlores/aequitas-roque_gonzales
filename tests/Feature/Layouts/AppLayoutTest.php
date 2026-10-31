<?php

namespace Tests\Feature\Layouts;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class AppLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_layout_renders_a_vertical_sidebar(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Secretario');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('id="app-sidebar"', false);
        $response->assertSee('md:flex', false);
        $response->assertSee('flex-1 flex-col', false);
        $response->assertSee('md:w-64', false);
        $response->assertSee('h-screen w-64', false);
        $response->assertSee('md:sticky', false);
        $response->assertSee('Secretario');
        $response->assertSee('data-theme-toggle', false);
        $response->assertSee('data-theme-toggle-label', false);
        $response->assertDontSee('Navegación');
        $response->assertSee('Inicio');
        $response->assertSee('Gestión jurídica');
        $response->assertSee('Informes');
        $response->assertDontSee('Administración');
        $response->assertSee('x-data="{ sectionOpen: true }"', false);
        $response->assertSee('x-data="{ sectionOpen: false }"', false);
        $response->assertSee('x-show="sectionOpen"', false);
        $response->assertSee('aria-controls="sidebar-group-legal"', false);
        $response->assertSee('aria-expanded="false"', false);
        $response->assertSee('x-show="accountMenuOpen"', false);
        $response->assertSee('id="sidebar-account-actions"', false);
        $response->assertSee('x-cloak', false);
        $response->assertDontSee($user->email);
        $response->assertSee(route('profile.edit'), false);
        $response->assertSee(route('logout'), false);
    }

    public function test_both_navigations_show_only_the_destinations_authorized_by_the_matrix(): void
    {
        $this->seed(RoleSeeder::class);

        $destinations = [
            ['label' => 'Usuarios y Roles', 'route' => 'users.index', 'roles' => ['Directivo', 'Administrador']],
            ['label' => 'Servicios por profesional', 'route' => 'profesionales.servicios.index', 'roles' => ['Coordinador', 'Directivo', 'Administrador']],
            ['label' => 'Servicios', 'route' => 'servicios.index', 'roles' => ['Directivo', 'Administrador']],
            ['label' => 'Clientes', 'route' => 'clientes.index', 'roles' => ['Secretario', 'Coordinador', 'Directivo', 'Administrador']],
            ['label' => 'Legajos', 'route' => 'legajos.index', 'roles' => ['Profesional', 'Coordinador', 'Directivo', 'Administrador']],
            ['label' => 'Procesos', 'route' => 'procesos.index', 'roles' => ['Secretario', 'Profesional', 'Coordinador', 'Directivo', 'Administrador']],
            ['label' => 'Comprobantes', 'route' => 'comprobantes.index', 'roles' => ['Secretario', 'Coordinador', 'Directivo', 'Administrador']],
            ['label' => 'Estados de proceso', 'route' => 'estados-proceso.index', 'roles' => ['Coordinador', 'Administrador']],
            ['label' => 'Categorías', 'route' => 'categorias.index', 'roles' => ['Coordinador', 'Administrador']],
            ['label' => 'Turnos', 'route' => 'turnos.index', 'roles' => ['Secretario', 'Profesional', 'Coordinador', 'Administrador']],
        ];

        foreach (['Secretario', 'Profesional', 'Coordinador', 'Directivo', 'Administrador'] as $role) {
            $user = User::factory()->create();
            $user->assignRole($role);
            $this->actingAs($user);

            $response = $this->get(route('dashboard'));
            $response->assertOk();
            $staticNavigation = Blade::render("@include('layouts.static-navigation')");

            $visibleSections = match ($role) {
                'Secretario' => ['Gestión jurídica', 'Informes'],
                'Profesional' => ['Gestión jurídica'],
                'Coordinador' => ['Gestión jurídica', 'Administración', 'Informes'],
                'Directivo' => ['Gestión jurídica', 'Administración', 'Informes'],
                'Administrador' => ['Gestión jurídica', 'Administración', 'Informes'],
            };

            foreach (['Gestión jurídica', 'Administración', 'Informes'] as $section) {
                if (in_array($section, $visibleSections, true)) {
                    $response->assertSee($section);
                } else {
                    $response->assertDontSee($section);
                }
            }

            foreach ($destinations as $destination) {
                $isAuthorized = in_array($role, $destination['roles'], true);
                if ($isAuthorized) {
                    $response->assertSee(route($destination['route']), false);
                    $response->assertSee($destination['label']);
                    $this->assertStringContainsString(route($destination['route']), $staticNavigation);
                } else {
                    $response->assertDontSee(route($destination['route']), false);
                    $this->assertStringNotContainsString(route($destination['route']), $staticNavigation);
                }
            }

            $response->assertSee(route('dashboard'), false);
            $response->assertSee(route('profile.edit'), false);
            $response->assertDontSee('Guía Técnica');
            $response->assertDontSee('/tutorial', false);
            $response->assertSee(route('logout'), false);
            $this->assertStringContainsString(route('profile.edit'), $staticNavigation);
            $this->assertStringNotContainsString('/tutorial', $staticNavigation);
        }
    }

    public function test_tutorial_route_is_not_available(): void
    {
        $this->get('/tutorial')->assertNotFound();
    }

    public function test_sidebar_expands_the_section_containing_the_active_route(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Secretario');

        $response = $this->actingAs($user)->get(route('clientes.index'));

        $response->assertOk();
        $response->assertDontSee('Navegación');
        $response->assertSee('x-data="{ sectionOpen: true }"', false);
        $response->assertSee('sidebar-group-legal', false);
    }

    public function test_navigation_destinations_remain_denied_to_roles_without_matrix_access(): void
    {
        $this->seed(RoleSeeder::class);

        $attempts = [
            ['role' => 'Secretario', 'route' => 'users.index'],
            ['role' => 'Profesional', 'route' => 'clientes.index'],
            ['role' => 'Coordinador', 'route' => 'servicios.index'],
            ['role' => 'Directivo', 'route' => 'turnos.index'],
        ];

        foreach ($attempts as $attempt) {
            $user = User::factory()->create();
            $user->assignRole($attempt['role']);

            $this->actingAs($user)->get(route($attempt['route']))->assertForbidden();
        }
    }
}
