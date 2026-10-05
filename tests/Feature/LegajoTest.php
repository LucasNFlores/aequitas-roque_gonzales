<?php

namespace Tests\Feature;

use App\Livewire\Legajos\Index;
use App\Models\CategoriaDocumento;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Models\Proceso;
use App\Models\Reporte;
use App\Models\Servicio;
use App\Models\Turno;
use App\Models\User;
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
        $this->processFor($client, $otherProfessional, $coordinator, 'Proceso ajeno');

        Turno::factory()->create([
            'cliente_id' => $client->id,
            'profesional_id' => $professional->id,
            'proceso_id' => $assignedProcess->id,
        ]);
        Turno::factory()->create([
            'cliente_id' => $client->id,
            'profesional_id' => $otherProfessional->id,
            'proceso_id' => null,
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
            ->assertDontSee('Proceso ajeno')
            ->assertDontSee('Turno de otro profesional');
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

        $response = $this->actingAs($executive)->get(route('legajos.show', $client));

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

        $this->actingAs($secretary)->get(route('legajos.index'))->assertForbidden();
        $this->actingAs($secretary)->get(route('legajos.show', $client))->assertForbidden();
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

    public function test_related_tables_are_loaded_in_batches_for_multiple_processes(): void
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
        $this->actingAs($executive)->get(route('legajos.show', $client))->assertOk();
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
        $this->assertSame(2, $queryCountForTable('turnos'));
        $this->assertSame(1, $queryCountForTable('comprobante_pagos'));
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
