<?php

namespace Tests\Unit;

use App\Models\EstadoProceso;
use App\Models\Proceso;
use DomainException;
use Tests\TestCase;

class ProcesoTransitionTest extends TestCase
{
    public function test_transition_rejects_a_state_that_has_not_been_persisted(): void
    {
        $proceso = new Proceso(['estado' => Proceso::ESTADO_PENDIENTE]);
        $estado = new EstadoProceso(['slug' => 'en_proceso', 'activo' => true]);

        try {
            $proceso->transitionTo($estado);
            self::fail('A process must not transition to a state that does not exist.');
        } catch (DomainException $exception) {
            self::assertSame('No se puede transicionar a un estado inactivo.', $exception->getMessage());
            self::assertSame(Proceso::ESTADO_PENDIENTE, $proceso->estado);
            self::assertFalse($proceso->exists);
        }
    }

    public function test_transition_rejects_an_inactive_state_without_changing_the_process(): void
    {
        $proceso = new Proceso(['estado' => Proceso::ESTADO_PENDIENTE]);
        $estado = new EstadoProceso(['slug' => 'en_proceso', 'activo' => false]);
        $estado->exists = true;

        try {
            $proceso->transitionTo($estado);
            self::fail('A process must not transition to an inactive state.');
        } catch (DomainException $exception) {
            self::assertSame('No se puede transicionar a un estado inactivo.', $exception->getMessage());
            self::assertSame(Proceso::ESTADO_PENDIENTE, $proceso->estado);
            self::assertFalse($proceso->exists);
        }
    }

    public function test_transition_rejects_a_soft_deleted_state_without_changing_the_process(): void
    {
        $proceso = new Proceso(['estado' => Proceso::ESTADO_PENDIENTE]);
        $estado = new EstadoProceso(['slug' => 'en_proceso', 'activo' => true]);
        $estado->exists = true;
        $estado->deleted_at = now();

        try {
            $proceso->transitionTo($estado);
            self::fail('A process must not transition to a soft-deleted state.');
        } catch (DomainException $exception) {
            self::assertSame('No se puede transicionar a un estado inactivo.', $exception->getMessage());
            self::assertSame(Proceso::ESTADO_PENDIENTE, $proceso->estado);
            self::assertFalse($proceso->exists);
        }
    }

    public function test_transition_to_the_current_active_state_is_a_no_op(): void
    {
        $proceso = new Proceso(['estado' => Proceso::ESTADO_PENDIENTE]);
        $estado = new EstadoProceso(['slug' => Proceso::ESTADO_PENDIENTE, 'activo' => true]);
        $estado->exists = true;

        $proceso->transitionTo($estado, 'No debe guardarse.');

        self::assertSame(Proceso::ESTADO_PENDIENTE, $proceso->estado);
        self::assertFalse($proceso->exists);
    }
}
