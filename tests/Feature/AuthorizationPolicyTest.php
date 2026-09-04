<?php

namespace Tests\Feature;

use App\Http\Requests\StoreDocumentoRequest;
use App\Http\Requests\StoreProcesoRequest;
use App\Http\Requests\StoreReporteRequest;
use App\Http\Requests\StoreTurnoRequest;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Proceso;
use App\Models\Reporte;
use App\Models\Servicio;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AuthorizationPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_professional_is_limited_to_assigned_processes_and_clients(): void
    {
        $professional = $this->userWithRole('Profesional');
        $otherProfessional = $this->userWithRole('Profesional');
        $coordinator = $this->userWithRole('Coordinador');
        $assignedClient = Cliente::factory()->create();
        $otherClient = Cliente::factory()->create();

        $assignedProcess = $this->processFor($professional, $coordinator, $assignedClient);
        $otherProcess = $this->processFor($otherProfessional, $coordinator, $otherClient);

        $this->assertTrue($professional->can('view', $assignedProcess));
        $this->assertFalse($professional->can('view', $otherProcess));
        $this->assertTrue($professional->can('view', $assignedClient));
        $this->assertFalse($professional->can('view', $otherClient));
        $this->assertSame(
            [$assignedProcess->id],
            Proceso::query()->visibleTo($professional)->pluck('id')->all(),
        );
        $this->assertSame(
            [$assignedClient->id],
            Cliente::query()->visibleTo($professional)->pluck('id')->all(),
        );
    }

    public function test_process_actions_follow_the_role_matrix(): void
    {
        $professional = $this->userWithRole('Profesional');
        $secretary = $this->userWithRole('Secretario');
        $coordinator = $this->userWithRole('Coordinador');
        $executive = $this->userWithRole('Directivo');
        $administrator = $this->userWithRole('Administrador');
        $process = $this->processFor($professional, $coordinator);

        $this->assertTrue($coordinator->can('admit', $process));
        $this->assertTrue($coordinator->can('reject', $process));
        $this->assertTrue($coordinator->can('assignProfessional', $process));
        $this->assertTrue($coordinator->can('reassignProfessional', $process));
        $this->assertTrue($coordinator->can('updateState', $process));
        $this->assertTrue($secretary->can('assignProfessional', $process));
        $this->assertTrue($secretary->can('reassignProfessional', $process));
        $this->assertFalse($secretary->can('admit', $process));
        $this->assertTrue($professional->can('updateState', $process));
        $this->assertFalse($executive->can('updateState', $process));
        $this->assertTrue($executive->can('view', $process));
        $this->assertTrue($administrator->can('admit', $process));
        $this->assertTrue($administrator->can('delete', $process));
    }

    public function test_document_and_report_policies_enforce_role_and_ownership_scope(): void
    {
        $professional = $this->userWithRole('Profesional');
        $otherProfessional = $this->userWithRole('Profesional');
        $secretary = $this->userWithRole('Secretario');
        $coordinator = $this->userWithRole('Coordinador');
        $executive = $this->userWithRole('Directivo');
        $administrator = $this->userWithRole('Administrador');
        $assignedProcess = $this->processFor($professional, $coordinator);
        $otherProcess = $this->processFor($otherProfessional, $coordinator);
        $assignedDocument = Documento::factory()->create(['proceso_id' => $assignedProcess->id]);
        $otherDocument = Documento::factory()->create(['proceso_id' => $otherProcess->id]);
        $ownReport = Reporte::factory()->create([
            'proceso_id' => $assignedProcess->id,
            'profesional_id' => $professional->id,
        ]);
        $otherReport = Reporte::factory()->create([
            'proceso_id' => $otherProcess->id,
            'profesional_id' => $otherProfessional->id,
        ]);

        $this->assertTrue($professional->can('view', $assignedDocument));
        $this->assertTrue($professional->can('download', $assignedDocument));
        $this->assertFalse($professional->can('view', $otherDocument));
        $this->assertFalse($professional->can('download', $otherDocument));
        $this->assertTrue($secretary->can('view', $assignedDocument));
        $this->assertFalse($secretary->can('download', $assignedDocument));
        $this->assertTrue($coordinator->can('download', $assignedDocument));
        $this->assertFalse($executive->can('view', $assignedDocument));
        $this->assertTrue($administrator->can('download', $assignedDocument));

        $this->assertTrue($professional->can('view', $ownReport));
        $this->assertFalse($professional->can('view', $otherReport));
        $this->assertTrue($professional->can('update', $ownReport));
        $this->assertFalse($professional->can('update', $otherReport));
        $this->assertTrue($professional->can('delete', $ownReport));
        $this->assertTrue($coordinator->can('view', $ownReport));
        $this->assertTrue($executive->can('view', $ownReport));
        $this->assertTrue($administrator->can('delete', $otherReport));
        $this->assertSame(
            [$assignedDocument->id],
            Documento::query()->visibleTo($professional)->pluck('id')->all(),
        );
        $this->assertSame(
            [$ownReport->id],
            Reporte::query()->visibleTo($professional)->pluck('id')->all(),
        );
    }

    public function test_client_and_user_deletion_are_blocked_while_they_have_active_processes(): void
    {
        $secretary = $this->userWithRole('Secretario');
        $coordinator = $this->userWithRole('Coordinador');
        $client = Cliente::factory()->create();
        $professional = $this->userWithRole('Profesional');
        $process = $this->processFor($professional, $coordinator, $client);

        $this->assertFalse($secretary->can('delete', $client));

        $process->update(['estado' => 'finalizado']);

        $this->assertTrue($secretary->can('delete', $client));

        $executive = $this->userWithRole('Directivo');
        $target = $this->userWithRole('Profesional');
        $userProcess = $this->processFor($target, $coordinator);

        $this->assertFalse($executive->can('delete', $target));

        $userProcess->update(['estado' => 'rechazado']);

        $this->assertTrue($executive->can('delete', $target));
    }

    public function test_file_and_state_validation_rules_are_present(): void
    {
        $documentRules = (new StoreDocumentoRequest())->rules();
        $this->assertContains('mimes:pdf', $documentRules['archivo']);
        $this->assertContains('max:20480', $documentRules['archivo']);

        $oversizedFile = UploadedFile::fake()->create('documento.pdf', 20481, 'application/pdf');
        $fileValidator = Validator::make(
            ['archivo' => $oversizedFile],
            ['archivo' => $documentRules['archivo']],
        );

        $this->assertTrue($fileValidator->fails());

        $stateValidator = Validator::make(
            ['estado' => 'iniciado'],
            (new StoreProcesoRequest())->rules(),
        );

        $this->assertTrue($stateValidator->fails());
        $this->assertTrue($stateValidator->errors()->has('estado'));
    }

    public function test_turno_validation_rejects_inconsistent_external_data(): void
    {
        $request = StoreTurnoRequest::create('/', 'POST', [
            'es_externo' => false,
            'tipo' => 'externo',
            'detalle_externo' => null,
        ]);
        $validator = Validator::make($request->all(), $request->rules());

        foreach ($request->after() as $after) {
            $validator->after($after);
        }

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('tipo'));
    }

    public function test_professional_report_validation_rejects_an_unassigned_process(): void
    {
        $professional = $this->userWithRole('Profesional');
        $otherProfessional = $this->userWithRole('Profesional');
        $coordinator = $this->userWithRole('Coordinador');
        $process = $this->processFor($otherProfessional, $coordinator);
        $request = StoreReporteRequest::create('/', 'POST', ['proceso_id' => $process->id]);
        $request->setUserResolver(fn (): User => $professional);
        $validator = Validator::make($request->all(), $request->rules());

        foreach ($request->after() as $after) {
            $validator->after($after);
        }

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('proceso_id'));
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function processFor(User $professional, User $coordinator, ?Cliente $client = null): Proceso
    {
        return Proceso::factory()->create([
            'cliente_id' => $client?->id ?? Cliente::factory(),
            'profesional_id' => $professional->id,
            'servicio_id' => Servicio::factory(),
            'coordinador_id' => $coordinator->id,
        ]);
    }
}
