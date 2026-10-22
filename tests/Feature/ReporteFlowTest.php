<?php

namespace Tests\Feature;

use App\Models\Proceso;
use App\Models\Reporte;
use App\Models\ReporteVersion;
use App\Models\Servicio;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_assigned_professional_can_create_edit_and_soft_delete_a_report(): void
    {
        $professional = $this->userWithRole('Profesional');
        $process = $this->processFor($professional);

        $this->assertTrue($professional->can('registrar_reportes'));
        $this->assertTrue($professional->can('createFor', $process));

        $this->actingAs($professional)
            ->post(route('procesos.reportes.store', $process), [
                'proceso_id' => $process->id,
                'contenido' => 'Primera consulta completada.',
                'fecha' => now()->toDateString(),
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $report = Reporte::query()->sole();
        $this->assertSame($professional->id, $report->profesional_id);
        $this->assertSame($process->id, $report->proceso_id);

        $this->actingAs($professional)
            ->put(route('procesos.reportes.update', [$process, $report]), [
                'contenido' => 'Se recibió la documentación pendiente.',
                'fecha' => now()->subDay()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('reportes', [
            'id' => $report->id,
            'contenido' => 'Se recibió la documentación pendiente.',
        ]);
        $this->assertDatabaseHas('reporte_versiones', [
            'reporte_id' => $report->id,
            'contenido_anterior' => 'Primera consulta completada.',
            'usuario_id' => $professional->id,
            'usuario_nombre' => $professional->nombre_completo,
        ]);
        $this->assertSame(now()->toDateString(), ReporteVersion::query()->sole()->fecha_anterior->toDateString());

        $this->actingAs($professional)
            ->delete(route('procesos.reportes.destroy', [$process, $report]))
            ->assertRedirect();

        $this->assertSoftDeleted($report);
        $this->assertDatabaseHas('reporte_versiones', ['reporte_id' => $report->id]);
    }

    public function test_editing_without_changes_does_not_create_a_version_and_multiple_edits_keep_snapshots(): void
    {
        $professional = $this->userWithRole('Profesional');
        $process = $this->processFor($professional);
        $report = Reporte::factory()->create([
            'proceso_id' => $process->id,
            'profesional_id' => $professional->id,
            'contenido' => 'Versión inicial.',
            'fecha' => now()->subDays(2)->toDateString(),
        ]);
        $url = route('procesos.reportes.update', [$process, $report]);

        $this->actingAs($professional)
            ->put($url, [
                'contenido' => 'Versión inicial.',
                'fecha' => now()->subDays(2)->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('reporte_versiones', 0);

        $this->actingAs($professional)
            ->put($url, ['contenido' => 'Primera edición.'])
            ->assertRedirect();
        $this->actingAs($professional)
            ->put($url, ['contenido' => 'Segunda edición.'])
            ->assertRedirect();

        $this->assertDatabaseCount('reporte_versiones', 2);
        $this->assertSame(
            ['Versión inicial.', 'Primera edición.'],
            ReporteVersion::query()->where('reporte_id', $report->id)->orderBy('id')->pluck('contenido_anterior')->all(),
        );
    }

    public function test_administrator_can_edit_another_authors_report_and_history_records_the_editor(): void
    {
        $professional = $this->userWithRole('Profesional');
        $administrator = $this->userWithRole('Administrador');
        $process = $this->processFor($professional);
        $report = Reporte::factory()->create([
            'proceso_id' => $process->id,
            'profesional_id' => $professional->id,
            'contenido' => 'Contenido del profesional.',
        ]);

        $this->actingAs($administrator)
            ->put(route('procesos.reportes.update', [$process, $report]), ['contenido' => 'Contenido corregido por administración.'])
            ->assertRedirect();

        $this->assertDatabaseHas('reporte_versiones', [
            'reporte_id' => $report->id,
            'contenido_anterior' => 'Contenido del profesional.',
            'usuario_id' => $administrator->id,
            'usuario_nombre' => $administrator->nombre_completo,
        ]);
        $this->assertDatabaseHas('reportes', [
            'id' => $report->id,
            'profesional_id' => $professional->id,
            'contenido' => 'Contenido corregido por administración.',
        ]);
    }

    public function test_administrator_can_register_for_the_assigned_professional_and_both_can_see_the_report(): void
    {
        $professional = $this->userWithRole('Profesional');
        $administrator = $this->userWithRole('Administrador');
        $process = $this->processFor($professional);

        $this->actingAs($administrator)
            ->post(route('procesos.reportes.store', $process), [
                'proceso_id' => $process->id,
                'contenido' => 'Registro administrativo para el profesional asignado.',
                'fecha' => now()->toDateString(),
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $report = Reporte::query()->sole();

        $this->assertSame($professional->id, $report->profesional_id);
        $this->assertSame($administrator->id, $report->registrado_por_id);
        $this->assertSame([$report->id], Reporte::query()->visibleTo($professional)->pluck('id')->all());
        $this->assertSame([$report->id], Reporte::query()->visibleTo($administrator)->pluck('id')->all());

        $this->actingAs($administrator)
            ->get(route('legajos.procesos.show', [$process->cliente, $process]))
            ->assertOk()
            ->assertSee('Autor: '.$professional->nombre_completo.' · Registrado por '.$administrator->nombre_completo);
    }

    public function test_professional_report_scope_excludes_reports_from_processes_reassigned_to_someone_else(): void
    {
        $author = $this->userWithRole('Profesional');
        $currentProfessional = $this->userWithRole('Profesional');
        $process = $this->processFor($author);
        $report = Reporte::factory()->create([
            'proceso_id' => $process->id,
            'profesional_id' => $author->id,
            'fecha' => now()->toDateString(),
        ]);

        $process->update(['profesional_id' => $currentProfessional->id]);

        $this->assertFalse($author->can('view', $report));
        $this->assertSame([], Reporte::query()->visibleTo($author)->pluck('id')->all());
        $this->assertSame([], Reporte::query()->visibleTo($currentProfessional)->pluck('id')->all());
        $this->assertSame([$report->id], Reporte::query()->visibleTo($this->userWithRole('Administrador'))->pluck('id')->all());
    }

    public function test_administrator_cannot_register_a_report_without_an_assigned_professional(): void
    {
        $administrator = $this->userWithRole('Administrador');
        $professional = $this->userWithRole('Profesional');
        $process = $this->processFor($professional);
        $process->update(['profesional_id' => null]);

        $this->actingAs($administrator)
            ->from(route('legajos.index'))
            ->post(route('procesos.reportes.store', $process), [
                'proceso_id' => $process->id,
                'contenido' => 'Sin autor profesional.',
                'fecha' => now()->toDateString(),
            ])
            ->assertRedirect(route('legajos.index'))
            ->assertSessionHasErrors('proceso_id');

        $this->assertDatabaseCount('reportes', 0);
    }

    public function test_only_report_author_and_administrator_can_soft_delete_directly(): void
    {
        $author = $this->userWithRole('Profesional');
        $otherProfessional = $this->userWithRole('Profesional');
        $process = $this->processFor($author);
        $report = Reporte::factory()->create([
            'proceso_id' => $process->id,
            'profesional_id' => $author->id,
        ]);
        $url = route('procesos.reportes.destroy', [$process, $report]);

        $this->actingAs($otherProfessional)
            ->delete($url)
            ->assertForbidden();

        $this->actingAs($this->userWithRole('Administrador'))
            ->delete($url)
            ->assertRedirect();

        $this->assertSoftDeleted($report);
        $this->assertSame([], Reporte::query()->visibleTo($author)->pluck('id')->all());
        $this->actingAs($author)
            ->get(route('procesos.reportes.historial', [$process, $report]))
            ->assertNotFound();
    }

    public function test_direct_update_rejects_non_author_professionals_and_roles_without_cu22(): void
    {
        $author = $this->userWithRole('Profesional');
        $process = $this->processFor($author);
        $report = Reporte::factory()->create([
            'proceso_id' => $process->id,
            'profesional_id' => $author->id,
            'contenido' => 'Contenido original.',
        ]);
        $url = route('procesos.reportes.update', [$process, $report]);

        foreach (['Profesional', 'Coordinador', 'Directivo', 'Secretario'] as $role) {
            $user = $this->userWithRole($role);

            $this->actingAs($user)
                ->put($url, ['contenido' => 'Intento no autorizado.'])
                ->assertForbidden();
        }

        $this->assertDatabaseHas('reportes', [
            'id' => $report->id,
            'contenido' => 'Contenido original.',
        ]);
        $this->assertDatabaseCount('reporte_versiones', 0);
    }

    public function test_direct_history_request_obeys_cu24_roles_and_professional_scope(): void
    {
        $professional = $this->userWithRole('Profesional');
        $process = $this->processFor($professional);
        $report = Reporte::factory()->create([
            'proceso_id' => $process->id,
            'profesional_id' => $professional->id,
        ]);
        $version = $report->versiones()->create([
            'contenido_anterior' => 'Texto anterior confidencial.',
            'fecha_anterior' => now()->subDay()->toDateString(),
            'usuario_id' => $professional->id,
            'usuario_nombre' => $professional->nombre_completo,
            'fecha_cambio' => now(),
        ]);
        $historyUrl = route('procesos.reportes.historial', [$process, $report]);

        foreach ([$professional, $this->userWithRole('Coordinador'), $this->userWithRole('Directivo'), $this->userWithRole('Administrador')] as $authorizedUser) {
            $this->actingAs($authorizedUser)
                ->get($historyUrl)
                ->assertOk()
                ->assertSee('Texto anterior confidencial.')
                ->assertSee($version->usuario_nombre);
        }

        $this->actingAs($this->userWithRole('Secretario'))
            ->get($historyUrl)
            ->assertForbidden();

        $otherProfessional = $this->userWithRole('Profesional');
        $this->actingAs($otherProfessional)
            ->get($historyUrl)
            ->assertForbidden();
    }

    public function test_report_history_returns_to_its_process_detail(): void
    {
        $professional = $this->userWithRole('Profesional');
        $process = $this->processFor($professional);
        $report = Reporte::factory()->create([
            'proceso_id' => $process->id,
            'profesional_id' => $professional->id,
        ]);
        $detailUrl = route('legajos.procesos.show', [$process->cliente_id, $process]);

        $this->actingAs($professional)
            ->get(route('procesos.reportes.historial', [$process, $report]))
            ->assertOk()
            ->assertSee('Volver al proceso')
            ->assertSee($detailUrl);
    }

    public function test_professional_author_cannot_access_history_after_process_assignment_changes(): void
    {
        $author = $this->userWithRole('Profesional');
        $newAssignee = $this->userWithRole('Profesional');
        $process = $this->processFor($author);
        $report = Reporte::factory()->create([
            'proceso_id' => $process->id,
            'profesional_id' => $author->id,
        ]);
        $process->update(['profesional_id' => $newAssignee->id]);

        $this->actingAs($author)
            ->get(route('procesos.reportes.historial', [$process, $report]))
            ->assertForbidden();
    }

    public function test_professional_cannot_create_a_report_for_an_unassigned_process(): void
    {
        $professional = $this->userWithRole('Profesional');
        $otherProfessional = $this->userWithRole('Profesional');
        $process = $this->processFor($otherProfessional);

        $this->actingAs($professional)
            ->from(route('legajos.index'))
            ->post(route('procesos.reportes.store', $process), [
                'proceso_id' => $process->id,
                'contenido' => 'No autorizado.',
                'fecha' => now()->toDateString(),
            ])
            ->assertRedirect(route('legajos.index'))
            ->assertSessionHasErrors('proceso_id');

        $this->assertDatabaseCount('reportes', 0);
    }

    public function test_report_process_id_must_match_the_nested_process_route(): void
    {
        $professional = $this->userWithRole('Profesional');
        $routeProcess = $this->processFor($professional, 'Ruta');
        $submittedProcess = $this->processFor($professional, 'Formulario');

        $this->assertSame($professional->id, $submittedProcess->profesional_id);
        $this->assertTrue($professional->can('createFor', $submittedProcess));

        $this->actingAs($professional)
            ->post(route('procesos.reportes.store', $routeProcess), [
                'proceso_id' => $submittedProcess->id,
                'contenido' => 'Reporte desviado.',
                'fecha' => now()->toDateString(),
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('reportes', 0);
    }

    public function test_roles_without_report_permissions_cannot_write_reports(): void
    {
        $coordinator = $this->userWithRole('Coordinador');
        $process = $this->processFor($this->userWithRole('Profesional'));

        foreach ([$coordinator, $this->userWithRole('Secretario'), $this->userWithRole('Directivo')] as $unauthorizedUser) {
            $this->actingAs($unauthorizedUser)
                ->post(route('procesos.reportes.store', $process), [
                    'proceso_id' => $process->id,
                    'contenido' => 'Sin permiso.',
                    'fecha' => now()->toDateString(),
                ])
                ->assertForbidden();
        }

        $this->assertDatabaseCount('reportes', 0);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function processFor(User $professional, string $name = 'Proceso de prueba'): Proceso
    {
        return Proceso::factory()->create([
            'profesional_id' => $professional->id,
            'coordinador_id' => $this->userWithRole('Coordinador')->id,
            'servicio_id' => Servicio::factory()->create()->id,
            'nombre' => $name,
        ]);
    }
}
