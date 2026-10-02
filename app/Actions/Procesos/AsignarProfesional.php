<?php

namespace App\Actions\Procesos;

use App\Models\Proceso;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class AsignarProfesional
{
    public function handle(Proceso $proceso, int $profesionalId): void
    {
        DB::transaction(function () use ($proceso, $profesionalId): void {
            $lockedProceso = Proceso::query()->lockForUpdate()->findOrFail($proceso->id);
            $profesional = User::query()->find($profesionalId);

            if (! $profesional instanceof User || $profesional->trashed() || ! $profesional->hasRole('Profesional')) {
                throw new DomainException('Selecciona un usuario activo con rol Profesional.');
            }

            if ($lockedProceso->profesional_id === $profesional->id) {
                throw new DomainException('El profesional seleccionado ya está asignado.');
            }

            $servicio = $lockedProceso->servicio;

            if ($servicio === null) {
                throw new DomainException('El servicio del proceso no está disponible.');
            }

            if (! $profesional->servicios()->whereKey($servicio->id)->exists()) {
                throw new DomainException('El profesional no está habilitado para el servicio del proceso.');
            }

            $lockedProceso->forceFill([
                'profesional_id' => $profesional->id,
                'honorarios' => $servicio->costo_servicio,
            ])->save();
        });
    }
}
