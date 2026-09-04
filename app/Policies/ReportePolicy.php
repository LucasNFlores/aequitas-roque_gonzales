<?php

namespace App\Policies;

use App\Models\Proceso;
use App\Models\Reporte;
use App\Models\User;

class ReportePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('consultar_reportes');
    }

    public function view(User $user, Reporte $reporte): bool
    {
        if ($user->hasRole('Profesional')) {
            return $user->can('consultar_reportes')
                && $reporte->profesional_id === $user->id
                && $reporte->proceso?->profesional_id === $user->id;
        }

        return $user->can('consultar_reportes');
    }

    public function create(User $user): bool
    {
        return $user->can('registrar_reportes');
    }

    public function createFor(User $user, Proceso $proceso): bool
    {
        if ($user->hasRole('Profesional')) {
            return $user->can('registrar_reportes')
                && $proceso->profesional_id === $user->id;
        }

        return $user->can('registrar_reportes');
    }

    public function update(User $user, Reporte $reporte): bool
    {
        return $user->hasRole('Administrador')
            || ($user->can('editar_reportes_propios')
                && $reporte->profesional_id === $user->id);
    }

    public function delete(User $user, Reporte $reporte): bool
    {
        return $user->hasRole('Administrador')
            || ($user->can('eliminar_reportes_propios')
                && $reporte->profesional_id === $user->id);
    }

    public function restore(User $user, Reporte $reporte): bool
    {
        return $this->update($user, $reporte);
    }

    public function forceDelete(User $user, Reporte $reporte): bool
    {
        return false;
    }
}
