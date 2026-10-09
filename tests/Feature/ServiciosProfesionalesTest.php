<?php

namespace Tests\Feature;

use App\Livewire\Profesionales\Servicios as ServiciosProfesionales;
use App\Models\Servicio;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiciosProfesionalesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_pantalla_y_navegacion_cu34_respetan_los_cinco_roles(): void
    {
        $allowedRoles = ['Coordinador', 'Directivo', 'Administrador'];

        foreach (['Secretario', 'Profesional', ...$allowedRoles] as $role) {
            $actor = $this->userWithRole($role);

            $navigation = $this->actingAs($actor)->get(route('profile.edit'));
            $screen = $this->actingAs($actor)->get(route('profesionales.servicios.index'));

            if (in_array($role, $allowedRoles, true)) {
                $navigation->assertOk()->assertSee('Servicios por profesional');
                $screen->assertOk()
                    ->assertSee('Asignar servicios a profesionales')
                    ->assertDontSee('Listado de Usuarios')
                    ->assertDontSee('Nuevo Usuario');
            } else {
                $navigation->assertOk()->assertDontSee('Servicios por profesional');
                $screen->assertForbidden();
            }

            if ($role === 'Coordinador') {
                $this->assertFalse($actor->can('listar_usuarios'));
                $this->actingAs($actor)->get(route('users.index'))->assertForbidden();
            }

            if (in_array($role, ['Directivo', 'Administrador'], true)) {
                $this->actingAs($actor)->get(route('users.index'))->assertOk();
            }
        }
    }

    public function test_peticiones_directas_persisten_asignaciones_y_retiros_segun_rol(): void
    {
        $professional = $this->userWithRole('Profesional');
        $assignedService = Servicio::factory()->create(['nombre' => 'Civil']);
        $replacementService = Servicio::factory()->create(['nombre' => 'Familia']);

        foreach (['Coordinador', 'Directivo', 'Administrador'] as $role) {
            $actor = $this->userWithRole($role);

            $this->actingAs($actor)->put(route('users.servicios.update', $professional), [
                'servicios' => [$assignedService->id],
            ])->assertRedirect();

            $this->assertDatabaseHas('user_servicios', [
                'user_id' => $professional->id,
                'servicio_id' => $assignedService->id,
            ]);

            $this->actingAs($actor)->put(route('users.servicios.update', $professional), [
                'servicios' => [$replacementService->id],
            ])->assertRedirect();

            $this->assertDatabaseMissing('user_servicios', [
                'user_id' => $professional->id,
                'servicio_id' => $assignedService->id,
            ]);
            $this->assertDatabaseHas('user_servicios', [
                'user_id' => $professional->id,
                'servicio_id' => $replacementService->id,
            ]);
        }

        $coordinator = $this->userWithRole('Coordinador');
        $this->actingAs($coordinator)->put(route('users.servicios.update', $professional), [
            'servicios' => [],
        ])->assertRedirect();
        $this->assertDatabaseMissing('user_servicios', [
            'user_id' => $professional->id,
            'servicio_id' => $replacementService->id,
        ]);

        foreach (['Secretario', 'Profesional'] as $role) {
            $actor = $this->userWithRole($role);
            $this->actingAs($actor)->put(route('users.servicios.update', $professional), [
                'servicios' => [$assignedService->id],
            ])->assertForbidden();
        }

        $this->assertDatabaseCount('user_servicios', 0);
    }

    public function test_solo_lista_profesionales_activos_y_rechaza_servicios_dados_de_baja(): void
    {
        $coordinator = $this->userWithRole('Coordinador');
        $professional = $this->userWithRole('Profesional');
        $inactiveProfessional = $this->userWithRole('Profesional');
        $inactiveProfessional->delete();
        $secretary = $this->userWithRole('Secretario');
        $activeService = Servicio::factory()->create();
        $deletedService = Servicio::factory()->create();
        $deletedService->delete();

        Livewire::actingAs($coordinator)
            ->test(ServiciosProfesionales::class)
            ->assertSee($professional->name)
            ->assertDontSee($inactiveProfessional->name)
            ->assertDontSee($secretary->name)
            ->call('openServices', $professional->id)
            ->set('selectedServicios', [$deletedService->id])
            ->call('saveServices')
            ->assertHasErrors(['selectedServicios.0']);

        $this->assertDatabaseMissing('user_servicios', [
            'user_id' => $professional->id,
            'servicio_id' => $deletedService->id,
        ]);

        $this->actingAs($coordinator)->put(route('users.servicios.update', $professional), [
            'servicios' => [$deletedService->id],
        ])->assertSessionHasErrors('servicios.0');

        Livewire::actingAs($coordinator)
            ->test(ServiciosProfesionales::class)
            ->call('openServices', $professional->id)
            ->set('selectedServicios', [$activeService->id])
            ->call('saveServices')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('user_servicios', [
            'user_id' => $professional->id,
            'servicio_id' => $activeService->id,
        ]);
    }
}
