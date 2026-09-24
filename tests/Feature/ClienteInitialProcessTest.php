<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Notificacion;
use App\Models\Proceso;
use App\Models\Servicio;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteInitialProcessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_secretary_registration_creates_the_initial_process_turn_and_notification(): void
    {
        $secretario = $this->userWithRole('Secretario');
        $coordinador = $this->createInitialConfiguration();

        $this->actingAs($secretario)
            ->post(route('clientes.store'), $this->validData())
            ->assertRedirect(route('clientes.index'));

        $cliente = Cliente::query()->where('dni', '30123456')->firstOrFail();
        $proceso = Proceso::query()->where('cliente_id', $cliente->id)->firstOrFail();
        $turno = Turno::query()->where('proceso_id', $proceso->id)->firstOrFail();

        $this->assertSame('pendiente', $proceso->estado);
        $this->assertSame($coordinador->id, $proceso->coordinador_id);
        $this->assertSame('Consulta legal inicial', $proceso->servicio->nombre);
        $this->assertSame('consulta_inicial', $turno->tipo);
        $this->assertFalse($turno->es_externo);
        $this->assertSame($coordinador->id, $turno->coordinador_id);
        $this->assertSame($coordinador->id, $turno->profesional_id);
        $this->assertSame('2030-05-20 10:30', $turno->fecha_hora->format('Y-m-d H:i'));
        $this->assertDatabaseHas('notificaciones', [
            'user_id' => $coordinador->id,
            'cliente_id' => $cliente->id,
            'canal' => 'interno',
            'estado' => 'enviado',
        ]);
        $this->assertDatabaseCount('clientes', 1);
        $this->assertDatabaseCount('procesos', 1);
        $this->assertDatabaseCount('turnos', 1);
        $this->assertSame(1, Notificacion::count());
    }

    public function test_administrator_can_register_a_client_with_its_initial_records(): void
    {
        $administrador = $this->userWithRole('Administrador');
        $this->createInitialConfiguration();

        $this->actingAs($administrador)
            ->post(route('clientes.store'), $this->validData())
            ->assertRedirect(route('clientes.index'));

        $this->assertDatabaseCount('clientes', 1);
        $this->assertDatabaseCount('procesos', 1);
        $this->assertDatabaseCount('turnos', 1);
        $this->assertSame(1, Notificacion::count());
    }

    public function test_duplicate_registration_does_not_create_derived_records(): void
    {
        $secretario = $this->userWithRole('Secretario');
        $this->createInitialConfiguration();

        $this->actingAs($secretario)->post(route('clientes.store'), $this->validData());

        $this->actingAs($secretario)
            ->post(route('clientes.store'), $this->validData())
            ->assertSessionHasErrors('dni');

        $this->assertDatabaseCount('clientes', 1);
        $this->assertDatabaseCount('procesos', 1);
        $this->assertDatabaseCount('turnos', 1);
        $this->assertSame(1, Notificacion::count());
    }

    public function test_registration_rolls_back_when_there_is_no_coordinator(): void
    {
        $secretario = $this->userWithRole('Secretario');
        Servicio::factory()->create(['nombre' => 'Consulta legal inicial']);

        $this->actingAs($secretario)
            ->post(route('clientes.store'), $this->validData())
            ->assertSessionHasErrors([
                'fecha_hora_inicial' => 'No hay un Coordinador activo para asignar el turno inicial.',
            ]);

        $this->assertDatabaseCount('clientes', 0);
        $this->assertDatabaseCount('procesos', 0);
        $this->assertDatabaseCount('turnos', 0);
        $this->assertSame(0, Notificacion::count());
    }

    public function test_registration_rolls_back_when_the_initial_service_is_not_configured(): void
    {
        $secretario = $this->userWithRole('Secretario');
        $this->userWithRole('Coordinador');

        $this->actingAs($secretario)
            ->post(route('clientes.store'), $this->validData())
            ->assertSessionHasErrors([
                'fecha_hora_inicial' => 'No está configurado el servicio inicial "Consulta legal inicial".',
            ]);

        $this->assertDatabaseCount('clientes', 0);
        $this->assertDatabaseCount('procesos', 0);
        $this->assertDatabaseCount('turnos', 0);
        $this->assertSame(0, Notificacion::count());
    }

    public function test_registration_requires_the_initial_turn_date_and_time(): void
    {
        $secretario = $this->userWithRole('Secretario');

        $this->actingAs($secretario)
            ->post(route('clientes.store'), $this->validData(['fecha_hora_inicial' => '']))
            ->assertSessionHasErrors('fecha_hora_inicial');

        $this->assertDatabaseCount('clientes', 0);
        $this->assertDatabaseCount('procesos', 0);
        $this->assertDatabaseCount('turnos', 0);
    }

    public function test_professional_cannot_register_a_client_or_initial_records(): void
    {
        $profesional = $this->userWithRole('Profesional');
        $this->createInitialConfiguration();

        $this->actingAs($profesional)
            ->post(route('clientes.store'), $this->validData())
            ->assertForbidden();

        $this->assertDatabaseCount('clientes', 0);
        $this->assertDatabaseCount('procesos', 0);
        $this->assertDatabaseCount('turnos', 0);
        $this->assertSame(0, Notificacion::count());
    }

    private function createInitialConfiguration(): User
    {
        Servicio::factory()->create(['nombre' => 'Consulta legal inicial']);

        return $this->userWithRole('Coordinador');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    /**
     * @return array<string, string>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'dni' => '30123456',
            'telefono' => '11-4567-8901',
            'correo' => 'juan.perez@example.com',
            'domicilio' => 'Av. Corrientes 1234',
            'fecha_nacimiento' => '1990-05-15',
            'fecha_hora_inicial' => '2030-05-20 10:30:00',
        ], $overrides);
    }
}
