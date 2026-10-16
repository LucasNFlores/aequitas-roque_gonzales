<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Proceso;
use App\Models\Reporte;
use App\Models\Servicio;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\EstadoProcesoSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, EstadoProcesoSeeder::class]);
        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(12, 0));
    }

    public function test_dashboard_is_available_to_all_internal_roles_with_only_their_role_metrics(): void
    {
        foreach (['Secretario', 'Profesional', 'Coordinador', 'Directivo', 'Administrador'] as $role) {
            $user = $this->userWithRole($role);

            $response = $this->actingAs($user)->get(route('dashboard'));

            $response->assertOk()
                ->assertSee('Indicadores')
                ->assertSee('Procesos activos')
                ->assertSee(route('procesos.index'), false);

            if ($role === 'Directivo') {
                $response->assertSee('Usuarios activos')
                    ->assertSee(route('users.index'), false)
                    ->assertDontSee('Turnos próximos')
                    ->assertDontSee(route('turnos.agenda'), false)
                    ->assertSee('No hay pendientes operativos para este rol');
            } else {
                $response->assertSee('Turnos próximos')
                    ->assertSee(route('turnos.agenda'), false);
            }

            if ($role === 'Profesional') {
                $response->assertDontSee('data-process-state-chart')
                    ->assertSee('No hay procesos activos para mostrar.');
            } elseif (! in_array($role, ['Secretario', 'Coordinador'], true)) {
                $response->assertDontSee('Clientes registrados (30 días)')
                    ->assertDontSee('data-process-state-chart');
            }
        }
    }

    public function test_professional_metrics_and_follow_up_list_exclude_unassigned_process_data(): void
    {
        $professional = $this->userWithRole('Profesional');
        $otherProfessional = $this->userWithRole('Profesional');
        $coordinator = $this->userWithRole('Coordinador');
        $assigned = $this->processFor($professional, $coordinator, 'Caso profesional visible', 'en_proceso');
        $assignedSecondState = $this->processFor($professional, $coordinator, 'Caso propio en espera', 'en_espera');
        $other = $this->processFor($otherProfessional, $coordinator, 'Caso de otro profesional', 'admitido');
        Reporte::factory()->create([
            'proceso_id' => $assigned->id,
            'profesional_id' => $professional->id,
            'fecha' => now()->toDateString(),
        ]);
        Reporte::factory()->create([
            'proceso_id' => $other->id,
            'profesional_id' => $otherProfessional->id,
            'fecha' => now()->toDateString(),
        ]);
        Turno::factory()->create([
            'cliente_id' => $assigned->cliente_id,
            'proceso_id' => $assigned->id,
            'profesional_id' => $professional->id,
            'fecha_hora' => now()->addDay(),
        ]);
        Turno::factory()->create([
            'cliente_id' => $other->cliente_id,
            'proceso_id' => $other->id,
            'profesional_id' => $otherProfessional->id,
            'fecha_hora' => now()->addDay(),
        ]);

        $response = $this->actingAs($professional)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Caso profesional visible')
            ->assertSee('Caso propio en espera')
            ->assertDontSee('Caso de otro profesional')
            ->assertSee('Clientes en mis legajos')
            ->assertDontSee('Reportes recientes')
            ->assertSee('Turnos próximos')
            ->assertSee('data-process-state-chart')
            ->assertSee('Solo procesos asignados actualmente a tu usuario')
            ->assertSee('En Proceso')
            ->assertSee('En Espera')
            ->assertDontSee('Admitido');
        $this->assertSame(
            [$assigned->id, $assignedSecondState->id],
            Proceso::query()->visibleTo($professional)->orderBy('id')->pluck('id')->all(),
        );
    }

    public function test_professional_with_one_active_state_gets_accessible_values_without_a_chart(): void
    {
        $professional = $this->userWithRole('Profesional');
        $process = $this->processFor($professional, $this->userWithRole('Coordinador'), 'Único estado propio', 'iniciado');

        $this->actingAs($professional)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('data-process-state-chart')
            ->assertSee('Iniciado')
            ->assertSee('Único estado propio')
            ->assertSee(route('procesos.index'), false);
    }

    public function test_admission_and_assignment_queues_follow_action_permissions(): void
    {
        $secretary = $this->userWithRole('Secretario');
        $coordinator = $this->userWithRole('Coordinador');
        $professional = $this->userWithRole('Profesional');
        $awaitingAdmission = $this->processFor($professional, $coordinator, 'Caso para admitir', 'pendiente');
        $awaitingAssignment = $this->processFor($professional, $coordinator, 'Caso para asignar', 'admitido');
        $awaitingAssignment->forceFill(['profesional_id' => null])->save();

        $coordinatorView = $this->actingAs($coordinator)->get(route('dashboard'));
        $coordinatorView->assertOk()
            ->assertSee('Para admitir')
            ->assertSee('Caso para admitir')
            ->assertSee('Para asignar profesional')
            ->assertSee('Caso para asignar');

        $secretaryView = $this->actingAs($secretary)->get(route('dashboard'));
        $secretaryView->assertOk()
            ->assertDontSee('Para admitir')
            ->assertSee('Para asignar profesional')
            ->assertSee('Caso para asignar');
    }

    public function test_directivo_does_not_receive_agenda_or_document_links_and_agenda_route_remains_denied(): void
    {
        $directivo = $this->userWithRole('Directivo');
        $process = $this->processFor($this->userWithRole('Profesional'), $this->userWithRole('Coordinador'), 'Caso directivo visible', 'en_proceso');
        Turno::factory()->create([
            'cliente_id' => $process->cliente_id,
            'proceso_id' => $process->id,
            'profesional_id' => $process->profesional_id,
            'fecha_hora' => now()->addDay(),
        ]);

        $response = $this->actingAs($directivo)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Procesos activos')
            ->assertDontSee('Caso directivo visible')
            ->assertSee('Reportes recientes')
            ->assertDontSee('Próximos turnos')
            ->assertDontSee(route('turnos.agenda'), false)
            ->assertDontSee(route('procesos.documentos.index', ['proceso' => $process]), false);
        $this->get(route('turnos.agenda'))->assertForbidden();
    }

    public function test_dashboard_shows_empty_states_and_guest_cannot_read_metrics(): void
    {
        $coordinator = $this->userWithRole('Coordinador');

        $this->actingAs($coordinator)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('No hay casos en esta lista.')
            ->assertSee('No tienes turnos programados en los próximos siete días.')
            ->assertSee('No hay procesos activos para mostrar.')
            ->assertDontSee('data-process-state-chart');

        auth()->logout();
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_secretary_dashboard_counts_only_clients_registered_during_the_last_thirty_calendar_days(): void
    {
        $secretary = $this->userWithRole('Secretario');

        Cliente::factory()->create([
            'created_at' => now()->subDays(29)->startOfDay(),
        ]);
        Cliente::factory()->create([
            'created_at' => now()->subDays(30)->subSecond(),
        ]);
        $deletedClient = Cliente::factory()->create([
            'created_at' => now()->subDays(2),
        ]);
        $deletedClient->delete();

        $this->actingAs($secretary)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Clientes registrados (30 días)')
            ->assertSee(route('clientes.index'), false)
            ->assertViewHas('metrics', function (array $metrics): bool {
                $recentClientsMetric = collect($metrics)->firstWhere('label', 'Clientes registrados (30 días)');

                return $recentClientsMetric['value'] === 1;
            });
    }

    public function test_secretary_dashboard_shows_a_process_chart_for_multiple_states_and_list_fallbacks(): void
    {
        $secretary = $this->userWithRole('Secretario');
        $professional = $this->userWithRole('Profesional');
        $coordinator = $this->userWithRole('Coordinador');
        $this->processFor($professional, $coordinator, 'Proceso en gestión', 'en_proceso');

        $singleStateResponse = $this->actingAs($secretary)->get(route('dashboard'));
        $singleStateResponse->assertOk()
            ->assertDontSee('data-process-state-chart')
            ->assertSee('En Proceso');

        $this->processFor($professional, $coordinator, 'Proceso admitido', 'admitido');
        $multipleStatesResponse = $this->actingAs($secretary)->get(route('dashboard'));
        $multipleStatesResponse->assertOk()
            ->assertSee('data-process-state-chart', false)
            ->assertSee('Admitido')
            ->assertSee('En Proceso');

        $this->actingAs($coordinator)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-process-state-chart', false);
    }

    public function test_coordinator_dashboard_prioritizes_operational_metrics_and_preserves_authorized_navigation(): void
    {
        $coordinator = $this->userWithRole('Coordinador');
        $professional = $this->userWithRole('Profesional');

        $this->processFor($professional, $coordinator, 'Caso para admitir', 'pendiente');
        $awaitingAssignment = $this->processFor($professional, $coordinator, 'Caso para asignar', 'admitido');
        $awaitingAssignment->forceFill(['profesional_id' => null])->save();
        $followUpProcess = $this->processFor($professional, $coordinator, 'Caso en seguimiento', 'en_proceso');
        $this->processFor($professional, $coordinator, 'Caso finalizado', 'finalizado');
        $deletedProcess = $this->processFor($professional, $coordinator, 'Caso dado de baja', 'pendiente');
        $deletedProcess->delete();

        Reporte::factory()->create([
            'proceso_id' => $followUpProcess->id,
            'profesional_id' => $professional->id,
            'fecha' => now()->toDateString(),
        ]);

        Turno::factory()->create([
            'cliente_id' => $followUpProcess->cliente_id,
            'proceso_id' => $followUpProcess->id,
            'profesional_id' => $professional->id,
            'coordinador_id' => $coordinator->id,
            'fecha_hora' => now()->addDay(),
        ]);
        Turno::factory()->create([
            'cliente_id' => $followUpProcess->cliente_id,
            'proceso_id' => $followUpProcess->id,
            'profesional_id' => $professional->id,
            'coordinador_id' => $coordinator->id,
            'fecha_hora' => now()->addDays(7),
        ]);
        Turno::factory()->create([
            'cliente_id' => $followUpProcess->cliente_id,
            'proceso_id' => $followUpProcess->id,
            'profesional_id' => $professional->id,
            'coordinador_id' => $coordinator->id,
            'fecha_hora' => now()->addDays(8),
        ]);
        Turno::factory()->create([
            'cliente_id' => $followUpProcess->cliente_id,
            'proceso_id' => $followUpProcess->id,
            'profesional_id' => $professional->id,
            'coordinador_id' => $coordinator->id,
            'fecha_hora' => now()->addDay(),
            'estado' => 'cancelado',
        ]);

        $this->actingAs($coordinator)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Indicadores de gestión')
            ->assertSee('Procesos activos por estado')
            ->assertSee('data-process-state-chart', false)
            ->assertSee('Para admitir')
            ->assertSee('Caso para admitir')
            ->assertSee('Para asignar profesional')
            ->assertSee('Caso para asignar')
            ->assertSee('En seguimiento')
            ->assertSee('Caso en seguimiento')
            ->assertDontSee('Caso finalizado')
            ->assertDontSee('Caso dado de baja')
            ->assertDontSee('Reportes recientes')
            ->assertDontSee('Clientes registrados')
            ->assertDontSee('Clientes en mis legajos')
            ->assertSee(route('clientes.index'), false)
            ->assertSee(route('legajos.index'), false)
            ->assertViewHas('metrics', function (array $metrics): bool {
                $labels = collect($metrics)->pluck('label')->all();
                $activeProcesses = collect($metrics)->firstWhere('label', 'Procesos activos');

                return $labels === ['Procesos activos', 'Turnos próximos']
                    && $activeProcesses['value'] === 3;
            })
            ->assertViewHas('appointments', function (array $appointments): bool {
                return $appointments['count'] === 2;
            });
    }

    public function test_coordinator_chart_requires_two_active_states_and_keeps_accessible_values(): void
    {
        $coordinator = $this->userWithRole('Coordinador');
        $professional = $this->userWithRole('Profesional');
        $this->processFor($professional, $coordinator, 'Primer proceso activo', 'iniciado');

        $this->actingAs($coordinator)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('data-process-state-chart')
            ->assertSee('Iniciado')
            ->assertSee('Primer proceso activo');

        $this->processFor($professional, $coordinator, 'Segundo proceso activo', 'en_espera');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-process-state-chart', false)
            ->assertSee('Iniciado')
            ->assertSee('En Espera')
            ->assertSee('Valores de procesos activos por estado');
    }

    public function test_secretary_dashboard_explains_empty_process_states(): void
    {
        $this->actingAs($this->userWithRole('Secretario'))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('data-process-state-chart')
            ->assertSee('No hay procesos activos para mostrar.');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function processFor(User $professional, User $coordinator, string $name, string $state): Proceso
    {
        return Proceso::factory()->create([
            'cliente_id' => Cliente::factory(),
            'profesional_id' => $professional->id,
            'servicio_id' => Servicio::factory(),
            'coordinador_id' => $coordinator->id,
            'nombre' => $name,
            'estado' => $state,
        ]);
    }
}
