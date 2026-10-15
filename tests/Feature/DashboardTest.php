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

            if ($role !== 'Secretario') {
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
        $other = $this->processFor($otherProfessional, $coordinator, 'Caso de otro profesional', 'en_proceso');
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
            ->assertDontSee('Caso de otro profesional')
            ->assertSee('Clientes en mis legajos')
            ->assertSee('Reportes recientes')
            ->assertSee('Turnos próximos');
        $this->assertSame([$assigned->id], Proceso::query()->visibleTo($professional)->pluck('id')->all());
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
            ->assertSee('No tienes turnos programados en los próximos siete días.');

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
            ->assertDontSee('data-process-state-chart');
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
