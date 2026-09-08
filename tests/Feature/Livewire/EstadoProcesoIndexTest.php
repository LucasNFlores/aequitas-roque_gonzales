<?php

namespace Tests\Feature\Livewire;

use App\Livewire\EstadosProceso\Index as EstadosProcesoIndex;
use App\Models\EstadoProceso;
use App\Models\Proceso;
use App\Models\Servicio;
use App\Models\User;
use Database\Seeders\EstadoProcesoSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EstadoProcesoIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, EstadoProcesoSeeder::class]);
    }

    public function test_state_page_renders_livewire_and_alpine_interface(): void
    {
        $coordinator = $this->userWithRole('Coordinador');

        $this->actingAs($coordinator)
            ->get(route('estados-proceso.index'))
            ->assertOk()
            ->assertSee('wire:click="createEstado"', false)
            ->assertSee('x-data', false)
            ->assertSee('x-show="formOpen"', false)
            ->assertSee('x-show="deactivateOpen"', false)
            ->assertSee('Eliminar estado', false);
    }

    public function test_coordinator_can_create_and_edit_a_process_state(): void
    {
        $coordinator = $this->userWithRole('Coordinador');

        Livewire::actingAs($coordinator)
            ->test(EstadosProcesoIndex::class)
            ->call('createEstado')
            ->set('nombre', 'En revisión')
            ->call('saveEstado')
            ->assertHasNoErrors()
            ->assertDispatched('estado-guardado');

        $state = EstadoProceso::query()->where('slug', 'en_revision')->firstOrFail();

        $this->assertTrue($state->activo);
        $this->assertGreaterThan(7, $state->posicion);

        Livewire::actingAs($coordinator)
            ->test(EstadosProcesoIndex::class)
            ->call('editEstado', $state->id)
            ->set('nombre', 'En revisión legal')
            ->call('saveEstado')
            ->assertHasNoErrors();

        $this->assertSame('En revisión legal', $state->fresh()->nombre);
        $this->assertSame('en_revision', $state->fresh()->slug);
    }

    public function test_coordinator_can_reorder_and_deactivate_an_unused_state(): void
    {
        $coordinator = $this->userWithRole('Coordinador');
        $pending = EstadoProceso::query()->where('slug', 'pendiente')->firstOrFail();
        $admitted = EstadoProceso::query()->where('slug', 'admitido')->firstOrFail();

        Livewire::actingAs($coordinator)
            ->test(EstadosProcesoIndex::class)
            ->call('moveStateDown', $pending->id);

        $this->assertSame($admitted->posicion, $pending->fresh()->posicion);
        $this->assertSame(1, $admitted->fresh()->posicion);

        $unused = EstadoProceso::factory()->create([
            'nombre' => 'Sin uso',
            'slug' => 'sin_uso',
            'posicion' => 20,
        ]);

        Livewire::actingAs($coordinator)
            ->test(EstadosProcesoIndex::class)
            ->call('confirmDeactivate', $unused->id)
            ->call('deactivateEstado')
            ->assertHasNoErrors()
            ->assertDispatched('estado-desactivado');

        $this->assertFalse($unused->fresh()->activo);
    }

    public function test_state_used_by_a_process_cannot_be_deactivated(): void
    {
        $coordinator = $this->userWithRole('Coordinador');
        $professional = $this->userWithRole('Profesional');
        $state = EstadoProceso::query()->where('slug', 'en_proceso')->firstOrFail();

        Proceso::factory()->create([
            'profesional_id' => $professional->id,
            'coordinador_id' => $coordinator->id,
            'servicio_id' => Servicio::factory(),
            'estado' => $state->slug,
        ]);

        Livewire::actingAs($coordinator)
            ->test(EstadosProcesoIndex::class)
            ->call('confirmDeactivate', $state->id)
            ->call('deactivateEstado')
            ->assertHasErrors(['deactivate']);

        $this->assertTrue($state->fresh()->activo);
    }

    public function test_only_coordinator_and_administrator_can_manage_states(): void
    {
        $secretary = $this->userWithRole('Secretario');
        $directivo = $this->userWithRole('Directivo');
        $administrator = $this->userWithRole('Administrador');

        $this->actingAs($secretary)->get(route('estados-proceso.index'))->assertForbidden();
        $this->actingAs($directivo)->get(route('estados-proceso.index'))->assertForbidden();
        $this->actingAs($administrator)->get(route('estados-proceso.index'))->assertOk();
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
