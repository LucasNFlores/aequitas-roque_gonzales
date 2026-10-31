<?php

namespace Tests\Feature;

use App\Livewire\Legajos\Index;
use App\Livewire\Turnos\Form as TurnoForm;
use App\Models\CategoriaDocumento;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Models\EstadoProceso;
use App\Models\Proceso;
use App\Models\Reporte;
use App\Models\Servicio;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\EstadoProcesoSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class LegajoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(EstadoProcesoSeeder::class);
        Storage::fake('local');
    }

    public function test_professional_search_and_legajo_show_only_assigned_processes_and_turnos(): void
    {
        $professional = $this->userWithRole('Profesional');
        $otherProfessional = $this->userWithRole('Profesional');
        $coordinator = $this->userWithRole('Coordinador');
        $client = Cliente::factory()->create([
            'nombre' => 'Ana María',
            'apellido' => 'Legajo Prueba',
            'dni' => '28123456',
        ]);
        $assignedProcess = $this->processFor($client, $professional, $coordinator, 'Visible asignado');
        $otherProcess = $this->processFor($client, $otherProfessional, $coordinator, 'Proceso ajeno');

        Turno::factory()->create([
            'cliente_id' => $client->id,
            'profesional_id' => $professional->id,
            'proceso_id' => $assignedProcess->id,
        ]);
        Turno::factory()->create([
            'cliente_id' => $client->id,
            'profesional_id' => $otherProfessional->id,
            'proceso_id' => $otherProcess->id,
            'detalle_externo' => 'Turno de otro profesional',
        ]);

        Livewire::actingAs($professional)
            ->test(Index::class)
            ->set('search', '28123456')
            ->assertSee('Legajo Prueba, Ana María')
            ->assertSee('1');

        $response = $this->actingAs($professional)->get(route('legajos.show', $client));

        $response->assertOk()
            ->assertSee('Visible asignado')
            ->assertSee(route('legajos.procesos.show', [$client, $assignedProcess]))
            ->assertSee($professional->nombre_completo)
            ->assertSee($coordinator->nombre_completo)
            ->assertDontSee('Proceso ajeno')
            ->assertDontSee('Turnos del cliente sin proceso asociado')
            ->assertDontSee('Turno de otro profesional');

        $this->actingAs($professional)
            ->get(route('legajos.procesos.show', [$client, $assignedProcess]))
            ->assertOk()
            ->assertSee('Turnos relacionados')
            ->assertSee('Visible asignado')
            ->assertDontSee('Proceso ajeno');

        $this->actingAs($professional)
            ->get(route('legajos.procesos.show', [$client, $this->processFor($client, $otherProfessional, $coordinator, 'Proceso directo ajeno')]))
            ->assertNotFound();

        $otherClient = Cliente::factory()->create();
        $otherClientProcess = $this->processFor($otherClient, $professional, $coordinator, 'Proceso de otro cliente');

        $this->actingAs($professional)
            ->get(route('legajos.procesos.show', [$client, $otherClientProcess]))
            ->assertNotFound();
    }

    public function test_directivo_can_read_document_metadata_but_never_receives_file_paths_or_links(): void
    {
        $executive = $this->userWithRole('Directivo');
        $client = Cliente::factory()->create();
        $process = $this->processFor($client, $this->userWithRole('Profesional'), $this->userWithRole('Coordinador'));
        $category = CategoriaDocumento::factory()->create(['nombre' => 'Identidad reservada']);
        $document = Documento::factory()->create([
            'proceso_id' => $process->id,
            'categoria_id' => $category->id,
            'nombre' => 'DNI actualizado',
            'archivo_path' => 'documentos/privado/archivo-secreto.pdf',
        ]);

        $response = $this->actingAs($executive)->get(route('legajos.procesos.show', [$client, $process]));

        $response->assertOk()
            ->assertSee('DNI actualizado')
            ->assertSee('Identidad reservada')
            ->assertDontSee($document->archivo_path)
            ->assertDontSee(route('procesos.documentos.show', [$process, $document]))
            ->assertDontSee(route('procesos.documentos.download', [$process, $document]));
    }

    public function test_users_without_cu18_cannot_open_legajo_by_direct_url(): void
    {
        $secretary = $this->userWithRole('Secretario');
        $client = Cliente::factory()->create();
        $process = $this->processFor($client, $this->userWithRole('Profesional'), $this->userWithRole('Coordinador'));

        $this->actingAs($secretary)->get(route('legajos.index'))->assertForbidden();
        $this->actingAs($secretary)->get(route('legajos.show', $client))->assertForbidden();
        $this->actingAs($secretary)->get(route('legajos.procesos.show', [$client, $process]))->assertForbidden();
    }

    public function test_legajo_process_detail_shows_client_editing_only_to_authorized_roles(): void
    {
        $administrator = $this->userWithRole('Administrador');
        $executive = $this->userWithRole('Directivo');
        $client = Cliente::factory()->create();
        $process = $this->processFor($client, $this->userWithRole('Profesional'), $this->userWithRole('Coordinador'));

        $this->actingAs($executive)
            ->get(route('legajos.procesos.show', [$client, $process]))
            ->assertOk()
            ->assertDontSee('Editar datos del cliente');

        $this->actingAs($administrator)
            ->get(route('legajos.procesos.show', [$client, $process]))
            ->assertOk()
            ->assertSee('Editar datos del cliente');
    }

    public function test_process_state_editing_is_authorized_and_recorded_from_the_detail(): void
    {
        $professional = $this->userWithRole('Profesional');
        $coordinator = $this->userWithRole('Coordinador');
        $executive = $this->userWithRole('Directivo');
        $client = Cliente::factory()->create();
        $process = $this->processFor($client, $professional, $coordinator, 'Proceso editable');
        $state = EstadoProceso::query()->where('slug', 'en_proceso')->firstOrFail();

        $this->actingAs($professional)
            ->get(route('legajos.procesos.show', [$client, $process]))
            ->assertOk()
            ->assertSee('Cambiar fase del caso');

        $this->actingAs($executive)
            ->get(route('legajos.procesos.show', [$client, $process]))
            ->assertOk()
            ->assertDontSee('Cambiar fase del caso');

        $this->actingAs($executive)
            ->patch(route('procesos.estado.update', $process), ['estado' => 'en_proceso'])
            ->assertForbidden();

        $this->actingAs($professional)
            ->patch(route('procesos.estado.update', $process), ['estado' => 'en_proceso', 'motivo' => 'Se inicia la etapa probatoria.'])
            ->assertRedirect(route('legajos.procesos.show', [$client, $process]));

        $this->assertSame('en_proceso', $process->fresh()->estado);
        $this->assertDatabaseHas('historial_estados_proceso', [
            'proceso_id' => $process->id,
            'estado_nuevo' => $state->slug,
            'motivo' => 'Se inicia la etapa probatoria.',
            'usuario_id' => $professional->id,
        ]);
    }

    public function test_scheduling_a_turn_from_process_detail_preserves_and_returns_to_the_origin(): void
    {
        $administrator = $this->userWithRole('Administrador');
        $client = Cliente::factory()->create();
        $professional = $this->userWithRole('Profesional');
        $coordinator = $this->userWithRole('Coordinador');
        $process = $this->processFor($client, $professional, $coordinator);
        $detailUrl = route('legajos.procesos.show', [$client, $process]);
        $createUrl = route('turnos.create', [
            'return_cliente_id' => $client->id,
            'return_proceso_id' => $process->id,
        ]);

        $this->actingAs($administrator)
            ->get($detailUrl)
            ->assertOk()
            ->assertSee($createUrl);

        $createResponse = $this->actingAs($administrator)->get($createUrl);
        $createResponse->assertViewHas('returnClienteId', $client->id);
        $createResponse->assertViewHas('returnProcesoId', $process->id);
        $createResponse->assertOk();

        Livewire::actingAs($administrator)
            ->test(TurnoForm::class, [
                'returnClienteId' => $client->id,
                'returnProcesoId' => $process->id,
            ])
            ->assertSet('returnClienteId', $client->id)
            ->assertSet('returnProcesoId', $process->id)
            ->assertSet('clienteId', (string) $client->id)
            ->assertSet('procesoId', (string) $process->id)
            ->assertSet('profesionalId', (string) $professional->id)
            ->set('fechaHora', now()->addDays(4)->setTime(10, 0)->format('Y-m-d\\TH:i'))
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect($detailUrl);

        $this->assertDatabaseHas('turnos', [
            'cliente_id' => $client->id,
            'proceso_id' => $process->id,
            'profesional_id' => $professional->id,
            'estado' => 'programado',
        ]);
    }

    public function test_editing_a_client_from_process_detail_returns_to_that_process(): void
    {
        $administrator = $this->userWithRole('Administrador');
        $client = Cliente::factory()->create();
        $process = $this->processFor(
            $client,
            $this->userWithRole('Profesional'),
            $this->userWithRole('Coordinador'),
        );
        $detailUrl = route('legajos.procesos.show', [$client, $process]);
        $context = [
            'cliente' => $client,
            'return_cliente_id' => $client->id,
            'return_proceso_id' => $process->id,
        ];

        $this->actingAs($administrator)
            ->get($detailUrl)
            ->assertOk()
            ->assertSee(route('clientes.edit', $context));

        $this->actingAs($administrator)
            ->get(route('clientes.edit', $context))
            ->assertOk()
            ->assertSee($detailUrl);

        $this->actingAs($administrator)
            ->put(route('clientes.update', $context), [
                'nombre' => 'Nombre actualizado',
                'apellido' => $client->apellido,
                'dni' => $client->dni,
                'telefono' => $client->telefono,
                'correo' => $client->correo,
                'domicilio' => $client->domicilio,
                'fecha_nacimiento' => $client->fecha_nacimiento?->format('Y-m-d'),
            ])
            ->assertRedirect($detailUrl);

        $this->assertDatabaseHas('clientes', [
            'id' => $client->id,
            'nombre' => 'Nombre actualizado',
        ]);
    }

    public function test_client_edit_does_not_accept_a_process_from_another_client_as_return_context(): void
    {
        $administrator = $this->userWithRole('Administrador');
        $client = Cliente::factory()->create();
        $otherClient = Cliente::factory()->create();
        $otherProcess = $this->processFor(
            $otherClient,
            $this->userWithRole('Profesional'),
            $this->userWithRole('Coordinador'),
        );
        $context = [
            'cliente' => $client,
            'return_cliente_id' => $client->id,
            'return_proceso_id' => $otherProcess->id,
        ];

        $this->actingAs($administrator)
            ->get(route('clientes.edit', $context))
            ->assertOk()
            ->assertSee(route('clientes.index'))
            ->assertDontSee(route('legajos.procesos.show', [$otherClient, $otherProcess]));
    }

    public function test_coordinator_and_administrator_can_open_a_legajo_with_empty_relations(): void
    {
        $client = Cliente::factory()->create();

        foreach (['Coordinador', 'Administrador'] as $role) {
            $user = $this->userWithRole($role);

            $this->actingAs($user)
                ->get(route('legajos.show', $client))
                ->assertOk()
                ->assertSee('No hay procesos visibles');
        }
    }

    public function test_authorized_user_sees_empty_relations_without_an_error(): void
    {
        $executive = $this->userWithRole('Directivo');
        $client = Cliente::factory()->create();

        $this->actingAs($executive)
            ->get(route('legajos.show', $client))
            ->assertOk()
            ->assertSee('No hay procesos visibles');
    }

    public function test_search_shows_a_clear_empty_state_when_no_client_matches(): void
    {
        $coordinator = $this->userWithRole('Coordinador');
        $client = Cliente::factory()->create([
            'nombre' => 'Cliente Encontrable',
            'dni' => '28123456',
        ]);
        $this->processFor($client, $this->userWithRole('Profesional'), $coordinator);

        Livewire::actingAs($coordinator)
            ->test(Index::class)
            ->set('search', '99999999')
            ->assertSee('No encontramos clientes con esos datos.')
            ->assertDontSee('Cliente Encontrable')
            ->assertDontSee('28123456');
    }

    public function test_related_tables_are_loaded_in_batches_for_the_selected_process_detail(): void
    {
        $executive = $this->userWithRole('Directivo');
        $client = Cliente::factory()->create();
        $professional = $this->userWithRole('Profesional');
        $coordinator = $this->userWithRole('Coordinador');

        foreach (['Proceso uno', 'Proceso dos'] as $name) {
            $process = $this->processFor($client, $professional, $coordinator, $name);
            $document = Documento::factory()->create(['proceso_id' => $process->id]);
            DocumentoVersion::query()->create([
                'documento_id' => $document->id,
                'usuario_id' => $professional->id,
                'archivo_path' => 'documentos/version-anterior.pdf',
                'categoria_id' => null,
                'categoria_nombre' => 'Categoría anterior',
                'tipo_documento' => 'Tipo anterior',
                'nombre' => 'Versión anterior',
                'fecha_reemplazo' => now(),
            ]);
            Reporte::query()->create([
                'proceso_id' => $process->id,
                'profesional_id' => $professional->id,
                'contenido' => 'Reporte del proceso.',
                'fecha' => now()->toDateString(),
            ]);
            Turno::factory()->create([
                'cliente_id' => $client->id,
                'profesional_id' => $professional->id,
                'coordinador_id' => $coordinator->id,
                'proceso_id' => $process->id,
            ]);
        }

        DB::enableQueryLog();
        $this->actingAs($executive)->get(route('legajos.procesos.show', [$client, $client->procesos()->firstOrFail()]))->assertOk();
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $queryCountForTable = static function (string $table) use ($queries): int {
            return count(array_filter($queries, static fn (array $query): bool => preg_match(
                '/\bfrom\s+[`"]?'.preg_quote($table, '/').'[`"]?/i',
                $query['query'],
            ) === 1));
        };

        $this->assertSame(1, $queryCountForTable('documentos'));
        $this->assertSame(1, $queryCountForTable('documento_versiones'));
        $this->assertSame(1, $queryCountForTable('reportes'));
        $this->assertSame(1, $queryCountForTable('historial_estados_proceso'));
        $this->assertSame(1, $queryCountForTable('turnos'));
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function processFor(Cliente $client, User $professional, User $coordinator, string $name = 'Proceso de prueba'): Proceso
    {
        return Proceso::factory()->create([
            'cliente_id' => $client->id,
            'profesional_id' => $professional->id,
            'coordinador_id' => $coordinator->id,
            'servicio_id' => Servicio::factory()->create()->id,
            'nombre' => $name,
        ]);
    }
}
