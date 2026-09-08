<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Procesos\Index as ProcesosIndex;
use App\Models\Cliente;
use App\Models\EstadoProceso;
use App\Models\Proceso;
use App\Models\Servicio;
use App\Models\User;
use Database\Seeders\EstadoProcesoSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProcesoIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, EstadoProcesoSeeder::class]);
    }

    public function test_process_page_renders_livewire_filters_and_authorized_actions(): void
    {
        $coordinator = $this->userWithRole('Coordinador');
        $professional = $this->userWithRole('Profesional');
        $process = $this->processFor($professional, $coordinator, 'Proceso visible');

        $this->actingAs($coordinator)
            ->get(route('procesos.index'))
            ->assertOk()
            ->assertSee('wire:click="openHistory('.$process->id.')"', false)
            ->assertSee('wire:click="prepareStateChange('.$process->id.')"', false)
            ->assertSee('x-data', false)
            ->assertSee('wire:model.live', false);
    }

    public function test_coordinator_can_change_process_state_and_history_keeps_reason_and_user(): void
    {
        $coordinator = $this->userWithRole('Coordinador');
        $professional = $this->userWithRole('Profesional');
        $process = $this->processFor($professional, $coordinator);

        Livewire::actingAs($coordinator)
            ->test(ProcesosIndex::class)
            ->call('prepareStateChange', $process->id)
            ->assertSet('showStateModal', true)
            ->set('pendingEstado', 'en_proceso')
            ->set('motivoEstado', 'Se inició el trabajo del caso.')
            ->call('saveStateChange')
            ->assertHasNoErrors()
            ->assertDispatched('proceso-estado-actualizado')
            ->assertSet('showStateModal', false);

        $process->refresh();

        $this->assertSame('en_proceso', $process->estado);
        $change = $process->historialEstados()
            ->where('estado_nuevo', 'en_proceso')
            ->firstOrFail();

        $this->assertSame($coordinator->id, $change->usuario_id);
        $this->assertSame('Se inició el trabajo del caso.', $change->motivo);
        $this->assertSame('pendiente', $change->estado_anterior);
    }

    public function test_professional_is_limited_to_assigned_processes_and_history(): void
    {
        $professional = $this->userWithRole('Profesional');
        $otherProfessional = $this->userWithRole('Profesional');
        $coordinator = $this->userWithRole('Coordinador');
        $assigned = $this->processFor($professional, $coordinator, 'Proceso asignado');
        $other = $this->processFor($otherProfessional, $coordinator, 'Proceso ajeno');

        $this->assertTrue($professional->can('viewHistory', $assigned));
        $this->assertFalse($professional->can('viewHistory', $other));
        $this->assertSame([$assigned->id], Proceso::query()->visibleTo($professional)->pluck('id')->all());

        Livewire::actingAs($professional)
            ->test(ProcesosIndex::class)
            ->assertSee('Proceso asignado')
            ->assertDontSee('Proceso ajeno');
    }

    public function test_directivo_can_consult_history_but_cannot_change_process_state(): void
    {
        $directivo = $this->userWithRole('Directivo');
        $professional = $this->userWithRole('Profesional');
        $coordinator = $this->userWithRole('Coordinador');
        $process = $this->processFor($professional, $coordinator);

        $this->assertTrue($directivo->can('viewHistory', $process));
        $this->assertFalse($directivo->can('updateState', $process));

        Livewire::actingAs($directivo)
            ->test(ProcesosIndex::class)
            ->call('openHistory', $process->id)
            ->assertSet('showHistoryModal', true);
    }

    public function test_process_and_state_routes_follow_the_role_matrix(): void
    {
        $secretary = $this->userWithRole('Secretario');
        $coordinator = $this->userWithRole('Coordinador');
        $directivo = $this->userWithRole('Directivo');
        $administrator = $this->userWithRole('Administrador');

        $this->actingAs($secretary)->get(route('procesos.index'))->assertOk();
        $this->actingAs($coordinator)->get(route('estados-proceso.index'))->assertOk();
        $this->actingAs($directivo)->get(route('estados-proceso.index'))->assertForbidden();
        $this->actingAs($administrator)->get(route('estados-proceso.index'))->assertOk();
    }

    public function test_state_change_requires_a_different_active_state(): void
    {
        $coordinator = $this->userWithRole('Coordinador');
        $professional = $this->userWithRole('Profesional');
        $process = $this->processFor($professional, $coordinator);
        EstadoProceso::query()->where('slug', 'en_proceso')->update(['activo' => false]);

        Livewire::actingAs($coordinator)
            ->test(ProcesosIndex::class)
            ->call('prepareStateChange', $process->id)
            ->set('pendingEstado', 'en_proceso')
            ->call('saveStateChange')
            ->assertHasErrors(['pendingEstado']);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function processFor(User $professional, User $coordinator, string $name = 'Proceso de prueba'): Proceso
    {
        return Proceso::factory()->create([
            'cliente_id' => Cliente::factory(),
            'profesional_id' => $professional->id,
            'servicio_id' => Servicio::factory(),
            'coordinador_id' => $coordinator->id,
            'nombre' => $name,
        ]);
    }
}
