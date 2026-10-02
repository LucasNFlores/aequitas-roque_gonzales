<?php

namespace App\Actions\Procesos;

use App\Models\EstadoProceso;
use App\Models\Proceso;
use DomainException;
use Illuminate\Support\Facades\DB;

class AdmitirProceso
{
    public function handle(Proceso $proceso): void
    {
        DB::transaction(function () use ($proceso): void {
            $lockedProceso = Proceso::query()->lockForUpdate()->findOrFail($proceso->id);

            if ($lockedProceso->estado !== Proceso::ESTADO_PENDIENTE) {
                throw new DomainException('Solo se pueden admitir procesos pendientes.');
            }

            $estado = EstadoProceso::query()
                ->active()
                ->where('slug', Proceso::ESTADO_ADMITIDO)
                ->first();

            if (! $estado instanceof EstadoProceso) {
                throw new DomainException('El estado de admisión no está activo en el catálogo.');
            }

            $lockedProceso->forceFill(['motivo_rechazo' => null]);
            $lockedProceso->transitionTo($estado, 'Admisión aprobada.');
        });
    }
}
