<?php

namespace Tests\Feature;

use App\Models\Proceso;
use App\Models\Turno;
use App\Models\User;
use App\Services\Turnos\TurnoScheduler;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class TurnoProcessIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_a_process_for_the_same_client_and_professional(): void
    {
        $turno = Turno::factory()->create();

        $this->assertNotNull($turno->proceso);
        $this->assertSame((int) $turno->cliente_id, (int) $turno->proceso->cliente_id);
        $this->assertSame((int) $turno->profesional_id, (int) $turno->proceso->profesional_id);
    }

    /** @return array<string, array{string}> */
    public static function appointmentTypes(): array
    {
        return [
            'consulta inicial' => ['consulta_inicial'],
            'seguimiento' => ['seguimiento'],
            'externo' => ['externo'],
        ];
    }

    #[DataProvider('appointmentTypes')]
    public function test_database_rejects_creating_any_type_of_turn_without_a_process(string $type): void
    {
        $turno = Turno::factory()->create(['tipo' => $type, 'es_externo' => $type === 'externo']);
        $attributes = $turno->getAttributes();
        unset($attributes['id']);
        $attributes['proceso_id'] = null;

        $this->expectException(QueryException::class);

        DB::table('turnos')->insert($attributes);
    }

    public function test_database_rejects_removing_the_process_from_an_existing_turn(): void
    {
        $turno = Turno::factory()->create();

        $this->expectException(QueryException::class);

        DB::table('turnos')->where('id', $turno->id)->update(['proceso_id' => null]);
    }

    public function test_database_rejects_a_nonexistent_process(): void
    {
        $turno = Turno::factory()->create();

        $this->expectException(QueryException::class);

        DB::table('turnos')->where('id', $turno->id)->update(['proceso_id' => $turno->proceso_id + 1]);
    }

    public function test_database_prevents_deleting_a_process_with_turns(): void
    {
        $turno = Turno::factory()->create();

        $this->expectException(QueryException::class);

        $turno->proceso->forceDelete();
    }

    public function test_historical_turn_keeps_its_process_relation_after_the_process_is_soft_deleted(): void
    {
        $turno = Turno::factory()->create();
        $processId = $turno->proceso_id;
        $turno->proceso->delete();

        $this->assertSame((int) $processId, (int) $turno->fresh()->proceso->id);
        $this->assertTrue($turno->fresh()->proceso->trashed());
    }

    public function test_scheduler_rejects_missing_invalid_deleted_or_unrelated_processes_for_internal_and_external_turns(): void
    {
        $process = Proceso::factory()->create();
        $otherProcess = Proceso::factory()->create();
        $deletedProcess = Proceso::factory()->create(['cliente_id' => $process->cliente_id]);
        $deletedProcess->delete();
        $attributes = [
            'cliente_id' => $process->cliente_id,
            'profesional_id' => $process->profesional_id,
            'fecha_hora' => now()->addDays(2)->setTime(9, 0)->toDateTimeString(),
            'tipo' => 'consulta_inicial',
            'detalle_externo' => 'Audiencia',
        ];

        foreach (['schedule', 'scheduleExternal'] as $method) {
            foreach ([[], ['proceso_id' => null], ['proceso_id' => 0], ['proceso_id' => $otherProcess->id], ['proceso_id' => $deletedProcess->id]] as $processAttributes) {
                try {
                    app(TurnoScheduler::class)->{$method}([...$attributes, ...$processAttributes]);
                    $this->fail('El turno debe rechazar un proceso ausente o inválido.');
                } catch (ValidationException $exception) {
                    $this->assertArrayHasKey('procesoId', $exception->errors());
                }
            }
        }

        $this->assertDatabaseCount('turnos', 0);
    }

    public function test_migration_repairs_legacy_demo_turns_and_preserves_all_other_attributes(): void
    {
        Storage::fake('local');
        $this->seed(DatabaseSeeder::class);
        $externalTurns = Turno::query()->where('detalle_externo', 'like', '[DEMO] Compromiso externo %')->get();
        $this->assertCount(4, $externalTurns);
        $count = Turno::query()->count();
        $migration = $this->migration();
        $migration->down();
        DB::table('turnos')->whereIn('id', $externalTurns->modelKeys())->update(['proceso_id' => null]);

        $migration->up();

        foreach ($externalTurns as $turno) {
            $this->assertSame($turno->getAttributes(), $turno->fresh()->getAttributes());
        }
        $this->assertSame($count, Turno::query()->count());
        $this->assertFalse(Turno::query()->whereNull('proceso_id')->exists());
    }

    public function test_migration_refuses_unknown_orphans_without_partially_repairing_demo_turns(): void
    {
        Storage::fake('local');
        $this->seed(DatabaseSeeder::class);
        $demoTurn = Turno::query()->where('detalle_externo', '[DEMO] Compromiso externo 1')->firstOrFail();
        $unrelatedTurn = Turno::factory()->create();
        $migration = $this->migration();
        $migration->down();
        DB::table('turnos')->whereIn('id', [$demoTurn->id, $unrelatedTurn->id])->update(['proceso_id' => null]);

        try {
            $migration->up();
            $this->fail('La migración debe detenerse ante turnos sin una asociación identificable.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString((string) $unrelatedTurn->id, $exception->getMessage());
        }

        $this->assertNull($demoTurn->fresh()->proceso_id);
        $this->assertNull($unrelatedTurn->fresh()->proceso_id);
    }

    public function test_migration_does_not_choose_between_multiple_demo_processes(): void
    {
        Storage::fake('local');
        $this->seed(DatabaseSeeder::class);
        $turno = Turno::query()->where('detalle_externo', '[DEMO] Compromiso externo 1')->firstOrFail();
        Proceso::factory()->create([
            'cliente_id' => $turno->cliente_id,
            'profesional_id' => $turno->proceso->profesional_id,
            'nombre' => $turno->proceso->nombre.' adicional',
        ]);
        $migration = $this->migration();
        $migration->down();
        DB::table('turnos')->where('id', $turno->id)->update(['proceso_id' => null]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Asocie los turnos antes de migrar: '.$turno->id);

        $migration->up();
    }

    public function test_migration_does_not_associate_a_demo_looking_turn_for_another_professional(): void
    {
        Storage::fake('local');
        $this->seed(DatabaseSeeder::class);
        $turno = Turno::query()->where('detalle_externo', '[DEMO] Compromiso externo 1')->firstOrFail();
        $otherProfessional = User::factory()->create();
        $migration = $this->migration();
        $migration->down();
        DB::table('turnos')->where('id', $turno->id)->update([
            'proceso_id' => null,
            'profesional_id' => $otherProfessional->id,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Asocie los turnos antes de migrar: '.$turno->id);

        $migration->up();
    }

    private function migration(): Migration
    {
        return require database_path('migrations/2026_10_22_135346_require_proceso_id_on_turnos_table.php');
    }
}
