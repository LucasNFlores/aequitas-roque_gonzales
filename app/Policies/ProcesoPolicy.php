<?php

namespace App\Policies;

use App\Models\Proceso;
use App\Models\User;

class ProcesoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('listar_filtrar_procesos');
    }

    public function view(User $user, Proceso $proceso): bool
    {
        if ($user->hasRole('Profesional')) {
            return $user->can('listar_filtrar_procesos')
                && $proceso->profesional_id === $user->id;
        }

        return $user->can('listar_filtrar_procesos') || $user->can('consultar_legajos');
    }

    public function create(User $user): bool
    {
        return $user->can('registrar_clientes');
    }

    public function update(User $user, Proceso $proceso): bool
    {
        return $this->updateState($user, $proceso);
    }

    public function delete(User $user, Proceso $proceso): bool
    {
        return $user->hasRole('Administrador');
    }

    public function restore(User $user, Proceso $proceso): bool
    {
        return $user->hasRole('Administrador');
    }

    public function forceDelete(User $user, Proceso $proceso): bool
    {
        return false;
    }

    public function admit(User $user, Proceso $proceso): bool
    {
        return $user->can('admitir_procesos');
    }

    public function reject(User $user, Proceso $proceso): bool
    {
        return $user->can('registrar_motivo_rechazo');
    }

    public function assignProfessional(User $user, Proceso $proceso): bool
    {
        return $user->can('asignar_profesionales');
    }

    public function reassignProfessional(User $user, Proceso $proceso): bool
    {
        return $user->can('reasignar_profesionales');
    }

    public function updateState(User $user, Proceso $proceso): bool
    {
        if ($user->hasRole('Profesional')) {
            return $user->can('actualizar_estados_proceso')
                && $proceso->profesional_id === $user->id;
        }

        return $user->can('actualizar_estados_proceso');
    }

    public function viewHistory(User $user, Proceso $proceso): bool
    {
        if ($user->hasRole('Profesional')) {
            return $user->can('consultar_historial_estados')
                && $proceso->profesional_id === $user->id;
        }

        return $user->can('consultar_historial_estados');
    }
}
