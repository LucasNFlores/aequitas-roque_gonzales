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
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProcesoStateUpdateValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, EstadoProcesoSeeder::class]);
    }

    /** @return array<string, array{array<string, mixed>, ?string, string, string}> */
    public static function invalidStateSubmissions(): array
    {
        return [
            'missing state' => [[], null, 'estado', 'Selecciona un estado.'],
            'unknown state' => [['estado' => 'estado_inexistente'], null, 'estado', 'El estado seleccionado ya no está disponible.'],
            'inactive state' => [['estado' => 'en_proceso'], 'en_proceso', 'estado', 'El estado seleccionado ya no está disponible.'],
            'current state' => [['estado' => 'pendiente'], null, 'estado', 'Selecciona un estado diferente al actual.'],
            'admitted state' => [['estado' => Proceso::ESTADO_ADMITIDO], null, 'estado', 'Utiliza las acciones de admisión o rechazo para ese estado.'],
            'rejected state' => [['estado' => Proceso::ESTADO_RECHAZADO], null, 'estado', 'Utiliza las acciones de admisión o rechazo para ese estado.'],
            'oversized reason' => [
                ['estado' => 'en_proceso', 'motivo' => str_repeat('m', 5001)],
                null,
                'motivo',
                'El motivo no puede superar los 5000 caracteres.',
            ],
        ];
    }

    #[DataProvider('invalidStateSubmissions')]
    public function test_invalid_state_patch_shows_a_clear_error_and_preserves_the_process(
        array $payload,
        ?string $stateToDeactivate,
        string $errorField,
        string $expectedMessage,
    ): void {
        [$professional, $process] = $this->createAssignedProcess();

        if ($stateToDeactivate !== null) {
            EstadoProceso::query()->where('slug', $stateToDeactivate)->update(['activo' => false]);
        }

        $initialState = $process->estado;
        $initialHistoryCount = $process->historialEstados()->count();
        $detailUrl = route('legajos.procesos.show', [$process->cliente_id, $process]);

        $patchResponse = $this->actingAs($professional)
            ->from($detailUrl)
            ->patch(route('procesos.estado.update', $process), $payload);

        $patchResponse->assertRedirect($detailUrl)
            ->assertSessionHasErrors($errorField);
        $this->get($detailUrl)
            ->assertOk()
            ->assertSeeText($expectedMessage);
        $this->assertSame($initialState, $process->fresh()->estado);
        $this->assertSame($initialHistoryCount, $process->historialEstados()->count());
    }

    public function test_state_patch_accepts_a_reason_at_the_5000_character_limit(): void
    {
        [$professional, $process] = $this->createAssignedProcess();
        $reason = str_repeat('m', 5000);

        $response = $this->actingAs($professional)
            ->patch(route('procesos.estado.update', $process), [
                'estado' => 'en_proceso',
                'motivo' => $reason,
            ]);

        $response->assertRedirect(route('legajos.procesos.show', [$process->cliente_id, $process]));
        $this->assertSame('en_proceso', $process->fresh()->estado);
        $this->assertSame($reason, $process->historialEstados()->latest('id')->firstOrFail()->motivo);
    }

    public function test_state_patch_accepts_an_omitted_optional_reason(): void
    {
        [$professional, $process] = $this->createAssignedProcess();
        $initialHistoryCount = $process->historialEstados()->count();

        $response = $this->actingAs($professional)
            ->patch(route('procesos.estado.update', $process), ['estado' => 'en_proceso']);

        $response->assertRedirect(route('legajos.procesos.show', [$process->cliente_id, $process]));
        $this->assertSame('en_proceso', $process->fresh()->estado);
        $this->assertSame($initialHistoryCount + 1, $process->historialEstados()->count());
        $this->assertNull($process->historialEstados()->latest('id')->firstOrFail()->motivo);
    }

    /** @return array{User, Proceso} */
    private function createAssignedProcess(): array
    {
        $professional = $this->userWithRole('Profesional');
        $coordinator = $this->userWithRole('Coordinador');
        $client = Cliente::factory()->create();
        $service = Servicio::factory()->create();
        $process = Proceso::factory()->create([
            'cliente_id' => $client->id,
            'profesional_id' => $professional->id,
            'coordinador_id' => $coordinator->id,
            'servicio_id' => $service->id,
        ]);

        return [$professional, $process];
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
