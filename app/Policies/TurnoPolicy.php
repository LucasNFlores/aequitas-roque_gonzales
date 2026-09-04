<?php

namespace App\Policies;

use App\Models\Turno;
use App\Models\User;

class TurnoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver_agenda_profesional');
    }

    public function view(User $user, Turno $turno): bool
    {
        if ($user->hasRole('Profesional')) {
            return $user->can('ver_agenda_profesional')
                && $turno->profesional_id === $user->id;
        }

        return $user->can('ver_agenda_profesional');
    }

    public function create(User $user): bool
    {
        return $user->canAny([
            'agendar_turnos_internos',
            'agendar_turnos_seguimiento',
            'agendar_turnos_externos',
        ]);
    }

    public function update(User $user, Turno $turno): bool
    {
        return $turno->es_externo
            ? $user->can('modificar_turnos_externos')
            : $user->can('modificar_turnos');
    }

    public function delete(User $user, Turno $turno): bool
    {
        return $user->can('eliminar_turnos');
    }

    public function restore(User $user, Turno $turno): bool
    {
        return false;
    }

    public function forceDelete(User $user, Turno $turno): bool
    {
        return false;
    }
}
