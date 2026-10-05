<?php

namespace Tests\Feature;

use App\Livewire\Turnos\Agenda;
use App\Livewire\Turnos\ExternalForm;
use App\Livewire\Turnos\Form;
use App\Livewire\Turnos\Index;
use App\Livewire\Turnos\Show;
use App\Models\Cliente;
use App\Models\Proceso;
use App\Models\Servicio;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class TurnoTest extends TestCase
{
    use RefreshDatabase;

    private User $secretario;

    private User $profesional;

    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->secretario = User::factory()->create();
        $this->secretario->assignRole('Secretario');
        $this->profesional = User::factory()->create();
        $this->profesional->assignRole('Profesional');
        $this->cliente = Cliente::factory()->create();
    }

    private function procesoActivo(): Proceso
    {
        $coordinador = User::factory()->create();
        $coordinador->assignRole('Coordinador');

        return Proceso::factory()->create([
            'cliente_id' => $this->cliente->id,
            'profesional_id' => $this->profesional->id,
            'servicio_id' => Servicio::factory()->create()->id,
            'coordinador_id' => $coordinador->id,
            'estado' => 'admitido',
        ]);
    }

    private function form(): Testable
    {
        return Livewire::actingAs($this->secretario)->test(Form::class);
    }

    private function turno(array $overrides = []): Turno
    {
        return Turno::factory()->create(array_merge([
            'cliente_id' => $this->cliente->id,
            'profesional_id' => $this->profesional->id,
            'fecha_hora' => now()->addDays(2)->setTime(10, 0),
            'estado' => 'programado',
        ], $overrides));
    }

    private function fillNewTurno(array $overrides = []): array
    {
        return array_merge([
            'clienteId' => (string) $this->cliente->id,
            'profesionalId' => (string) $this->profesional->id,
            'procesoId' => '',
            'fechaHora' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d\\TH:i'),
            'tipo' => 'consulta_inicial',
        ], $overrides);
    }

    private function fillNewExternalTurno(array $overrides = []): array
    {
        return array_merge([
            'clienteId' => (string) $this->cliente->id,
            'profesionalId' => (string) $this->profesional->id,
            'procesoId' => '',
            'fechaHora' => now()->addDays(3)->setTime(9, 0)->format('Y-m-d\\TH:i'),
            'detalleExterno' => 'Audiencia de prueba HU-13',
        ], $overrides);
    }

    public function test_secretario_agenda_consulta_inicial_sin_generar_notificacion(): void
    {
        $turnoDate = now()->addDays(2)->setTime(10, 0)->format('Y-m-d\\TH:i');

        $this->form()
            ->set($this->fillNewTurno(['fechaHora' => $turnoDate]))
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('turnos', [
            'cliente_id' => $this->cliente->id,
            'profesional_id' => $this->profesional->id,
            'tipo' => 'consulta_inicial',
            'estado' => 'programado',
        ]);
        $this->assertDatabaseCount('notificaciones', 0);
    }

    public function test_seguimiento_exige_proceso_activo_y_cliente_y_profesional_coherentes(): void
    {
        $this->form()
            ->set($this->fillNewTurno(['tipo' => 'seguimiento']))
            ->call('save')
            ->assertHasErrors('procesoId');

        $process = $this->procesoActivo();
        $this->form()
            ->set($this->fillNewTurno(['tipo' => 'seguimiento', 'procesoId' => (string) $process->id]))
            ->call('save')
            ->assertHasNoErrors();

        $process->update(['estado' => 'finalizado']);
        $this->form()
            ->set($this->fillNewTurno([
                'tipo' => 'seguimiento',
                'procesoId' => (string) $process->id,
                'fechaHora' => now()->addDays(3)->setTime(11, 0)->format('Y-m-d\\TH:i'),
            ]))
            ->call('save')
            ->assertHasErrors('procesoId');

        $otherClient = Cliente::factory()->create();
        $this->form()
            ->set($this->fillNewTurno([
                'clienteId' => (string) $otherClient->id,
                'tipo' => 'seguimiento',
                'procesoId' => (string) $process->id,
                'fechaHora' => now()->addDays(4)->setTime(11, 0)->format('Y-m-d\\TH:i'),
            ]))
            ->call('save')
            ->assertHasErrors('clienteId');
    }

    public function test_conflicto_es_fecha_hora_exacta_y_no_se_inventa_duracion(): void
    {
        $date = now()->addDays(2)->setTime(10, 0);
        $this->turno(['fecha_hora' => $date]);

        $this->form()
            ->set($this->fillNewTurno(['fechaHora' => $date->format('Y-m-d\\TH:i')]))
            ->call('save')
            ->assertHasErrors('fechaHora');

        $this->form()
            ->set($this->fillNewTurno(['fechaHora' => $date->copy()->addMinutes(30)->format('Y-m-d\\TH:i')]))
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_compromiso_externo_activo_bloquea_el_dia_completo(): void
    {
        $date = now()->addDays(4)->setTime(10, 0);
        $this->turno([
            'fecha_hora' => $date,
            'es_externo' => true,
            'detalle_externo' => 'Audiencia externa',
            'tipo' => 'externo',
        ]);

        $this->form()
            ->set($this->fillNewTurno(['fechaHora' => $date->copy()->setTime(15, 0)->format('Y-m-d\\TH:i')]))
            ->call('save')
            ->assertHasErrors('fechaHora');
    }

    public function test_secretario_registra_compromiso_externo_con_detalle_y_bloquea_toda_la_fecha(): void
    {
        $date = now()->addDays(6)->setTime(9, 0);

        Livewire::actingAs($this->secretario)
            ->test(ExternalForm::class)
            ->set($this->fillNewExternalTurno(['fechaHora' => $date->format('Y-m-d\\TH:i')]))
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $turno = Turno::query()->where('tipo', 'externo')->firstOrFail();
        $this->assertDatabaseHas('turnos', [
            'id' => $turno->id,
            'cliente_id' => $this->cliente->id,
            'profesional_id' => $this->profesional->id,
            'tipo' => 'externo',
            'es_externo' => true,
            'detalle_externo' => 'Audiencia de prueba HU-13',
            'estado' => 'programado',
        ]);

        $this->form()
            ->set($this->fillNewTurno(['fechaHora' => $date->copy()->setTime(17, 0)->format('Y-m-d\\TH:i')]))
            ->call('save')
            ->assertHasErrors('fechaHora');
    }

    public function test_alta_externa_exige_detalle_y_relaciones_compatibles(): void
    {
        Livewire::actingAs($this->secretario)
            ->test(ExternalForm::class)
            ->set($this->fillNewExternalTurno(['detalleExterno' => '   ']))
            ->call('save')
            ->assertHasErrors('detalleExterno');

        $process = $this->procesoActivo();
        $otherClient = Cliente::factory()->create();
        $otherProfessional = User::factory()->create();
        $otherProfessional->assignRole('Profesional');

        Livewire::actingAs($this->secretario)
            ->test(ExternalForm::class)
            ->set($this->fillNewExternalTurno([
                'clienteId' => (string) $otherClient->id,
                'profesionalId' => (string) $otherProfessional->id,
                'procesoId' => (string) $process->id,
                'fechaHora' => now()->addDays(4)->setTime(11, 0)->format('Y-m-d\\TH:i'),
            ]))
            ->call('save')
            ->assertHasErrors(['clienteId', 'profesionalId']);
    }

    public function test_alta_externa_rechaza_un_dia_ocupado_por_turno_interno(): void
    {
        $date = now()->addDays(7)->setTime(10, 0);
        $this->turno(['fecha_hora' => $date]);

        Livewire::actingAs($this->secretario)
            ->test(ExternalForm::class)
            ->set($this->fillNewExternalTurno(['fechaHora' => $date->copy()->setTime(16, 0)->format('Y-m-d\\TH:i')]))
            ->call('save')
            ->assertHasErrors('fechaHora');
    }

    public function test_reprogramacion_externa_valida_la_fecha_antes_de_liberar_la_anterior(): void
    {
        $oldDate = now()->addDays(8)->setTime(10, 0);
        $turno = $this->turno([
            'fecha_hora' => $oldDate,
            'es_externo' => true,
            'detalle_externo' => 'Audiencia inicial',
            'tipo' => 'externo',
        ]);
        $conflictDate = now()->addDays(9)->setTime(12, 0);
        $this->turno(['fecha_hora' => $conflictDate]);

        Livewire::actingAs($this->secretario)
            ->test(ExternalForm::class, ['turnoId' => $turno->id])
            ->set('fechaHora', $conflictDate->format('Y-m-d\\TH:i'))
            ->set('detalleExterno', 'Audiencia reprogramada')
            ->call('save')
            ->assertHasErrors('fechaHora');

        $this->assertDatabaseHas('turnos', [
            'id' => $turno->id,
            'fecha_hora' => $oldDate->format('Y-m-d H:i:s'),
            'detalle_externo' => 'Audiencia inicial',
        ]);

        $newDate = now()->addDays(10)->setTime(14, 0);
        Livewire::actingAs($this->secretario)
            ->test(ExternalForm::class, ['turnoId' => $turno->id])
            ->set('fechaHora', $newDate->format('Y-m-d\\TH:i'))
            ->set('detalleExterno', 'Audiencia reprogramada')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('turnos', [
            'id' => $turno->id,
            'fecha_hora' => $newDate->format('Y-m-d H:i:s'),
            'detalle_externo' => 'Audiencia reprogramada',
            'estado' => 'programado',
        ]);

        $this->form()
            ->set($this->fillNewTurno(['fechaHora' => $oldDate->copy()->setTime(17, 0)->format('Y-m-d\\TH:i')]))
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_cancelacion_externa_conserva_el_registro_y_libera_la_fecha(): void
    {
        $date = now()->addDays(11)->setTime(10, 0);
        $turno = $this->turno([
            'fecha_hora' => $date,
            'es_externo' => true,
            'detalle_externo' => 'Audiencia cancelada',
            'tipo' => 'externo',
        ]);

        Livewire::actingAs($this->secretario)
            ->test(Show::class, ['turno' => $turno])
            ->call('requestCancellation')
            ->call('cancelTurno')
            ->assertRedirect(route('turnos.index'));

        $this->assertDatabaseHas('turnos', [
            'id' => $turno->id,
            'estado' => 'cancelado',
            'deleted_at' => null,
            'fecha_hora' => $date->format('Y-m-d H:i:s'),
            'detalle_externo' => 'Audiencia cancelada',
        ]);

        $this->form()
            ->set($this->fillNewTurno(['fechaHora' => $date->copy()->setTime(16, 0)->format('Y-m-d\\TH:i')]))
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_reprogramar_excluye_el_turno_actual_y_libera_horario_anterior(): void
    {
        $turno = $this->turno();
        $oldDate = $turno->fecha_hora->format('Y-m-d H:i:s');
        $newDate = now()->addDays(2)->setTime(14, 0)->format('Y-m-d\\TH:i');

        Livewire::actingAs($this->secretario)->test(Form::class, ['turno' => $turno])
            ->set('fechaHora', $newDate)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('turnos', ['id' => $turno->id, 'fecha_hora' => str_replace('T', ' ', $newDate).':00']);

        $this->form()
            ->set($this->fillNewTurno(['fechaHora' => str_replace(' ', 'T', substr($oldDate, 0, 16))]))
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_cancelacion_conserva_historial_y_libera_horario(): void
    {
        $turno = $this->turno(['fecha_hora' => now()->addDays(5)->setTime(10, 0)]);

        Livewire::actingAs($this->secretario)->test(Show::class, ['turno' => $turno])
            ->call('requestCancellation')
            ->call('cancelTurno')
            ->assertRedirect(route('turnos.index'));

        $this->assertDatabaseHas('turnos', ['id' => $turno->id, 'estado' => 'cancelado', 'deleted_at' => null]);
        $this->assertDatabaseCount('notificaciones', 0);

        $this->form()
            ->set($this->fillNewTurno(['fechaHora' => $turno->fecha_hora->format('Y-m-d\\TH:i')]))
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_turno_cancelado_no_se_puede_reprogramar(): void
    {
        $turno = $this->turno(['estado' => 'cancelado']);

        $this->actingAs($this->secretario)->get(route('turnos.edit', $turno))->assertForbidden();
        Livewire::actingAs($this->secretario)->test(Form::class, ['turno' => $turno])->assertForbidden();
    }

    public function test_profesional_solo_ve_su_agenda_aunque_intente_modificar_filtros(): void
    {
        $otherProfessional = User::factory()->create();
        $otherProfessional->assignRole('Profesional');
        $own = $this->turno();
        $own->cliente->update(['apellido' => 'ClientePropioHU11']);
        $otherClient = Cliente::factory()->create(['apellido' => 'ClienteAjenoHU11']);
        $other = $this->turno(['profesional_id' => $otherProfessional->id, 'cliente_id' => $otherClient->id]);

        $response = Livewire::actingAs($this->profesional)
            ->test(Agenda::class)
            ->set('profesionalId', (string) $otherProfessional->id)
            ->call('applyFilters');

        $response->assertHasNoErrors()->assertSee('ClientePropioHU11')->assertDontSee('ClienteAjenoHU11');
        Livewire::actingAs($this->profesional)->test(Show::class, ['turno' => $other])->assertForbidden();
    }

    public function test_agenda_requiere_rango_acotado_de_hasta_90_dias(): void
    {
        Livewire::actingAs($this->secretario)
            ->test(Agenda::class)
            ->set('desde', '2032-01-01')
            ->set('hasta', '2032-04-02')
            ->call('applyFilters')
            ->assertHasErrors('hasta');
    }

    public function test_agenda_aplica_todos_los_profesionales_muestra_limites_y_cancelaciones(): void
    {
        $desde = now()->addDays(5)->startOfDay();
        $hasta = $desde->copy()->addDays(2);
        $turnoInicial = Cliente::factory()->create(['apellido' => 'InicioHU12']);
        $turnoFinal = Cliente::factory()->create(['apellido' => 'FinalHU12']);
        $turnoPrevio = Cliente::factory()->create(['apellido' => 'PrevioFueraHU12']);
        $turnoPosterior = Cliente::factory()->create(['apellido' => 'PosteriorFueraHU12']);

        $this->turno([
            'cliente_id' => $turnoInicial->id,
            'fecha_hora' => $desde,
        ]);
        $this->turno([
            'cliente_id' => $turnoFinal->id,
            'fecha_hora' => $hasta->copy()->endOfDay(),
        ]);
        $this->turno([
            'fecha_hora' => $desde->copy()->setTime(12, 0),
            'tipo' => 'seguimiento',
        ]);
        $this->turno([
            'cliente_id' => $turnoPrevio->id,
            'fecha_hora' => $desde->copy()->subSecond(),
        ]);
        $this->turno([
            'cliente_id' => $turnoPosterior->id,
            'fecha_hora' => $hasta->copy()->endOfDay()->addSecond(),
        ]);
        $this->turno([
            'fecha_hora' => $desde->copy()->addDay()->setTime(10, 0),
            'tipo' => 'externo',
            'es_externo' => true,
            'detalle_externo' => 'AudienciaHU12',
        ]);
        $this->turno([
            'fecha_hora' => $hasta->copy()->setTime(11, 0),
            'tipo' => 'externo',
            'es_externo' => true,
            'detalle_externo' => 'CanceladoHU12',
            'estado' => 'cancelado',
        ]);

        Livewire::actingAs($this->secretario)
            ->test(Agenda::class)
            ->set('profesionalId', '')
            ->set('desde', $desde->toDateString())
            ->set('hasta', $hasta->toDateString())
            ->call('applyFilters')
            ->assertHasNoErrors()
            ->assertSee('InicioHU12')
            ->assertSee('FinalHU12')
            ->assertSee('Seguimiento')
            ->assertSee('Horario ocupado')
            ->assertSee('AudienciaHU12')
            ->assertSee('Bloquea la jornada')
            ->assertSee('Cancelado (histórico, no ocupa disponibilidad)')
            ->assertDontSee('PrevioFueraHU12')
            ->assertDontSee('PosteriorFueraHU12');
    }

    public function test_agenda_acepta_el_maximo_de_90_dias_y_rechaza_rangos_invertidos(): void
    {
        $agenda = Livewire::actingAs($this->secretario)
            ->test(Agenda::class)
            ->set('profesionalId', (string) $this->profesional->id)
            ->set('desde', '2032-01-01')
            ->set('hasta', '2032-03-30')
            ->call('applyFilters');

        $agenda->assertHasNoErrors();

        Livewire::actingAs($this->secretario)
            ->test(Agenda::class)
            ->set('profesionalId', (string) $this->profesional->id)
            ->set('desde', '2032-01-01')
            ->set('hasta', '2032-03-31')
            ->call('applyFilters')
            ->assertHasErrors('hasta');

        Livewire::actingAs($this->secretario)
            ->test(Agenda::class)
            ->set('profesionalId', (string) $this->profesional->id)
            ->set('desde', '2032-03-30')
            ->set('hasta', '2032-01-01')
            ->call('applyFilters')
            ->assertHasErrors('hasta');
    }

    public function test_agenda_muestra_estado_vacio_para_el_periodo_filtrado(): void
    {
        Livewire::actingAs($this->secretario)
            ->test(Agenda::class)
            ->set('profesionalId', (string) $this->profesional->id)
            ->set('desde', '2032-01-01')
            ->set('hasta', '2032-01-03')
            ->call('applyFilters')
            ->assertHasNoErrors()
            ->assertSee('No hay turnos en el período.');
    }

    public function test_agenda_muestra_error_si_el_filtro_no_es_un_profesional(): void
    {
        Livewire::actingAs($this->secretario)
            ->test(Agenda::class)
            ->set('profesionalId', (string) $this->secretario->id)
            ->call('applyFilters')
            ->assertHasErrors('profesionalId');
    }

    public function test_permiso_de_turno_interno_no_permite_escalar_a_seguimiento(): void
    {
        $restrictedUser = User::factory()->create();
        $restrictedUser->givePermissionTo('agendar_turnos_internos');

        $this->assertTrue(Gate::forUser($restrictedUser)->allows('create', Turno::class));
        $this->assertTrue(Gate::forUser($restrictedUser)->allows('agendar_turnos_internos'));
        $this->assertFalse(Gate::forUser($restrictedUser)->allows('agendar_turnos_seguimiento'));
    }

    public function test_permisos_de_agenda_y_escritura_respetan_matriz(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrador');
        $coordinator = User::factory()->create();
        $coordinator->assignRole('Coordinador');
        $director = User::factory()->create();
        $director->assignRole('Directivo');

        $this->actingAs($admin)->get(route('turnos.index'))->assertOk();
        $this->actingAs($this->secretario)->get(route('turnos.agenda'))->assertOk();
        $this->actingAs($this->profesional)->get(route('turnos.index'))->assertOk();
        $this->actingAs($coordinator)->get(route('turnos.agenda'))->assertOk();
        $this->actingAs($director)->get(route('turnos.index'))->assertForbidden();

        Livewire::actingAs($this->profesional)->test(Form::class)->assertForbidden();
        Livewire::actingAs($coordinator)->test(Form::class)->assertForbidden();
        Livewire::actingAs($director)->test(Agenda::class)->assertForbidden();
    }

    public function test_escritura_externa_solo_se_permite_a_secretario_y_administrador(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrador');
        $coordinator = User::factory()->create();
        $coordinator->assignRole('Coordinador');
        $director = User::factory()->create();
        $director->assignRole('Directivo');
        $turno = $this->turno([
            'es_externo' => true,
            'detalle_externo' => 'Compromiso para permisos',
            'tipo' => 'externo',
        ]);

        $this->actingAs($this->secretario)->get(route('turnos.externos.create'))->assertOk();
        $this->actingAs($admin)->get(route('turnos.externos.create'))->assertOk();
        $this->actingAs($this->secretario)->get(route('turnos.externos.edit', $turno))->assertOk();
        $this->actingAs($admin)->get(route('turnos.externos.edit', $turno))->assertOk();

        foreach ([$this->profesional, $coordinator, $director] as $user) {
            $this->actingAs($user)->get(route('turnos.externos.create'))->assertForbidden();
            $this->actingAs($user)->get(route('turnos.externos.edit', $turno))->assertForbidden();
            Livewire::actingAs($user)->test(ExternalForm::class)->assertForbidden();
        }

        Livewire::actingAs($this->profesional)
            ->test(Index::class)
            ->assertDontSee('Registrar compromiso externo');
        Livewire::actingAs($coordinator)
            ->test(Index::class)
            ->assertDontSee('Registrar compromiso externo');
        Livewire::actingAs($this->profesional)
            ->test(Show::class, ['turno' => $turno])
            ->call('requestCancellation')
            ->assertForbidden();
        Livewire::actingAs($coordinator)
            ->test(Show::class, ['turno' => $turno])
            ->call('requestCancellation')
            ->assertForbidden();
    }
}
