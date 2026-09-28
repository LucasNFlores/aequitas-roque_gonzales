<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\EstadoProceso;
use App\Models\Proceso;
use App\Models\Servicio;
use App\Models\User;
use Database\Seeders\EstadoProcesoSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcesoAdicionalTest extends TestCase
{
    use RefreshDatabase;

    private function seedBase(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(EstadoProcesoSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(Cliente $cliente, Servicio $servicio, User $coordinador, ?User $profesional = null): array
    {
        return [
            'cliente_id' => $cliente->id,
            'servicio_id' => $servicio->id,
            'coordinador_id' => $coordinador->id,
            'profesional_id' => $profesional?->id,
            'nombre' => 'Proceso adicional de prueba',
            'descripcion' => 'Nueva necesidad jurídica sin duplicar datos administrativos.',
            'fecha_inicio' => now()->format('Y-m-d'),
            'tipo' => 'Civil',
        ];
    }

    public function test_secretario_crea_proceso_adicional_anidado_en_pendiente(): void
    {
        $this->seedBase();
        $secretario = $this->userWithRole('Secretario');
        $coordinador = $this->userWithRole('Coordinador');
        $cliente = Cliente::factory()->create();
        $servicio = Servicio::factory()->create();
        $updatedAtAntes = $cliente->updated_at;

        $this->actingAs($secretario)
            ->get(route('clientes.procesos.create', $cliente))
            ->assertOk()
            ->assertSee('Nuevo proceso adicional', false)
            ->assertSee($cliente->nombre, false);

        $response = $this->actingAs($secretario)->post(
            route('clientes.procesos.store', $cliente),
            $this->validPayload($cliente, $servicio, $coordinador)
        );

        $response->assertRedirect(route('clientes.show', $cliente));

        $proceso = Proceso::where('cliente_id', $cliente->id)->firstOrFail();
        $this->assertSame('pendiente', $proceso->estado);
        $this->assertNull($proceso->motivo_rechazo);
        $this->assertSame($servicio->id, $proceso->servicio_id);
        $this->assertSame($coordinador->id, $proceso->coordinador_id);

        // El cliente no se duplica ni se modifica como efecto de la operación.
        $this->assertSame(1, Cliente::count());
        $this->assertTrue($updatedAtAntes->equalTo($cliente->refresh()->updated_at));

        // No se crea ningún turno automático.
        $this->assertSame(0, $proceso->turnos()->count());

        // El flujo de admisión queda habilitado: historial inicial registrado.
        $this->assertSame(1, $proceso->historialEstados()->count());
        $this->assertSame('pendiente', $proceso->historialEstados()->firstOrFail()->estado_nuevo);
    }

    public function test_administrador_crea_proceso_adicional_global(): void
    {
        $this->seedBase();
        $admin = $this->userWithRole('Administrador');
        $coordinador = $this->userWithRole('Coordinador');
        $profesional = $this->userWithRole('Profesional');
        $cliente = Cliente::factory()->create();
        $servicio = Servicio::factory()->create();

        $this->actingAs($admin)
            ->get(route('procesos.create'))
            ->assertOk()
            ->assertSee('Nuevo proceso adicional', false);

        $this->actingAs($admin)->post(
            route('procesos.store'),
            $this->validPayload($cliente, $servicio, $coordinador, $profesional)
        )->assertRedirect(route('clientes.show', $cliente));

        $proceso = Proceso::where('cliente_id', $cliente->id)->firstOrFail();
        $this->assertSame('pendiente', $proceso->estado);
        $this->assertSame($profesional->id, $proceso->profesional_id);
        $this->assertSame(0, $proceso->turnos()->count());
        $this->assertSame(1, Cliente::count());
    }

    public function test_roles_no_autorizados_no_pueden_crear_proceso_adicional(): void
    {
        $this->seedBase();
        $cliente = Cliente::factory()->create();
        $servicio = Servicio::factory()->create();
        $coordinador = $this->userWithRole('Coordinador');

        // Invitado es redirigido al login.
        $this->get(route('procesos.create'))->assertRedirect(route('login'));
        $this->post(route('procesos.store'), $this->validPayload($cliente, $servicio, $coordinador))
            ->assertRedirect(route('login'));

        foreach (['Profesional', 'Coordinador', 'Directivo'] as $role) {
            $user = $this->userWithRole($role);

            $this->actingAs($user)->get(route('procesos.create'))->assertForbidden();
            $this->actingAs($user)->get(route('clientes.procesos.create', $cliente))->assertForbidden();
            $this->actingAs($user)->post(route('procesos.store'), $this->validPayload($cliente, $servicio, $coordinador))->assertForbidden();
            $this->actingAs($user)->post(route('clientes.procesos.store', $cliente), $this->validPayload($cliente, $servicio, $coordinador))->assertForbidden();
        }

        $this->assertSame(0, Proceso::count());
    }

    public function test_validacion_rechaza_datos_invalidos_sin_crear_proceso(): void
    {
        $this->seedBase();
        $secretario = $this->userWithRole('Secretario');
        $coordinador = $this->userWithRole('Coordinador');
        $profesional = $this->userWithRole('Profesional');
        $cliente = Cliente::factory()->create();
        $servicio = Servicio::factory()->create();

        // Cliente inexistente.
        $this->actingAs($secretario)->post(
            route('procesos.store'),
            array_merge($this->validPayload($cliente, $servicio, $coordinador), ['cliente_id' => 999999])
        )->assertSessionHasErrors('cliente_id');

        // Servicio inexistente.
        $this->actingAs($secretario)->post(
            route('procesos.store'),
            [...$this->validPayload($cliente, $servicio, $coordinador), 'servicio_id' => 999999]
        )->assertSessionHasErrors('servicio_id');

        // Coordinador sin rol Coordinador.
        $this->actingAs($secretario)->post(
            route('procesos.store'),
            [...$this->validPayload($cliente, $servicio, $coordinador), 'coordinador_id' => $profesional->id]
        )->assertSessionHasErrors('coordinador_id');

        // Profesional sin rol Profesional.
        $this->actingAs($secretario)->post(
            route('procesos.store'),
            [...$this->validPayload($cliente, $servicio, $coordinador, $profesional), 'profesional_id' => $coordinador->id]
        )->assertSessionHasErrors('profesional_id');

        // Estado inicial y motivo no son asignables por el solicitante.
        $this->actingAs($secretario)->post(
            route('procesos.store'),
            [...$this->validPayload($cliente, $servicio, $coordinador), 'estado' => 'admitido', 'motivo_rechazo' => 'x']
        )->assertSessionHasErrors(['estado', 'motivo_rechazo']);

        // Tipo fuera de catálogo.
        $this->actingAs($secretario)->post(
            route('procesos.store'),
            [...$this->validPayload($cliente, $servicio, $coordinador), 'tipo' => 'Penal']
        )->assertSessionHasErrors('tipo');

        $this->assertSame(0, Proceso::count());
    }

    public function test_inactivos_no_generan_proceso(): void
    {
        $this->seedBase();
        $secretario = $this->userWithRole('Secretario');
        $coordinador = $this->userWithRole('Coordinador');
        $cliente = Cliente::factory()->create();
        $servicio = Servicio::factory()->create();

        // Cliente dado de baja: la ruta anidada responde 404 y no crea nada.
        $clienteEliminado = Cliente::factory()->create();
        $clienteEliminado->delete();
        $this->actingAs($secretario)->get(route('clientes.procesos.create', $clienteEliminado->id))->assertNotFound();
        $this->actingAs($secretario)->post(
            route('clientes.procesos.store', $clienteEliminado->id),
            $this->validPayload($clienteEliminado, $servicio, $coordinador)
        )->assertNotFound();

        // Coordinador dado de baja.
        $coordinador->delete();
        $this->actingAs($secretario)->post(
            route('procesos.store'),
            $this->validPayload($cliente, $servicio, $coordinador)
        )->assertSessionHasErrors('coordinador_id');

        // Servicio dado de baja.
        $servicio->delete();
        $coordinadorActivo = $this->userWithRole('Coordinador');
        $this->actingAs($secretario)->post(
            route('procesos.store'),
            $this->validPayload($cliente, $servicio, $coordinadorActivo)
        )->assertSessionHasErrors('servicio_id');

        // Estado inicial pendiente inactivo.
        EstadoProceso::where('slug', 'pendiente')->update(['activo' => false]);
        $servicioActivo = Servicio::factory()->create();
        $this->actingAs($secretario)->post(
            route('procesos.store'),
            $this->validPayload($cliente, $servicioActivo, $coordinadorActivo)
        )->assertSessionHasErrors('estado');

        $this->assertSame(0, Proceso::count());
    }

    public function test_doble_envio_usa_redirect_y_boton_antidobleclick(): void
    {
        $this->seedBase();
        $secretario = $this->userWithRole('Secretario');
        $coordinador = $this->userWithRole('Coordinador');
        $cliente = Cliente::factory()->create();
        $servicio = Servicio::factory()->create();

        // PRG: la creación responde con redirect, un refresh (GET) no reenvía el POST.
        $this->actingAs($secretario)->post(
            route('clientes.procesos.store', $cliente),
            $this->validPayload($cliente, $servicio, $coordinador)
        )->assertRedirect(route('clientes.show', $cliente));
        $this->assertSame(1, Proceso::where('cliente_id', $cliente->id)->count());

        $this->actingAs($secretario)->get(route('clientes.show', $cliente))->assertOk();
        $this->assertSame(1, Proceso::where('cliente_id', $cliente->id)->count());

        // El formulario deshabilita el botón al enviar para evitar doble clic.
        $this->actingAs($secretario)->get(route('clientes.procesos.create', $cliente))
            ->assertOk()
            ->assertSee('proceso-adicional-submit', false)
            ->assertSee('btn.disabled = true', false);
    }
}
