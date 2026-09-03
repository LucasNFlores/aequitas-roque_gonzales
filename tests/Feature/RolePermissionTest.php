<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_seeder_creates_the_roles_and_permission_matrix(): void
    {
        $this->seed(RoleSeeder::class);

        $this->assertSame(5, Role::count());
        $this->assertTrue(Role::findByName('Administrador')->hasPermissionTo('gestionar_servicios'));
        $this->assertTrue(Role::findByName('Directivo')->hasPermissionTo('editar_roles'));
        $this->assertTrue(Role::findByName('Coordinador')->hasPermissionTo('gestionar_estados_proceso'));
        $this->assertTrue(Role::findByName('Profesional')->hasPermissionTo('registrar_reportes'));
        $this->assertFalse(Role::findByName('Profesional')->hasPermissionTo('gestionar_servicios'));
    }

    public function test_role_seeder_can_be_run_more_than_once(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->assertSame(5, Role::count());
        $this->assertSame(41, Role::findByName('Administrador')->permissions()->count());
    }

    public function test_permissions_control_the_existing_user_and_service_routes(): void
    {
        $this->seed(RoleSeeder::class);

        $directivo = User::factory()->create();
        $directivo->assignRole('Directivo');

        $profesional = User::factory()->create();
        $profesional->assignRole('Profesional');

        $this->actingAs($directivo)
            ->get(route('users.index'))
            ->assertOk();

        $this->actingAs($directivo)
            ->get(route('servicios.index'))
            ->assertOk();

        $this->actingAs($profesional)
            ->get(route('users.index'))
            ->assertForbidden();

        $this->actingAs($profesional)
            ->get(route('servicios.index'))
            ->assertForbidden();
    }

    public function test_directivo_can_assign_only_existing_roles(): void
    {
        $this->seed(RoleSeeder::class);

        $directivo = User::factory()->create();
        $directivo->assignRole('Directivo');

        $target = User::factory()->create();

        $this->actingAs($directivo)
            ->put(route('users.roles.update', $target), ['roles' => ['Secretario']])
            ->assertRedirect();

        $this->assertTrue($target->fresh()->hasRole('Secretario'));

        $this->actingAs($directivo)
            ->put(route('users.roles.update', $target), ['roles' => ['Rol inexistente']])
            ->assertSessionHasErrors('roles.0');
    }

    public function test_directivo_cannot_assign_or_remove_the_administrator_role(): void
    {
        $this->seed(RoleSeeder::class);

        $directivo = User::factory()->create();
        $directivo->assignRole('Directivo');

        $target = User::factory()->create();

        $this->actingAs($directivo)
            ->put(route('users.roles.update', $target), ['roles' => ['Administrador']])
            ->assertForbidden();

        $administrator = User::factory()->create();
        $administrator->assignRole('Administrador');

        $this->actingAs($directivo)
            ->put(route('users.roles.update', $administrator), ['roles' => ['Directivo']])
            ->assertForbidden();
    }

    public function test_administrator_is_allowed_by_the_super_admin_gate(): void
    {
        $this->seed(RoleSeeder::class);

        $administrator = User::factory()->create();
        $administrator->assignRole('Administrador');

        $this->assertTrue($administrator->can('permission_not_yet_defined'));
    }
}
