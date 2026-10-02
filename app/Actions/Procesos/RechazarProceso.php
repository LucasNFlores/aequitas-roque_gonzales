<?php

namespace App\Actions\Procesos;

use App\Models\EstadoProceso;
use App\Models\Proceso;
use DomainException;
use Illuminate\Support\Facades\DB;

class RechazarProceso
{
    public function handle(Proceso $proceso, string $motivo): void
    {
        $motivo = trim($motivo);

        if ($motivo === '') {
            throw new DomainException('El motivo de rechazo es obligatorio.');
        }

        DB::transaction(function () use ($proceso, $motivo): void {
            $lockedProceso = Proceso::query()->lockForUpdate()->findOrFail($proceso->id);

            if ($lockedProceso->estado !== Proceso::ESTADO_PENDIENTE) {
                throw new DomainException('Solo se pueden rechazar procesos pendientes.');
            }

            $estado = EstadoProceso::query()
                ->active()
                ->where('slug', Proceso::ESTADO_RECHAZADO)
                ->first();

            if (! $estado instanceof EstadoProceso) {
                throw new DomainException('El estado de rechazo no está activo en el catálogo.');
            }

            $lockedProceso->forceFill(['motivo_rechazo' => $motivo]);
            $lockedProceso->transitionTo($estado, $motivo);
        });
    }
}
