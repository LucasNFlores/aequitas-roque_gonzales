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
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($professional)
            ->test(ProcesosIndex::class)
            ->call('openHistory', $other->id);
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

    public function test_coordinator_can_admit_pending_process_and_audit_the_transition(): void
    {
        $coordinator = $this->userWithRole('Coordinador');
        $professional = $this->userWithRole('Profesional');
        $process = $this->processFor($professional, $coordinator);

        Livewire::actingAs($coordinator)
            ->test(ProcesosIndex::class)
            ->call('admitProcess', $process->id)
            ->assertHasNoErrors()
            ->assertSee('Proceso admitido correctamente.');

        $process->refresh();
        $this->assertSame('admitido', $process->estado);
        $this->assertNull($process->motivo_rechazo);

        $change = $process->historialEstados()
            ->where('estado_nuevo', 'admitido')
            ->firstOrFail();

        $this->assertSame('pendiente', $change->estado_anterior);
        $this->assertSame($coordinator->id, $change->usuario_id);
        $this->assertSame('Admisión aprobada.', $change->motivo);
    }

    public function test_rejection_requires_and_persists_a_reason_in_the_process_and_history(): void
    {
        $coordinator = $this->userWithRole('Coordinador');
        $professional = $this->userWithRole('Profesional');
        $process = $this->processFor($professional, $coordinator);

        Livewire::actingAs($coordinator)
            ->test(ProcesosIndex::class)
            ->call('prepareRejection', $process->id)
            ->assertSet('showRejectionModal', true)
            ->set('motivoRechazo', '   ')
            ->call('saveRejection')
            ->assertHasErrors(['motivoRechazo']);

        $this->assertSame('pendiente', $process->fresh()->estado);

        Livewire::actingAs($coordinator)
            ->test(ProcesosIndex::class)
            ->call('prepareRejection', $process->id)
            ->set('motivoRechazo', '  Falta documentación respaldatoria.  ')
            ->call('saveRejection')
            ->assertHasNoErrors()
            ->assertSee('Proceso rechazado correctamente.');

        $process->refresh();
        $this->assertSame('rechazado', $process->estado);
        $this->assertSame('Falta documentación respaldatoria.', $process->motivo_rechazo);

        $change = $process->historialEstados()
            ->where('estado_nuevo', 'rechazado')
            ->firstOrFail();

        $this->assertSame('pendiente', $change->estado_anterior);
        $this->assertSame($coordinator->id, $change->usuario_id);
        $this->assertSame($process->motivo_rechazo, $change->motivo);
    }

    public function test_admission_and_rejection_permissions_follow_the_matrix(): void
    {
        $professional = $this->userWithRole('Profesional');
        $coordinator = $this->userWithRole('Coordinador');
        $secretary = $this->userWithRole('Secretario');
        $directivo = $this->userWithRole('Directivo');
        $administrator = $this->userWithRole('Administrador');
        $process = $this->processFor($professional, $coordinator);

        foreach ([$coordinator, $administrator] as $authorizedUser) {
            $this->assertTrue($authorizedUser->can('admit', $process));
            $this->assertTrue($authorizedUser->can('reject', $process));
        }

        foreach ([$professional, $secretary, $directivo] as $unauthorizedUser) {
            $this->assertFalse($unauthorizedUser->can('admit', $process));
            $this->assertFalse($unauthorizedUser->can('reject', $process));
        }

        Livewire::actingAs($directivo)
            ->test(ProcesosIndex::class)
            ->assertSee('Ver historial')
            ->assertDontSee('wire:click="admitProcess('.$process->id.')"', false)
            ->assertDontSee('wire:click="prepareRejection('.$process->id.')"', false)
            ->assertDontSee('wire:click="prepareProfessionalAssignment('.$process->id.')"', false);
    }

    public function test_secretary_can_assign_only_a_compatible_professional_and_updates_honorarios(): void
    {
        $secretary = $this->userWithRole('Secretario');
        $coordinator = $this->userWithRole('Coordinador');
        $professional = $this->userWithRole('Profesional');
        $service = Servicio::factory()->create(['costo_servicio' => 9200.50]);
        $professional->servicios()->attach($service);
        $process = $this->processFor($professional, $coordinator);
        $process->forceFill(['servicio_id' => $service->id, 'profesional_id' => null])->save();

        Livewire::actingAs($secretary)
            ->test(ProcesosIndex::class)
            ->call('prepareProfessionalAssignment', $process->id)
            ->assertSet('showAssignmentModal', true)
            ->set('selectedProfesionalId', (string) $professional->id)
            ->call('saveProfessionalAssignment')
            ->assertHasNoErrors()
            ->assertSee('Profesional asignado correctamente.');

        $process->refresh();
        $this->assertSame($professional->id, $process->profesional_id);
        $this->assertSame('9200.50', $process->honorarios);
    }

    public function test_assignment_rejects_incompatible_inactive_and_wrong_role_users(): void
    {
        $coordinator = $this->userWithRole('Coordinador');
        $directivo = $this->userWithRole('Directivo');
        $currentProfessional = $this->userWithRole('Profesional');
        $incompatibleProfessional = $this->userWithRole('Profesional');
        $inactiveProfessional = $this->userWithRole('Profesional');
        $wrongRoleUser = $this->userWithRole('Secretario');
        $service = Servicio::factory()->create();
        $currentProfessional->servicios()->attach($service);
        $process = $this->processFor($currentProfessional, $coordinator);
        $process->forceFill(['servicio_id' => $service->id])->save();
        $inactiveProfessional->delete();

        Livewire::actingAs($directivo)
            ->test(ProcesosIndex::class)
            ->call('prepareProfessionalAssignment', $process->id)
            ->assertForbidden();

        $component = Livewire::actingAs($coordinator)
            ->test(ProcesosIndex::class)
            ->call('prepareProfessionalAssignment', $process->id);

        $component
            ->set('selectedProfesionalId', (string) $inactiveProfessional->id)
            ->call('saveProfessionalAssignment')
            ->assertHasErrors(['selectedProfesionalId']);

        $component
            ->set('selectedProfesionalId', (string) $wrongRoleUser->id)
            ->call('saveProfessionalAssignment')
            ->assertHasErrors(['selectedProfesionalId']);

        $component
            ->set('selectedProfesionalId', (string) $incompatibleProfessional->id)
            ->call('saveProfessionalAssignment')
            ->assertHasErrors(['selectedProfesionalId']);

        $this->assertSame($currentProfessional->id, $process->fresh()->profesional_id);
    }

    public function test_coordinator_can_reassign_and_refresh_honorarios_from_the_service(): void
    {
        $coordinator = $this->userWithRole('Coordinador');
        $currentProfessional = $this->userWithRole('Profesional');
        $replacementProfessional = $this->userWithRole('Profesional');
        $service = Servicio::factory()->create(['costo_servicio' => 6100.00]);
        $currentProfessional->servicios()->attach($service);
        $replacementProfessional->servicios()->attach($service);
        $process = $this->processFor($currentProfessional, $coordinator);
        $process->forceFill([
            'servicio_id' => $service->id,
            'honorarios' => 1200.00,
        ])->save();

        Livewire::actingAs($coordinator)
            ->test(ProcesosIndex::class)
            ->call('prepareProfessionalAssignment', $process->id)
            ->set('selectedProfesionalId', (string) $replacementProfessional->id)
            ->call('saveProfessionalAssignment')
            ->assertHasNoErrors();

        $process->refresh();
        $this->assertSame($replacementProfessional->id, $process->profesional_id);
        $this->assertSame('6100.00', $process->honorarios);
    }

    public function test_process_filters_include_service_coordinator_and_date_range(): void
    {
        $coordinator = $this->userWithRole('Coordinador');
        $otherCoordinator = $this->userWithRole('Coordinador');
        $professional = $this->userWithRole('Profesional');
        $otherProfessional = $this->userWithRole('Profesional');
        $matchingService = Servicio::factory()->create(['nombre' => 'Servicio HU07 Coincidente']);
        $otherService = Servicio::factory()->create(['nombre' => 'Servicio HU07 Alternativo']);
        $matching = Proceso::factory()->create([
            'cliente_id' => Cliente::factory()->create(['nombre' => 'Cliente HU07 Filtro']),
            'profesional_id' => $professional->id,
            'servicio_id' => $matchingService->id,
            'coordinador_id' => $coordinator->id,
            'nombre' => 'Proceso HU07 Dentro del rango',
            'fecha_inicio' => '2026-08-15',
        ]);
        Proceso::factory()->create([
            'cliente_id' => Cliente::factory(),
            'profesional_id' => $otherProfessional->id,
            'servicio_id' => $otherService->id,
            'coordinador_id' => $otherCoordinator->id,
            'nombre' => 'Proceso HU07 Fuera del rango',
            'fecha_inicio' => '2026-09-15',
            'estado' => 'en_proceso',
        ]);

        Livewire::actingAs($coordinator)
            ->test(ProcesosIndex::class)
            ->set('search', 'Cliente HU07 Filtro')
            ->assertSee($matching->nombre)
            ->set('search', '')
            ->set('clienteId', (string) $matching->cliente_id)
            ->set('servicioId', (string) $matchingService->id)
            ->set('estadoFiltro', 'pendiente')
            ->set('coordinadorId', (string) $coordinator->id)
            ->set('profesionalId', (string) $professional->id)
            ->set('fechaDesde', '2026-08-01')
            ->set('fechaHasta', '2026-08-31')
            ->assertSee('Proceso HU07 Dentro del rango')
            ->assertDontSee('Proceso HU07 Fuera del rango');
    }

    public function test_professional_cannot_use_general_state_change_to_admit_or_reject(): void
    {
        $professional = $this->userWithRole('Profesional');
        $coordinator = $this->userWithRole('Coordinador');
        $process = $this->processFor($professional, $coordinator);

        Livewire::actingAs($professional)
            ->test(ProcesosIndex::class)
            ->call('prepareStateChange', $process->id)
            ->set('pendingEstado', 'rechazado')
            ->call('saveStateChange')
            ->assertHasErrors(['pendingEstado']);

        $this->assertSame('pendiente', $process->fresh()->estado);
        $this->assertNull($process->fresh()->motivo_rechazo);
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
