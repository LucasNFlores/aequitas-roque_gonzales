<?php

namespace App\Services\Turnos;

use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TurnoScheduler
{
    /** @param array{cliente_id: int, profesional_id: int, proceso_id: int|null, fecha_hora: string, tipo: string} $attributes */
    public function schedule(array $attributes): Turno
    {
        return DB::transaction(function () use ($attributes): Turno {
            $professionalId = (int) $attributes['profesional_id'];
            $this->lockProfessionals([$professionalId]);

            $dateTime = Carbon::parse($attributes['fecha_hora'])->second(0);
            $this->assertAvailability($professionalId, $dateTime, null, false);

            return Turno::query()->create([
                ...$attributes,
                'fecha_hora' => $dateTime,
                'es_externo' => false,
                'detalle_externo' => null,
                'estado' => 'programado',
            ]);
        });
    }

    /** @param array{cliente_id: int, profesional_id: int, proceso_id: int|null, fecha_hora: string, detalle_externo: string} $attributes */
    public function scheduleExternal(array $attributes): Turno
    {
        return DB::transaction(function () use ($attributes): Turno {
            $professionalId = (int) $attributes['profesional_id'];
            $this->lockProfessionals([$professionalId]);

            $dateTime = Carbon::parse($attributes['fecha_hora'])->second(0);
            $this->assertAvailability($professionalId, $dateTime, null, true);

            return Turno::query()->create([
                ...$attributes,
                'fecha_hora' => $dateTime,
                'tipo' => 'externo',
                'es_externo' => true,
                'estado' => 'programado',
            ]);
        });
    }

    public function reschedule(Turno $turno, string $fechaHora): Turno
    {
        return DB::transaction(function () use ($turno, $fechaHora): Turno {
            $this->lockProfessionals([(int) $turno->profesional_id]);

            $lockedTurno = Turno::query()->lockForUpdate()->findOrFail($turno->id);

            if ($lockedTurno->isCancelado()) {
                throw ValidationException::withMessages([
                    'fechaHora' => 'No se puede reprogramar un turno cancelado.',
                ]);
            }

            $dateTime = Carbon::parse($fechaHora)->second(0);
            $this->assertAvailability(
                (int) $lockedTurno->profesional_id,
                $dateTime,
                $lockedTurno->id,
                $lockedTurno->es_externo,
            );

            $lockedTurno->update(['fecha_hora' => $dateTime]);

            return $lockedTurno->refresh();
        });
    }

    public function rescheduleExternal(Turno $turno, string $fechaHora, string $detalleExterno): Turno
    {
        return DB::transaction(function () use ($turno, $fechaHora, $detalleExterno): Turno {
            $this->lockProfessionals([(int) $turno->profesional_id]);

            $lockedTurno = Turno::query()
                ->where('tipo', 'externo')
                ->where('es_externo', true)
                ->lockForUpdate()
                ->findOrFail($turno->id);

            if ($lockedTurno->isCancelado()) {
                throw ValidationException::withMessages([
                    'fechaHora' => 'No se puede reprogramar un compromiso cancelado.',
                ]);
            }

            $dateTime = Carbon::parse($fechaHora)->second(0);
            $this->assertAvailability(
                (int) $lockedTurno->profesional_id,
                $dateTime,
                $lockedTurno->id,
                true,
            );

            $lockedTurno->update([
                'fecha_hora' => $dateTime,
                'detalle_externo' => $detalleExterno,
            ]);

            return $lockedTurno->refresh();
        });
    }

    public function cancel(Turno $turno): Turno
    {
        return DB::transaction(function () use ($turno): Turno {
            $this->lockProfessionals([(int) $turno->profesional_id]);

            $lockedTurno = Turno::query()->lockForUpdate()->findOrFail($turno->id);
            $lockedTurno->cancelar();

            return $lockedTurno->refresh();
        });
    }

    private function assertAvailability(
        int $professionalId,
        Carbon $dateTime,
        ?int $excludedTurnoId,
        bool $isExternal,
    ): void {
        if (Turno::existeConflicto($professionalId, $dateTime, $excludedTurnoId, $isExternal)) {
            throw ValidationException::withMessages([
                'fechaHora' => 'El profesional ya tiene un turno a esa hora o un compromiso externo ese día.',
            ]);
        }
    }

    /** @param list<int> $professionalIds */
    private function lockProfessionals(array $professionalIds): void
    {
        User::query()
            ->whereKey($professionalIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get(['id']);
    }
}
