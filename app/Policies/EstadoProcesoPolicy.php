<?php

namespace App\Policies;

use App\Models\EstadoProceso;
use App\Models\User;

class EstadoProcesoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('gestionar_estados_proceso');
    }

    public function create(User $user): bool
    {
        return $user->can('gestionar_estados_proceso');
    }

    public function update(User $user, EstadoProceso $estadoProceso): bool
    {
        return $user->can('gestionar_estados_proceso');
    }

    public function reorder(User $user, EstadoProceso $estadoProceso): bool
    {
        return $user->can('gestionar_estados_proceso');
    }

    public function activate(User $user, EstadoProceso $estadoProceso): bool
    {
        return $user->can('gestionar_estados_proceso');
    }

    public function deactivate(User $user, EstadoProceso $estadoProceso): bool
    {
        return $user->can('gestionar_estados_proceso');
    }
}
