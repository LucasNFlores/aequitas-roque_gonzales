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
        if ($user->hasRole('Profesional')) {
            return $user->can('consultar_legajos')
                && $cliente->procesos()->where('profesional_id', $user->id)->exists();
        }

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
        return $user->can('eliminar_clientes')
            && ! $cliente->procesos()
                ->whereNotIn('estado', ['finalizado', 'rechazado'])
                ->exists();
    }

    public function restore(User $user, Cliente $cliente): bool
    {
        return $user->can('eliminar_clientes');
    }

    public function forceDelete(User $user, Cliente $cliente): bool
    {
        return false;
    }
}
