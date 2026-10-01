<?php

namespace Tests\Feature;

use App\Livewire\Turnos\Agenda;
use App\Livewire\Turnos\Form;
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
}
