<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;

class ClientePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('listar_filtrar_clientes');
    }

    public function view(User $user, Cliente $cliente): bool
    {
        return $user->can('listar_filtrar_clientes');
    }

    public function create(User $user): bool
    {
        return $user->can('registrar_clientes');
    }

    public function update(User $user, Cliente $cliente): bool
    {
        return $user->can('modificar_clientes');
    }

    public function delete(User $user, Cliente $cliente): bool
    {
        return $user->can('eliminar_clientes');
    }

    public function restore(User $user, Cliente $cliente): bool
    {
        return $user->can('eliminar_clientes');
    }

    public function forceDelete(User $user, Cliente $cliente): bool
    {
        return $user->can('eliminar_clientes');
    }
}
