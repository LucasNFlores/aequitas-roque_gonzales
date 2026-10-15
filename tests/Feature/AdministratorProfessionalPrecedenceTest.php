<?php

namespace Tests\Feature;

use App\Livewire\Legajos\Index as LegajosIndex;
use App\Livewire\Procesos\Index as ProcesosIndex;
use App\Livewire\Turnos\Agenda;
use App\Livewire\Turnos\Index as TurnosIndex;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Models\Proceso;
use App\Models\Reporte;
use App\Models\Servicio;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdministratorProfessionalPrecedenceTest extends TestCase
{
    use RefreshDatabase;

    private string $localStoragePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->localStoragePath = sys_get_temp_dir().'/aequitas-test-storage-'.bin2hex(random_bytes(8));
        config(['filesystems.disks.local.root' => $this->localStoragePath]);
        Storage::forgetDisk('local');
        File::ensureDirectoryExists($this->localStoragePath);
    }

    protected function tearDown(): void
    {
        Storage::forgetDisk('local');
        File::deleteDirectory($this->localStoragePath);

        parent::tearDown();
    }

    public function test_scopes_and_policies_give_administrator_precedence_without_removing_professional_limits(): void
    {
        $records = $this->records();
        $administrator = $records['administrator'];
        $professional = $records['professional'];
        $combined = $records['combined'];

        $allClients = [$records['ownClient']->id, $records['sharedClient']->id, $records['foreignClient']->id];
        $professionalClients = [$records['ownClient']->id, $records['sharedClient']->id];
        $allProcesses = [
            $records['ownProcess']->id,
            $records['sharedAssignedProcess']->id,
            $records['sharedForeignProcess']->id,
            $records['foreignProcess']->id,
        ];
        $professionalProcesses = [$records['ownProcess']->id, $records['sharedAssignedProcess']->id];
        $allDocuments = array_map(static fn (Documento $document): int => $document->id, array_values($records['documents']));
        $professionalDocuments = [$records['documents']['own']->id, $records['documents']['shared_assigned']->id];
        $allReports = array_map(static fn (Reporte $report): int => $report->id, array_values($records['reports']));
        $professionalReports = [$records['reports']['own']->id, $records['reports']['shared_assigned']->id];

        foreach ([$administrator, $combined] as $authorizedAdministrator) {
            $this->assertSame($allClients, Cliente::query()->visibleTo($authorizedAdministrator)->orderBy('id')->pluck('id')->all());
            $this->assertSame($allProcesses, Proceso::query()->visibleTo($authorizedAdministrator)->orderBy('id')->pluck('id')->all());
            $this->assertSame(
                [$records['ownTurno']->id, $records['foreignTurno']->id],
                Turno::query()->visibleTo($authorizedAdministrator)->orderBy('id')->pluck('id')->all(),
            );
            $this->assertSame($allDocuments, Documento::query()->visibleTo($authorizedAdministrator)->orderBy('id')->pluck('id')->all());
            $this->assertSame($allReports, Reporte::query()->visibleTo($authorizedAdministrator)->orderBy('id')->pluck('id')->all());

            $this->assertTrue($authorizedAdministrator->can('view', $records['foreignClient']));
            $this->assertTrue($authorizedAdministrator->can('viewLegajo', $records['foreignClient']));
            $this->assertTrue($authorizedAdministrator->can('view', $records['foreignProcess']));
            $this->assertTrue($authorizedAdministrator->can('createFor', $records['foreignProcess']));
            $this->assertTrue($authorizedAdministrator->can('updateState', $records['foreignProcess']));
            $this->assertTrue($authorizedAdministrator->can('viewHistory', $records['foreignProcess']));
            $this->assertTrue($authorizedAdministrator->can('view', $records['foreignTurno']));
            $this->assertTrue($authorizedAdministrator->can('view', $records['documents']['foreign']));
            $this->assertTrue($authorizedAdministrator->can('download', $records['documents']['foreign']));
            $this->assertTrue($authorizedAdministrator->can('view', $records['reports']['foreign']));
            $this->assertTrue($authorizedAdministrator->can('update', $records['reports']['foreign']));
            $this->assertTrue($authorizedAdministrator->can('delete', $records['reports']['foreign']));
        }

        $this->assertSame($professionalClients, Cliente::query()->visibleTo($professional)->orderBy('id')->pluck('id')->all());
        $this->assertSame($professionalProcesses, Proceso::query()->visibleTo($professional)->orderBy('id')->pluck('id')->all());
        $this->assertSame([$records['ownTurno']->id], Turno::query()->visibleTo($professional)->orderBy('id')->pluck('id')->all());
        $this->assertSame($professionalDocuments, Documento::query()->visibleTo($professional)->orderBy('id')->pluck('id')->all());
        $this->assertSame($professionalReports, Reporte::query()->visibleTo($professional)->orderBy('id')->pluck('id')->all());

        $this->assertTrue($professional->can('view', $records['ownClient']));
        $this->assertFalse($professional->can('view', $records['foreignClient']));
        $this->assertTrue($professional->can('viewLegajo', $records['sharedClient']));
        $this->assertFalse($professional->can('viewLegajo', $records['foreignClient']));
        $this->assertTrue($professional->can('view', $records['ownProcess']));
        $this->assertFalse($professional->can('view', $records['foreignProcess']));
        $this->assertTrue($professional->can('createFor', $records['ownProcess']));
        $this->assertFalse($professional->can('createFor', $records['foreignProcess']));
        $this->assertTrue($professional->can('updateState', $records['ownProcess']));
        $this->assertFalse($professional->can('updateState', $records['foreignProcess']));
        $this->assertTrue($professional->can('viewHistory', $records['ownProcess']));
        $this->assertFalse($professional->can('viewHistory', $records['foreignProcess']));
        $this->assertTrue($professional->can('view', $records['ownTurno']));
        $this->assertFalse($professional->can('view', $records['foreignTurno']));
        $this->assertTrue($professional->can('view', $records['documents']['own']));
        $this->assertTrue($professional->can('download', $records['documents']['own']));
        $this->assertFalse($professional->can('view', $records['documents']['foreign']));
        $this->assertFalse($professional->can('download', $records['documents']['foreign']));
        $this->assertTrue($professional->can('view', $records['reports']['own']));
        $this->assertFalse($professional->can('view', $records['reports']['foreign']));
        $this->assertFalse($professional->can('update', $records['reports']['foreign']));
        $this->assertFalse($professional->can('delete', $records['reports']['foreign']));
    }

    public function test_only_administrator_and_combined_profile_get_unfiltered_client_process_and_legajo_views(): void
    {
        $records = $this->records();

        foreach ([$records['administrator'], $records['combined']] as $administrator) {
            $this->actingAs($administrator)
                ->get(route('clientes.index'))
                ->assertOk()
                ->assertSee('Cliente propio')
                ->assertSee('Cliente compartido')
                ->assertSee('Cliente sin asignación');

            $this->actingAs($administrator)
                ->get(route('clientes.show', $records['foreignClient']))
                ->assertOk()
                ->assertSee('Proceso sin asignación');

            Livewire::actingAs($administrator)
                ->test(ProcesosIndex::class)
                ->assertSee('Proceso propio')
                ->assertSee('Proceso asignado compartido')
                ->assertSee('Proceso ajeno compartido')
                ->assertSee('Proceso sin asignación')
                ->assertSee($records['otherProfessional']->name);

            Livewire::actingAs($administrator)
                ->test(LegajosIndex::class)
                ->assertSee('Cliente sin asignación');

            $this->actingAs($administrator)
                ->get(route('legajos.show', $records['sharedClient']))
                ->assertOk()
                ->assertSee('Proceso asignado compartido')
                ->assertSee('Proceso ajeno compartido')
                ->assertSee('Reporte compartido ajeno');

            $this->actingAs($administrator)
                ->get(route('legajos.show', $records['foreignClient']))
                ->assertOk()
                ->assertSee('Proceso sin asignación')
                ->assertSee('Reporte sin asignación');
        }

        $this->actingAs($records['professional'])
            ->get(route('clientes.index'))
            ->assertForbidden();

        $this->actingAs($records['professional'])
            ->get(route('clientes.show', $records['sharedClient']))
            ->assertOk()
            ->assertSee('Proceso asignado compartido')
            ->assertDontSee('Proceso ajeno compartido');

        $this->actingAs($records['professional'])
            ->get(route('clientes.show', $records['foreignClient']))
            ->assertForbidden();

        Livewire::actingAs($records['professional'])
            ->test(ProcesosIndex::class)
            ->assertSee('Proceso propio')
            ->assertSee('Proceso asignado compartido')
            ->assertDontSee('Proceso ajeno compartido')
            ->assertDontSee('Proceso sin asignación');

        Livewire::actingAs($records['professional'])
            ->test(LegajosIndex::class)
            ->assertSee('Cliente compartido')
            ->assertDontSee('Cliente sin asignación');

        $this->actingAs($records['professional'])
            ->get(route('legajos.show', $records['sharedClient']))
            ->assertOk()
            ->assertSee('Proceso asignado compartido')
            ->assertDontSee('Proceso ajeno compartido')
            ->assertDontSee('Reporte compartido ajeno');

        $this->actingAs($records['professional'])
            ->get(route('legajos.show', $records['foreignClient']))
            ->assertForbidden();

        Livewire::actingAs($records['combined'])
            ->test(ProcesosIndex::class)
            ->call('openHistory', $records['foreignProcess']->id)
            ->assertSet('showHistoryModal', true);

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($records['professional'])
            ->test(ProcesosIndex::class)
            ->call('openHistory', $records['foreignProcess']->id);
    }

    public function test_agenda_and_turno_lists_apply_professional_filters_only_without_administrator(): void
    {
        $records = $this->records();

        foreach ([$records['administrator'], $records['combined']] as $administrator) {
            Livewire::actingAs($administrator)
                ->test(Agenda::class)
                ->assertSee('Cliente propio')
                ->assertSee('Cliente sin asignación')
                ->set('profesionalId', (string) $records['otherProfessional']->id)
                ->call('applyFilters')
                ->assertHasNoErrors()
                ->assertDontSee('Cliente propio')
                ->assertSee('Cliente sin asignación');

            Livewire::actingAs($administrator)
                ->test(TurnosIndex::class)
                ->assertSee('Cliente propio')
                ->assertSee('Cliente sin asignación')
                ->set('profesionalId', (string) $records['otherProfessional']->id)
                ->assertDontSee('Cliente propio')
                ->assertSee('Cliente sin asignación');
        }

        Livewire::actingAs($records['professional'])
            ->test(Agenda::class)
            ->assertSee('Cliente propio')
            ->assertDontSee('Cliente sin asignación')
            ->set('profesionalId', (string) $records['otherProfessional']->id)
            ->call('applyFilters')
            ->assertHasNoErrors()
            ->assertSee('Cliente propio')
            ->assertDontSee('Cliente sin asignación');

        Livewire::actingAs($records['professional'])
            ->test(TurnosIndex::class)
            ->assertSee('Cliente propio')
            ->assertDontSee('Cliente sin asignación')
            ->set('profesionalId', (string) $records['otherProfessional']->id)
            ->assertSee('Cliente propio')
            ->assertDontSee('Cliente sin asignación');

        $this->actingAs($records['administrator'])->get(route('turnos.show', $records['foreignTurno']))->assertOk();
        $this->actingAs($records['combined'])->get(route('turnos.show', $records['foreignTurno']))->assertOk();
        $this->actingAs($records['professional'])->get(route('turnos.show', $records['foreignTurno']))->assertForbidden();
        $this->actingAs($records['professional'])->get(route('turnos.show', $records['ownTurno']))->assertOk();
    }

    public function test_document_view_download_and_version_routes_respect_the_same_precedence(): void
    {
        $records = $this->records();
        $foreignDocument = $records['documents']['foreign'];
        $foreignProcess = $records['foreignProcess'];
        $version = $records['version'];

        foreach ([$records['administrator'], $records['combined']] as $administrator) {
            $this->actingAs($administrator)
                ->get(route('procesos.documentos.show', [$foreignProcess, $foreignDocument]))
                ->assertOk();

            $this->actingAs($administrator)
                ->get(route('procesos.documentos.download', [$foreignProcess, $foreignDocument]))
                ->assertOk();

            $this->actingAs($administrator)
                ->get(route('procesos.documentos.versiones.show', [$foreignProcess, $foreignDocument, $version]))
                ->assertOk();

            $this->actingAs($administrator)
                ->get(route('procesos.documentos.versiones.download', [$foreignProcess, $foreignDocument, $version]))
                ->assertOk();
        }

        $this->actingAs($records['professional'])
            ->get(route('procesos.documentos.show', [$records['ownProcess'], $records['documents']['own']]))
            ->assertOk();

        $this->actingAs($records['professional'])
            ->get(route('procesos.documentos.download', [$records['ownProcess'], $records['documents']['own']]))
            ->assertOk();

        $this->actingAs($records['professional'])
            ->get(route('procesos.documentos.show', [$foreignProcess, $foreignDocument]))
            ->assertForbidden();

        $this->actingAs($records['professional'])
            ->get(route('procesos.documentos.download', [$foreignProcess, $foreignDocument]))
            ->assertForbidden();

        $this->actingAs($records['professional'])
            ->get(route('procesos.documentos.versiones.show', [$foreignProcess, $foreignDocument, $version]))
            ->assertForbidden();

        $this->actingAs($records['professional'])
            ->get(route('procesos.documentos.versiones.download', [$foreignProcess, $foreignDocument, $version]))
            ->assertForbidden();
    }

    public function test_combined_administrator_can_manage_reports_on_unassigned_processes(): void
    {
        $records = $this->records();
        $foreignProcess = $records['foreignProcess'];
        $combined = $records['combined'];

        $this->actingAs($combined)
            ->from(route('legajos.show', $records['foreignClient']))
            ->post(route('procesos.reportes.store', $foreignProcess), [
                'proceso_id' => $foreignProcess->id,
                'contenido' => 'Reporte creado por administrador combinado',
                'fecha' => now()->toDateString(),
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $createdReport = Reporte::query()
            ->where('contenido', 'Reporte creado por administrador combinado')
            ->sole();

        $this->assertSame($foreignProcess->profesional_id, $createdReport->profesional_id);
        $this->assertSame($combined->id, $createdReport->registrado_por_id);
        $this->assertSame($foreignProcess->id, $createdReport->proceso_id);

        $this->actingAs($combined)
            ->put(route('procesos.reportes.update', [$foreignProcess, $records['reports']['foreign']]), [
                'contenido' => 'Reporte ajeno actualizado por administrador',
                'fecha' => now()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('reportes', [
            'id' => $records['reports']['foreign']->id,
            'contenido' => 'Reporte ajeno actualizado por administrador',
        ]);

        $this->actingAs($combined)
            ->delete(route('procesos.reportes.destroy', [$foreignProcess, $records['reports']['foreign']]))
            ->assertRedirect();

        $this->assertSoftDeleted($records['reports']['foreign']);

        $this->actingAs($records['professional'])
            ->from(route('legajos.index'))
            ->post(route('procesos.reportes.store', $foreignProcess), [
                'proceso_id' => $foreignProcess->id,
                'contenido' => 'Reporte profesional no autorizado',
                'fecha' => now()->toDateString(),
            ])
            ->assertRedirect(route('legajos.index'))
            ->assertSessionHasErrors('proceso_id');

        $this->assertDatabaseMissing('reportes', ['contenido' => 'Reporte profesional no autorizado']);
    }

    public function test_professional_combined_with_another_non_admin_role_keeps_assignment_scope(): void
    {
        $records = $this->records();
        $professionalCoordinator = $this->userWithRoles('Profesional', 'Coordinador');

        $this->assertSame([], Proceso::query()->visibleTo($professionalCoordinator)->pluck('id')->all());
        $this->assertSame([], Cliente::query()->visibleTo($professionalCoordinator)->pluck('id')->all());
        $this->assertSame([], Turno::query()->visibleTo($professionalCoordinator)->pluck('id')->all());
        $this->assertSame([], Documento::query()->visibleTo($professionalCoordinator)->pluck('id')->all());
        $this->assertSame([], Reporte::query()->visibleTo($professionalCoordinator)->pluck('id')->all());
        $this->assertFalse($professionalCoordinator->can('view', $records['foreignProcess']));
    }

    /**
     * @return array{
     *     administrator: User,
     *     professional: User,
     *     otherProfessional: User,
     *     combined: User,
     *     coordinator: User,
     *     ownClient: Cliente,
     *     sharedClient: Cliente,
     *     foreignClient: Cliente,
     *     ownProcess: Proceso,
     *     sharedAssignedProcess: Proceso,
     *     sharedForeignProcess: Proceso,
     *     foreignProcess: Proceso,
     *     ownTurno: Turno,
     *     foreignTurno: Turno,
     *     documents: array<string, Documento>,
     *     reports: array<string, Reporte>,
     *     version: DocumentoVersion
     * }
     */
    private function records(): array
    {
        $administrator = $this->userWithRoles('Administrador');
        $professional = $this->userWithRoles('Profesional');
        $otherProfessional = $this->userWithRoles('Profesional');
        $combined = $this->userWithRoles('Administrador', 'Profesional');
        $coordinator = $this->userWithRoles('Coordinador');
        $service = Servicio::factory()->create();

        $ownClient = Cliente::factory()->create(['nombre' => 'Cliente propio', 'apellido' => 'Acceso']);
        $sharedClient = Cliente::factory()->create(['nombre' => 'Cliente compartido', 'apellido' => 'Acceso']);
        $foreignClient = Cliente::factory()->create(['nombre' => 'Cliente sin asignación', 'apellido' => 'Acceso']);

        $ownProcess = $this->processFor($ownClient, $professional, $coordinator, $service, 'Proceso propio');
        $sharedAssignedProcess = $this->processFor($sharedClient, $professional, $coordinator, $service, 'Proceso asignado compartido');
        $sharedForeignProcess = $this->processFor($sharedClient, $otherProfessional, $coordinator, $service, 'Proceso ajeno compartido');
        $foreignProcess = $this->processFor($foreignClient, $otherProfessional, $coordinator, $service, 'Proceso sin asignación');

        $ownTurno = Turno::factory()->create([
            'cliente_id' => $ownClient->id,
            'profesional_id' => $professional->id,
            'coordinador_id' => $coordinator->id,
            'proceso_id' => $ownProcess->id,
            'fecha_hora' => now()->addDays(5)->setTime(9, 0),
        ]);
        $foreignTurno = Turno::factory()->create([
            'cliente_id' => $foreignClient->id,
            'profesional_id' => $otherProfessional->id,
            'coordinador_id' => $coordinator->id,
            'proceso_id' => $foreignProcess->id,
            'fecha_hora' => now()->addDays(6)->setTime(10, 0),
        ]);

        $processes = [
            'own' => $ownProcess,
            'shared_assigned' => $sharedAssignedProcess,
            'shared_foreign' => $sharedForeignProcess,
            'foreign' => $foreignProcess,
        ];
        $documents = [];
        $reports = [];

        foreach ($processes as $key => $process) {
            $documentPath = 'documentos/'.$key.'-documento.pdf';
            Storage::disk('local')->put($documentPath, '%PDF-1.4 documento '.$key);
            $documents[$key] = Documento::factory()->create([
                'proceso_id' => $process->id,
                'archivo_path' => $documentPath,
                'nombre' => 'Documento '.$key,
            ]);

            $reports[$key] = Reporte::factory()->create([
                'proceso_id' => $process->id,
                'profesional_id' => $process->profesional_id,
                'contenido' => match ($key) {
                    'own' => 'Reporte propio',
                    'shared_assigned' => 'Reporte compartido propio',
                    'shared_foreign' => 'Reporte compartido ajeno',
                    default => 'Reporte sin asignación',
                },
                'fecha' => now()->toDateString(),
            ]);
        }

        $versionPath = 'documentos/versiones/documento-anterior.pdf';
        Storage::disk('local')->put($versionPath, '%PDF-1.4 versión anterior');
        $version = DocumentoVersion::query()->create([
            'documento_id' => $documents['foreign']->id,
            'usuario_id' => $otherProfessional->id,
            'archivo_path' => $versionPath,
            'categoria_id' => null,
            'categoria_nombre' => 'Categoría anterior',
            'tipo_documento' => 'Prueba',
            'nombre' => 'Documento anterior',
            'fecha_reemplazo' => now()->subDay(),
        ]);

        return compact(
            'administrator',
            'professional',
            'otherProfessional',
            'combined',
            'coordinator',
            'ownClient',
            'sharedClient',
            'foreignClient',
            'ownProcess',
            'sharedAssignedProcess',
            'sharedForeignProcess',
            'foreignProcess',
            'ownTurno',
            'foreignTurno',
            'documents',
            'reports',
            'version',
        );
    }

    private function userWithRoles(string ...$roles): User
    {
        $user = User::factory()->create();

        foreach ($roles as $role) {
            $user->assignRole($role);
        }

        return $user;
    }

    private function processFor(Cliente $client, User $professional, User $coordinator, Servicio $service, string $name): Proceso
    {
        return Proceso::factory()->create([
            'cliente_id' => $client->id,
            'profesional_id' => $professional->id,
            'coordinador_id' => $coordinator->id,
            'servicio_id' => $service->id,
            'nombre' => $name,
        ]);
    }
}
