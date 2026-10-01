<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Proceso;
use App\Models\Servicio;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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
        Mail::fake();

        $this->secretario = User::factory()->create();
        $this->secretario->assignRole('Secretario');

        $this->profesional = User::factory()->create();
        $this->profesional->assignRole('Profesional');

        $this->cliente = Cliente::factory()->create();
    }

    private function procesoActivo(): Proceso
    {
        $servicio = Servicio::factory()->create();
        $coordinador = User::factory()->create();
        $coordinador->assignRole('Coordinador');

        return Proceso::factory()->create([
            'cliente_id' => $this->cliente->id,
            'profesional_id' => $this->profesional->id,
            'servicio_id' => $servicio->id,
            'coordinador_id' => $coordinador->id,
            'estado' => 'admitido',
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'cliente_id' => $this->cliente->id,
            'profesional_id' => $this->profesional->id,
            'proceso_id' => null,
            'fecha_hora' => now()->addDays(2)->setHour(10)->setMinute(0)->format('Y-m-d H:i:s'),
            'tipo' => 'consulta_inicial',
        ], $overrides);
    }

    public function test_secretario_crea_consulta_inicial_y_genera_notificacion(): void
    {
        $this->actingAs($this->secretario)
            ->post(route('turnos.store'), $this->payload())
            ->assertRedirect();

        $this->assertDatabaseHas('turnos', [
            'cliente_id' => $this->cliente->id,
            'tipo' => 'consulta_inicial',
            'estado' => 'programado',
        ]);
        $this->assertDatabaseHas('notificaciones', [
            'cliente_id' => $this->cliente->id,
        ]);
    }

    public function test_seguimiento_exige_proceso_activo(): void
    {
        // Sin proceso -> 422
        $this->actingAs($this->secretario)
            ->post(route('turnos.store'), $this->payload(['tipo' => 'seguimiento']))
            ->assertSessionHasErrors('proceso_id');

        // Con proceso activo -> ok
        $proceso = $this->procesoActivo();
        $this->actingAs($this->secretario)
            ->post(route('turnos.store'), $this->payload(['tipo' => 'seguimiento', 'proceso_id' => $proceso->id]))
            ->assertRedirect();
        $this->assertDatabaseHas('turnos', ['proceso_id' => $proceso->id, 'estado' => 'programado']);

        // Proceso finalizado -> 422
        $proceso->update(['estado' => 'finalizado']);
        $this->actingAs($this->secretario)
            ->post(route('turnos.store'), $this->payload([
                'tipo' => 'seguimiento',
                'proceso_id' => $proceso->id,
                'fecha_hora' => now()->addDays(3)->format('Y-m-d H:i:s'),
            ]))
            ->assertSessionHasErrors('proceso_id');
    }

    public function test_consistencia_cliente_profesional_con_proceso(): void
    {
        $proceso = $this->procesoActivo();
        $otroCliente = Cliente::factory()->create();

        $this->actingAs($this->secretario)
            ->post(route('turnos.store'), $this->payload([
                'tipo' => 'seguimiento',
                'proceso_id' => $proceso->id,
                'cliente_id' => $otroCliente->id,
            ]))
            ->assertSessionHasErrors('cliente_id');
    }

    public function test_conflicto_misma_fecha_y_rango_solapado(): void
    {
        $fecha = now()->addDays(2)->setHour(10)->setMinute(0)->second(0);
        Turno::factory()->create([
            'cliente_id' => $this->cliente->id,
            'profesional_id' => $this->profesional->id,
            'fecha_hora' => $fecha,
            'estado' => 'programado',
        ]);

        // Misma fecha_hora exacta -> 422
        $this->actingAs($this->secretario)
            ->post(route('turnos.store'), $this->payload(['fecha_hora' => $fecha->format('Y-m-d H:i:s')]))
            ->assertSessionHasErrors('fecha_hora');

        // Solapado +30min (rango 60min) -> 422
        $this->actingAs($this->secretario)
            ->post(route('turnos.store'), $this->payload(['fecha_hora' => $fecha->copy()->addMinutes(30)->format('Y-m-d H:i:s')]))
            ->assertSessionHasErrors('fecha_hora');

        // +2h libre -> ok
        $this->actingAs($this->secretario)
            ->post(route('turnos.store'), $this->payload(['fecha_hora' => $fecha->copy()->addHours(2)->format('Y-m-d H:i:s')]))
            ->assertRedirect();
    }

    public function test_externo_bloquea_dia(): void
    {
        $fecha = now()->addDays(4)->setHour(10)->setMinute(0)->second(0);
        Turno::factory()->create([
            'cliente_id' => $this->cliente->id,
            'profesional_id' => $this->profesional->id,
            'fecha_hora' => $fecha,
            'es_externo' => true,
            'detalle_externo' => 'Trámite externo',
            'tipo' => 'externo',
            'estado' => 'programado',
        ]);

        $this->actingAs($this->secretario)
            ->post(route('turnos.store'), $this->payload(['fecha_hora' => $fecha->copy()->setHour(15)->format('Y-m-d H:i:s')]))
            ->assertSessionHasErrors('fecha_hora');
    }

    public function test_reprogramacion_libera_disponibilidad_anterior(): void
    {
        $turno = Turno::factory()->create([
            'cliente_id' => $this->cliente->id,
            'profesional_id' => $this->profesional->id,
            'fecha_hora' => now()->addDays(2)->setHour(10)->setMinute(0)->second(0),
            'estado' => 'programado',
        ]);
        $fechaAnterior = $turno->fecha_hora->format('Y-m-d H:i:s');
        $nuevaFecha = now()->addDays(2)->setHour(14)->setMinute(0)->second(0)->format('Y-m-d H:i:s');

        $this->actingAs($this->secretario)
            ->put(route('turnos.update', $turno), ['fecha_hora' => $nuevaFecha])
            ->assertRedirect();

        $this->assertDatabaseHas('turnos', ['id' => $turno->id, 'fecha_hora' => $nuevaFecha]);

        // La fecha anterior quedó libre: se puede agendar de nuevo.
        $this->actingAs($this->secretario)
            ->post(route('turnos.store'), $this->payload(['fecha_hora' => $fechaAnterior]))
            ->assertRedirect();
    }

    public function test_cancelacion_conserva_historial_y_libera_horario(): void
    {
        $turno = Turno::factory()->create([
            'cliente_id' => $this->cliente->id,
            'profesional_id' => $this->profesional->id,
            'fecha_hora' => now()->addDays(5)->setHour(10)->setMinute(0)->second(0),
            'estado' => 'programado',
        ]);
        $fecha = $turno->fecha_hora->format('Y-m-d H:i:s');

        $this->actingAs($this->secretario)
            ->delete(route('turnos.destroy', $turno))
            ->assertRedirect(route('turnos.index'));

        // No se borra: queda con estado cancelado.
        $this->assertDatabaseHas('turnos', ['id' => $turno->id, 'estado' => 'cancelado']);
        $this->assertNull(Turno::find($turno->id)->deleted_at);
        $this->assertDatabaseHas('notificaciones', ['cliente_id' => $this->cliente->id]);

        // El horario queda libre.
        $this->actingAs($this->secretario)
            ->post(route('turnos.store'), $this->payload(['fecha_hora' => $fecha]))
            ->assertRedirect();
    }

    public function test_roles_profesional_coordinador_solo_consultan_y_directivo_nada(): void
    {
        $profesional = User::factory()->create();
        $profesional->assignRole('Profesional');
        $coordinador = User::factory()->create();
        $coordinador->assignRole('Coordinador');
        $directivo = User::factory()->create();
        $directivo->assignRole('Directivo');

        $this->actingAs($profesional)->get(route('turnos.index'))->assertOk();
        $this->actingAs($coordinador)->get(route('turnos.index'))->assertOk();
        $this->actingAs($directivo)->get(route('turnos.index'))->assertForbidden();

        $this->actingAs($profesional)->post(route('turnos.store'), $this->payload())->assertForbidden();
        $this->actingAs($coordinador)->post(route('turnos.store'), $this->payload())->assertForbidden();
    }
}
