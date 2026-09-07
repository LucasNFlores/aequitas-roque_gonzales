<?php

namespace App\Policies;

use App\Models\Notificacion;
use App\Models\User;

class NotificacionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('consultar_notificaciones');
    }

    public function view(User $user, Notificacion $notificacion): bool
    {
        return $user->can('consultar_notificaciones');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Administrador');
    }

    public function update(User $user, Notificacion $notificacion): bool
    {
        return false;
    }

    public function delete(User $user, Notificacion $notificacion): bool
    {
        return false;
    }

    public function restore(User $user, Notificacion $notificacion): bool
    {
        return false;
    }

    public function forceDelete(User $user, Notificacion $notificacion): bool
    {
        return false;
    }
}
