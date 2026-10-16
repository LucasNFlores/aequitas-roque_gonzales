<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\HistorialEstadoProceso;
use App\Models\Proceso;
use App\Models\Reporte;
use App\Models\Servicio;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\EstadoProcesoSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
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
            $expectedHeading = match ($role) {
                'Directivo' => 'Resumen de gestión',
                'Administrador' => 'Panorama operativo global',
                default => 'Indicadores',
            };

            $response = $this->actingAs($user)->get(route('dashboard'));

            $response->assertOk()
                ->assertSee($expectedHeading)
                ->assertSee('Procesos activos')
                ->assertSee(route('procesos.index'), false);

            if ($role === 'Directivo') {
                $response->assertSee('Cuentas vigentes')
                    ->assertSee(route('users.index'), false)
                    ->assertDontSee('Turnos próximos')
                    ->assertDontSee(route('turnos.agenda'), false)
                    ->assertSee('Procesos activos por servicio')
                    ->assertDontSee('Pendientes y seguimiento')
                    ->assertDontSee('No hay pendientes operativos para este rol');
            } else {
                $response->assertSee('Turnos próximos')
                    ->assertSee(route('turnos.agenda'), false)
                    ->assertDontSee('Procesos activos por servicio');
            }

            if ($role === 'Profesional') {
                $response->assertDontSee('data-process-state-chart')
                    ->assertSee('No hay procesos activos para mostrar.');
            } elseif ($role === 'Directivo') {
                $response->assertDontSee('Clientes registrados (30 días)')
                    ->assertDontSee('data-process-state-chart');
            } elseif ($role === 'Administrador') {
                $response->assertSee('Cuentas internas vigentes')
                    ->assertSee('Gestión operativa')
                    ->assertDontSee('Procesos activos por servicio')
                    ->assertDontSee('Clientes vigentes')
                    ->assertDontSee('Reportes (últimos 30 días)');
            }
        }
    }

    public function test_administrator_dashboard_uses_global_process_state_counts_approved_queues_and_exact_periods(): void
    {
        $administrator = $this->userWithRole('Administrador');
        $professional = $this->userWithRole('Profesional');
        $otherProfessional = $this->userWithRole('Profesional');
        $coordinator = $this->userWithRole('Coordinador');

        $pendingProcess = $this->processFor($professional, $coordinator, 'Caso global pendiente', 'pendiente');
        $awaitingAssignment = $this->processFor($otherProfessional, $coordinator, 'Caso global para asignar', 'admitido');
        $awaitingAssignment->forceFill(['profesional_id' => null])->save();
        $activeProcess = $this->processFor($otherProfessional, $coordinator, 'Caso global en proceso', 'en_proceso');
        $this->processFor($professional, $coordinator, 'Caso global finalizado', 'finalizado');
        $this->processFor($professional, $coordinator, 'Caso global rechazado', 'rechazado');
        $deletedProcess = $this->processFor($professional, $coordinator, 'Caso global dado de baja', 'pendiente');
        $deletedProcess->delete();

        Turno::factory()->create([
            'cliente_id' => $activeProcess->cliente_id,
            'proceso_id' => $activeProcess->id,
            'profesional_id' => $otherProfessional->id,
            'fecha_hora' => now(),
        ]);
        Turno::factory()->create([
            'cliente_id' => $pendingProcess->cliente_id,
            'proceso_id' => $pendingProcess->id,
            'profesional_id' => $professional->id,
            'fecha_hora' => now()->addDays(7),
        ]);
        Turno::factory()->create([
            'cliente_id' => $activeProcess->cliente_id,
            'proceso_id' => $activeProcess->id,
            'profesional_id' => $otherProfessional->id,
            'fecha_hora' => now()->addDays(7)->addSecond(),
        ]);
        Turno::factory()->create([
            'cliente_id' => $activeProcess->cliente_id,
            'proceso_id' => $activeProcess->id,
            'profesional_id' => $otherProfessional->id,
            'fecha_hora' => now()->addDay(),
            'estado' => 'cancelado',
        ]);

        $deletedUser = User::factory()->create();
        $deletedUser->delete();

        $response = $this->actingAs($administrator)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Panorama operativo global')
            ->assertSee('Procesos activos globales por estado')
            ->assertSee('data-process-state-chart', false)
            ->assertSee('data-values="[1,1,1]"', false)
            ->assertSee('Valores globales de procesos activos por estado')
            ->assertSee('Para admitir')
            ->assertSee('Caso global pendiente')
            ->assertSee('Para asignar profesional')
            ->assertSee('Caso global para asignar')
            ->assertSee('Turnos próximos')
            ->assertSee('Cuentas internas vigentes')
            ->assertDontSee('Procesos activos por servicio')
            ->assertDontSee('En seguimiento')
            ->assertDontSee('Clientes vigentes')
            ->assertDontSee('Reportes (últimos 30 días)')
            ->assertViewHas('metrics', function (array $metrics): bool {
                $metricsByLabel = collect($metrics)->keyBy('label');

                return $metricsByLabel->get('Procesos activos globales')['value'] === 3
                    && $metricsByLabel->get('Turnos próximos')['value'] === 2
                    && $metricsByLabel->get('Cuentas internas vigentes')['value'] === User::query()->count();
            })
            ->assertViewHas('processStates', [
                ['label' => 'Admitido', 'value' => 1],
                ['label' => 'En Proceso', 'value' => 1],
                ['label' => 'Pendiente', 'value' => 1],
            ])
            ->assertViewHas('queues', function (array $queues): bool {
                $queuesByTitle = collect($queues)->keyBy('title');

                return $queuesByTitle->keys()->all() === ['Para admitir', 'Para asignar profesional']
                    && $queuesByTitle->get('Para admitir')['count'] === 1
                    && $queuesByTitle->get('Para asignar profesional')['count'] === 1;
            })
            ->assertViewHas('activeProcessesByService', []);
    }

    public function test_administrator_dashboard_explains_empty_global_process_and_appointment_states(): void
    {
        $administrator = $this->userWithRole('Administrador');

        $this->actingAs($administrator)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('data-process-state-chart')
            ->assertSee('No hay procesos activos globales para mostrar.')
            ->assertSee('No hay casos en esta lista.')
            ->assertSee('No tienes turnos programados en los próximos siete días.')
            ->assertSee('Cuentas internas vigentes');
    }

    public function test_directivo_metrics_and_service_chart_use_approved_visible_data_and_exact_periods(): void
    {
        $directivo = $this->userWithRole('Directivo');
        $professional = $this->userWithRole('Profesional');
        $coordinator = $this->userWithRole('Coordinador');
        $consultoria = Servicio::factory()->create(['nombre' => 'Consultoría']);
        $familia = Servicio::factory()->create(['nombre' => 'Familia']);

        $activeConsultoria = $this->processFor($professional, $coordinator, 'Caso activo uno', 'en_proceso');
        $activeConsultoria->update(['servicio_id' => $consultoria->id]);
        $activeConsultoriaTwo = $this->processFor($professional, $coordinator, 'Caso activo dos', 'en_espera');
        $activeConsultoriaTwo->update(['servicio_id' => $consultoria->id]);
        $activeFamilia = $this->processFor($professional, $coordinator, 'Caso activo tres', 'pendiente');
        $activeFamilia->update(['servicio_id' => $familia->id]);

        $finalizedProcess = $this->processFor($professional, $coordinator, 'Caso finalizado', 'finalizado');
        $finalizedProcess->update(['servicio_id' => $familia->id]);
        $rejectedProcess = $this->processFor($professional, $coordinator, 'Caso rechazado', 'rechazado');
        $rejectedProcess->update(['servicio_id' => $familia->id]);
        $deletedProcess = $this->processFor($professional, $coordinator, 'Caso eliminado', 'en_proceso');
        $deletedProcess->update(['servicio_id' => $familia->id]);
        $deletedProcess->delete();

        Reporte::factory()->create(['proceso_id' => $activeConsultoria->id, 'profesional_id' => $professional->id, 'fecha' => '2026-09-16']);
        Reporte::factory()->create(['proceso_id' => $activeConsultoria->id, 'profesional_id' => $professional->id, 'fecha' => '2026-10-15']);
        Reporte::factory()->create(['proceso_id' => $activeConsultoria->id, 'profesional_id' => $professional->id, 'fecha' => '2026-09-15']);
        Reporte::factory()->create(['proceso_id' => $activeConsultoria->id, 'profesional_id' => $professional->id, 'fecha' => '2026-10-16']);
        Reporte::factory()->create(['proceso_id' => $activeConsultoria->id, 'profesional_id' => $professional->id, 'fecha' => '2026-10-10'])->delete();

        $deletedClient = Cliente::factory()->create();
        $deletedClient->delete();
        $deletedUser = User::factory()->create();
        $deletedUser->delete();

        $response = $this->actingAs($directivo)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Resumen estratégico de datos autorizados para CU 40.')
            ->assertSee('Procesos activos por servicio')
            ->assertSee('Consultoría')
            ->assertSee('Familia')
            ->assertSee('data-horizontal-bar-chart', false)
            ->assertSee('data-values="[2,1]"', false)
            ->assertDontSee('Procesos activos por estado')
            ->assertDontSee('Pendientes y seguimiento');

        $metrics = collect($response->viewData('metrics'))->keyBy('label');
        $recentReportDates = Reporte::query()
            ->visibleTo($directivo)
            ->whereDate('fecha', '>=', '2026-09-16')
            ->whereDate('fecha', '<=', '2026-10-15')
            ->orderBy('fecha')
            ->get()
            ->map(fn (Reporte $report): string => $report->fecha->toDateString())
            ->all();

        $this->assertSame(3, $metrics->get('Procesos activos vigentes')['value']);
        $this->assertSame(Cliente::query()->visibleTo($directivo)->count(), $metrics->get('Clientes vigentes')['value']);
        $this->assertSame(['2026-09-16', '2026-10-15'], $recentReportDates);
        $this->assertSame(2, $metrics->get('Reportes (últimos 30 días)')['value']);
        $this->assertSame(User::query()->count(), $metrics->get('Cuentas vigentes')['value']);
        $this->assertSame([
            ['label' => 'Consultoría', 'value' => 2],
            ['label' => 'Familia', 'value' => 1],
        ], $response->viewData('activeProcessesByService'));
        $this->assertSame([], $response->viewData('processStates'));
        $this->assertSame([], $response->viewData('queues'));
        $this->assertNull($response->viewData('appointments'));
    }

    public function test_directivo_history_counts_distinct_processes_by_state_and_filters_inclusive_periods(): void
    {
        $directivo = $this->userWithRole('Directivo');
        $professional = $this->userWithRole('Profesional');
        $coordinator = $this->userWithRole('Coordinador');
        $firstProcess = $this->processFor($professional, $coordinator, 'Histórico uno', 'en_proceso');
        $secondProcess = $this->processFor($professional, $coordinator, 'Histórico dos', 'en_proceso');
        $thirdProcess = $this->processFor($professional, $coordinator, 'Histórico tres', 'en_proceso');
        $fourthProcess = $this->processFor($professional, $coordinator, 'Histórico eliminado', 'en_proceso');
        $this->moveInitialHistoryOutsidePeriods([$firstProcess, $secondProcess, $thirdProcess, $fourthProcess]);

        $this->addStateChange($firstProcess, 'en_proceso', '2026-07-15 00:00:00');
        $this->addStateChange($firstProcess, 'en_proceso', '2026-10-10 09:00:00');
        $this->addStateChange($firstProcess, 'finalizado', '2026-10-11 09:00:00');
        $this->addStateChange($firstProcess, 'rechazado', '2026-10-12 09:00:00');
        $this->addStateChange($secondProcess, 'en_proceso', '2026-07-14 23:59:59');
        $this->addStateChange($thirdProcess, 'finalizado', '2026-04-15 00:00:00');
        $this->addStateChange($fourthProcess, 'en_espera', '2026-04-14 23:59:59');
        $fourthProcess->delete();

        $defaultResponse = $this->actingAs($directivo)->get(route('dashboard'));
        $defaultResponse->assertOk()
            ->assertSee('Cambios de estado en procesos')
            ->assertSee('La fecha usada es la fecha de cambio de estado.')
            ->assertSee('Un proceso puede aparecer en varios estados')
            ->assertSee('Últimos 3 meses')
            ->assertSee('data-historical-state-chart', false)
            ->assertSee('data-values="[1]"', false);
        $this->assertSame([['label' => 'En Proceso', 'value' => 1]], $defaultResponse->viewData('historicalProcessStates'));

        $threeMonthsWithTerminalStates = $this->get(route('dashboard', [
            'period' => '3_months',
            'include_finalizado' => '1',
            'include_rechazado' => '1',
        ]));
        $this->assertSame([
            ['label' => 'En Proceso', 'value' => 1],
            ['label' => 'Finalizado', 'value' => 1],
            ['label' => 'Rechazado', 'value' => 1],
        ], $threeMonthsWithTerminalStates->viewData('historicalProcessStates'));

        $sixMonths = $this->get(route('dashboard', ['period' => '6_months']));
        $this->assertSame([['label' => 'En Proceso', 'value' => 2]], $sixMonths->viewData('historicalProcessStates'));

        $customRange = $this->get(route('dashboard', [
            'period' => 'custom',
            'from' => '2026-04-15',
            'to' => '2026-07-15',
            'include_finalizado' => '1',
        ]));
        $this->assertSame([
            ['label' => 'En Proceso', 'value' => 2],
            ['label' => 'Finalizado', 'value' => 1],
        ], $customRange->viewData('historicalProcessStates'));
        $customRange->assertSee('Rango personalizado: 15/04/2026 al 15/07/2026.');

        $metrics = collect($defaultResponse->viewData('metrics'))->keyBy('label');
        $this->assertCount(4, $metrics);
        $this->assertSame(3, $metrics->get('Procesos activos vigentes')['value']);
    }

    public function test_administrator_can_consult_history_but_other_roles_cannot(): void
    {
        $administrator = $this->userWithRole('Administrador');
        $process = $this->processFor($this->userWithRole('Profesional'), $this->userWithRole('Coordinador'), 'Proceso interno', 'en_proceso');
        $this->moveInitialHistoryOutsidePeriods([$process]);
        $this->addStateChange($process, 'iniciado', '2026-10-01 10:00:00');

        $this->actingAs($administrator)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Cambios de estado en procesos')
            ->assertSee('data-historical-state-chart', false);

        foreach (['Secretario', 'Profesional', 'Coordinador'] as $role) {
            $this->actingAs($this->userWithRole($role))
                ->get(route('dashboard', ['period' => 'invalid', 'from' => 'future']))
                ->assertOk()
                ->assertDontSee('Cambios de estado en procesos')
                ->assertDontSee('data-historical-state-chart');
        }
    }

    public function test_directivo_history_respects_the_process_visibility_scope(): void
    {
        $directivo = $this->userWithRole('Directivo');
        Role::findByName('Directivo')->revokePermissionTo(['listar_filtrar_procesos', 'consultar_legajos']);
        $process = $this->processFor($this->userWithRole('Profesional'), $this->userWithRole('Coordinador'), 'Caso fuera de scope', 'en_proceso');
        $this->addStateChange($process, 'iniciado', '2026-10-01 10:00:00');

        $response = $this->actingAs($directivo)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('No hay cambios de estado para el período y los filtros seleccionados.')
            ->assertDontSee('data-historical-state-chart');
        $this->assertSame([], $response->viewData('historicalProcessStates'));
    }

    public function test_directivo_history_rejects_invalid_custom_date_ranges(): void
    {
        $directivo = $this->userWithRole('Directivo');
        $invalidRanges = [
            ['period' => 'invalid'],
            ['period' => 'custom'],
            ['period' => 'custom', 'from' => '2026-10-10', 'to' => '2026-10-09'],
            ['period' => 'custom', 'from' => '2026-10-16', 'to' => '2026-10-16'],
            ['period' => 'custom', 'from' => 'not-a-date', 'to' => '2026-10-10'],
        ];

        foreach ($invalidRanges as $range) {
            $this->actingAs($directivo)
                ->from(route('dashboard'))
                ->get(route('dashboard', $range))
                ->assertRedirect(route('dashboard'))
                ->assertSessionHasErrors();
        }
    }

    public function test_directivo_history_explains_empty_results_and_restores_default_filters(): void
    {
        $directivo = $this->userWithRole('Directivo');

        $response = $this->actingAs($directivo)->get(route('dashboard', ['period' => '6_months']));

        $response->assertOk()
            ->assertSee('No hay cambios de estado para el período y los filtros seleccionados.')
            ->assertSee('Restablecer filtros')
            ->assertSee(route('dashboard', ['period' => '3_months']), false)
            ->assertDontSee('data-historical-state-chart');
        $this->assertSame([], $response->viewData('historicalProcessStates'));
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
            ->assertSee('Reportes (últimos 30 días)')
            ->assertDontSee('Próximos turnos')
            ->assertDontSee(route('turnos.agenda'), false)
            ->assertDontSee(route('procesos.documentos.index', ['proceso' => $process]), false);
        $this->get(route('turnos.agenda'))->assertForbidden();
        $this->get(route('procesos.documentos.index', ['proceso' => $process]))->assertForbidden();
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

    /** @param list<Proceso> $processes */
    private function moveInitialHistoryOutsidePeriods(array $processes): void
    {
        foreach ($processes as $process) {
            HistorialEstadoProceso::query()
                ->where('proceso_id', $process->id)
                ->update(['fecha_cambio' => '2026-01-01 00:00:00']);
        }
    }

    private function addStateChange(Proceso $process, string $state, string $changedAt): void
    {
        HistorialEstadoProceso::factory()->create([
            'proceso_id' => $process->id,
            'usuario_id' => null,
            'estado_anterior' => 'pendiente',
            'estado_nuevo' => $state,
            'fecha_cambio' => $changedAt,
        ]);
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
