<?php

namespace Tests\Feature;

use App\Models\Proceso;
use App\Models\Reporte;
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

        $this->actingAs($professional)
            ->delete(route('procesos.reportes.destroy', [$process, $report]))
            ->assertRedirect();

        $this->assertSoftDeleted($report);
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

        $this->actingAs($coordinator)
            ->post(route('procesos.reportes.store', $process), [
                'proceso_id' => $process->id,
                'contenido' => 'Sin permiso.',
                'fecha' => now()->toDateString(),
            ])
            ->assertForbidden();
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
