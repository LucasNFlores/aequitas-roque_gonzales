<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('listar_usuarios');
    }

    public function view(User $user, User $target): bool
    {
        return $user->can('listar_usuarios');
    }

    public function create(User $user): bool
    {
        return $user->can('agregar_usuarios');
    }

    public function update(User $user, User $target): bool
    {
        return $user->can('modificar_usuarios')
            && ($user->hasRole('Administrador') || ! $target->hasRole('Administrador'));
    }

    public function delete(User $user, User $target): bool
    {
        return $user->can('eliminar_usuarios')
            && ($user->hasRole('Administrador') || ! $target->hasRole('Administrador'))
            && ! $target->procesosComoProfesional()
                ->whereNotIn('estado', ['finalizado', 'rechazado'])
                ->exists()
            && ! $target->procesosComoCoordinador()
                ->whereNotIn('estado', ['finalizado', 'rechazado'])
                ->exists();
    }

    public function restore(User $user, User $target): bool
    {
        return $user->can('eliminar_usuarios');
    }

    public function forceDelete(User $user, User $target): bool
    {
        return false;
    }

    public function manageRoles(User $user, User $target): bool
    {
        return $user->can('editar_roles')
            && ($user->hasRole('Administrador') || ! $target->hasRole('Administrador'));
    }
}
