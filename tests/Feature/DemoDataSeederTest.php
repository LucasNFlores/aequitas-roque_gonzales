<?php

namespace Tests\Feature;

use App\Models\CategoriaDocumento;
use App\Models\Cliente;
use App\Models\ComprobantePago;
use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Models\HistorialEstadoProceso;
use App\Models\Proceso;
use App\Models\Reporte;
use App\Models\ReporteVersion;
use App\Models\Servicio;
use App\Models\Turno;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_populates_a_complete_repeatable_demo_without_touching_unrelated_rows(): void
    {
        Storage::fake('local');
        $deletedDemoProfessional = User::factory()->create(['email' => 'profesional.civil@example.test']);
        $deletedDemoProfessional->delete();
        $deletedDemoClient = Cliente::factory()->create(['dni' => '99010001']);
        $deletedDemoClient->delete();
        $deletedDemoService = Servicio::factory()->create(['nombre' => 'Consultoría']);
        $deletedDemoService->delete();
        $deletedDemoCategory = CategoriaDocumento::factory()->create(['nombre' => 'Judicial']);
        $deletedDemoCategory->delete();
        $unrelatedDeletedClient = Cliente::factory()->create(['dni' => '45678902']);
        $unrelatedDeletedClient->delete();
        $unrelatedDeletedService = Servicio::factory()->create(['nombre' => 'Servicio eliminado ajeno']);
        $unrelatedDeletedService->delete();
        $unrelatedClient = Cliente::factory()->create(['dni' => '45678901']);
        $unrelatedService = Servicio::factory()->create(['nombre' => 'Servicio ajeno']);
        $unrelatedUser = User::factory()->create(['email' => 'persona.ajena@example.test']);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('roles', 5);
        $this->assertEqualsCanonicalizing(
            ['Secretario', 'Profesional', 'Coordinador', 'Directivo', 'Administrador'],
            Role::query()->pluck('name')->all(),
        );
        $this->assertDatabaseMissing('roles', ['name' => 'Supervisor']);

        foreach ([
            'admin@example.com' => 'Administrador',
            'carlos@example.com' => 'Secretario',
            'juanperez@example.com' => 'Profesional',
            'coordinador@example.com' => 'Coordinador',
            'directivo@example.com' => 'Directivo',
        ] as $email => $role) {
            $this->assertTrue(User::query()->where('email', $email)->firstOrFail()->hasRole($role));
        }

        $this->assertCount(21, $this->demoClients());
        $this->assertCount(21, $this->demoProcesses());
        $this->assertEqualsCanonicalizing(
            ['pendiente', 'admitido', 'iniciado', 'en_proceso', 'en_espera', 'finalizado', 'rechazado'],
            $this->demoProcesses()->pluck('estado')->unique()->all(),
        );
        $this->assertGreaterThanOrEqual(30, $this->demoHistories()->count());
        $oldestHistoryDate = HistorialEstadoProceso::query()
            ->where('motivo', 'like', '[DEMO] P% transición%')
            ->min('fecha_cambio');
        $this->assertSame(
            CarbonImmutable::now()->subMonths(6)->toDateString(),
            CarbonImmutable::parse($oldestHistoryDate)->toDateString(),
        );
        $this->assertGreaterThan(0, HistorialEstadoProceso::query()->where('motivo', 'like', '[DEMO] P% transición%')->whereDate('fecha_cambio', '>=', CarbonImmutable::now()->subDays(30)->toDateString())->count());
        $this->assertCount(4, User::query()->whereIn('email', [
            'juanperez@example.com',
            'profesional.civil@example.test',
            'profesional.familia@example.test',
            'profesional.comercial@example.test',
        ])->get());
        $this->assertGreaterThanOrEqual(9, DB::table('user_servicios')->count());
        $this->assertCount(21, $this->demoDocuments());
        $this->assertCount(21, $this->demoReceipts());
        $this->assertGreaterThan(0, Reporte::query()->where('contenido', 'like', '[DEMO] Informe%')->whereDate('fecha', '>=', CarbonImmutable::now()->subDays(30)->toDateString())->count());
        $this->assertGreaterThan(0, Reporte::query()->where('contenido', 'like', '[DEMO] Informe%')->whereDate('fecha', '<=', CarbonImmutable::now()->subMonths(3)->toDateString())->count());
        $this->assertGreaterThan(0, DocumentoVersion::query()->whereHas('documento', fn ($query) => $query->where('nombre', 'like', '[DEMO] P%'))->count());
        $this->assertSame(7, DocumentoVersion::query()->whereHas('documento', fn ($query) => $query->where('nombre', 'like', '[DEMO] P%'))->count());
        $this->assertGreaterThan(0, ReporteVersion::query()->whereHas('reporte', fn ($query) => $query->where('contenido', 'like', '[DEMO] Informe%'))->count());
        $this->assertSame(4, ReporteVersion::query()->whereHas('reporte', fn ($query) => $query->where('contenido', 'like', '[DEMO] Informe%'))->count());
        $this->assertGreaterThan(0, Turno::query()->where('es_externo', true)->where('detalle_externo', 'like', '[DEMO] Compromiso%')->count());
        $this->assertGreaterThan(0, Turno::query()->activos()->whereIn('proceso_id', $this->demoProcesses()->modelKeys())->where('fecha_hora', '>', CarbonImmutable::now())->count());
        $this->assertSame(8, Turno::query()->where('estado', 'cancelado')->whereHas('proceso', fn ($query) => $query->where('nombre', 'like', '[DEMO] Legajo %'))->count());
        $this->assertScheduledTurnsHaveNoConflicts();

        foreach ($this->demoDocuments() as $document) {
            Storage::disk('local')->assertExists($document->archivo_path);
        }

        foreach ($this->demoReceipts() as $receipt) {
            Storage::disk('local')->assertExists($receipt->archivo_path);
        }

        foreach (DocumentoVersion::query()->whereHas('documento', fn ($query) => $query->where('nombre', 'like', '[DEMO] P%'))->get() as $version) {
            Storage::disk('local')->assertExists($version->archivo_path);
        }
        $countsBeforeRepeat = $this->demoCounts();
        $this->seed(DatabaseSeeder::class);
        $this->assertSame($countsBeforeRepeat, $this->demoCounts());
        $this->assertModelExists($unrelatedClient);
        $this->assertModelExists($unrelatedService);
        $this->assertModelExists($unrelatedUser);
        $this->assertNotSoftDeleted($deletedDemoProfessional);
        $this->assertNotSoftDeleted($deletedDemoClient);
        $this->assertNotSoftDeleted($deletedDemoService);
        $this->assertNotSoftDeleted($deletedDemoCategory);
        $this->assertSoftDeleted($unrelatedDeletedClient);
        $this->assertSoftDeleted($unrelatedDeletedService);
        $this->assertSame(1, Servicio::query()->where('nombre', 'Consultoría')->count());
        $this->assertSame(1, Cliente::query()->where('dni', '99010001')->count());
    }

    /** @return Collection<int, Cliente> */
    private function demoClients(): Collection
    {
        return Cliente::query()->whereBetween('dni', ['99010001', '99010021'])->get();
    }

    /** @return Collection<int, Proceso> */
    private function demoProcesses(): Collection
    {
        return Proceso::query()->where('nombre', 'like', '[DEMO] Legajo %')->get();
    }

    /** @return Collection<int, HistorialEstadoProceso> */
    private function demoHistories(): Collection
    {
        return HistorialEstadoProceso::query()->where('motivo', 'like', '[DEMO] P% transición%')->get();
    }

    /** @return Collection<int, Documento> */
    private function demoDocuments(): Collection
    {
        return Documento::query()->where('nombre', 'like', '[DEMO] P%')->get();
    }

    /** @return Collection<int, ComprobantePago> */
    private function demoReceipts(): Collection
    {
        return ComprobantePago::query()->where('archivo_path', 'like', 'demo/comprobantes/%')->get();
    }

    /** @return Collection<int, Reporte> */
    private function demoReports(): Collection
    {
        return Reporte::query()->where('contenido', 'like', '[DEMO] Informe%')->get();
    }

    /** @return array<string, int> */
    private function demoCounts(): array
    {
        return [
            'professionals' => User::query()->whereIn('email', [
                'profesional.civil@example.test',
                'profesional.familia@example.test',
                'profesional.comercial@example.test',
            ])->count(),
            'clients' => $this->demoClients()->count(),
            'processes' => $this->demoProcesses()->count(),
            'histories' => $this->demoHistories()->count(),
            'documents' => $this->demoDocuments()->count(),
            'reports' => $this->demoReports()->count(),
            'receipts' => $this->demoReceipts()->count(),
            'documentVersions' => DocumentoVersion::query()->whereHas('documento', fn ($query) => $query->where('nombre', 'like', '[DEMO] P%'))->count(),
            'reportVersions' => ReporteVersion::query()->whereHas('reporte', fn ($query) => $query->where('contenido', 'like', '[DEMO] Informe%'))->count(),
            'appointments' => Turno::query()->whereHas('proceso', fn ($query) => $query->where('nombre', 'like', '[DEMO] Legajo %'))->count(),
            'external' => Turno::query()->where('detalle_externo', 'like', '[DEMO] Compromiso%')->count(),
        ];
    }

    private function assertScheduledTurnsHaveNoConflicts(): void
    {
        $demoProcessIds = $this->demoProcesses()->modelKeys();
        $appointments = Turno::query()
            ->activos()
            ->whereIn('proceso_id', $demoProcessIds)
            ->get();

        foreach ($appointments->groupBy(fn (Turno $turno): string => $turno->profesional_id.'-'.$turno->fecha_hora->toDateString()) as $dayAppointments) {
            $hours = $dayAppointments->pluck('fecha_hora')->map(fn ($date): string => $date->format('H:i'))->all();
            $this->assertCount(count(array_unique($hours)), $hours);
        }

        $externalAppointments = Turno::query()
            ->activos()
            ->where('es_externo', true)
            ->where('detalle_externo', 'like', '[DEMO] Compromiso%')
            ->get();

        foreach ($externalAppointments as $externalAppointment) {
            $this->assertSame(1, Turno::query()
                ->activos()
                ->where('profesional_id', $externalAppointment->profesional_id)
                ->whereDate('fecha_hora', $externalAppointment->fecha_hora->toDateString())
                ->count());
        }
    }
}
