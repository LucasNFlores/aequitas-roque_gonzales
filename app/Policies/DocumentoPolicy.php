<?php

namespace App\Policies;

use App\Models\Documento;
use App\Models\User;

class DocumentoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('visualizar_documentacion');
    }

    public function view(User $user, Documento $documento): bool
    {
        if ($user->hasRole('Profesional')) {
            return $user->can('visualizar_documentacion')
                && $documento->proceso?->profesional_id === $user->id;
        }

        return $user->can('visualizar_documentacion');
    }

    public function create(User $user): bool
    {
        return $user->can('cargar_documentacion');
    }

    public function update(User $user, Documento $documento): bool
    {
        return $user->can('reemplazar_documentacion');
    }

    public function delete(User $user, Documento $documento): bool
    {
        return $user->can('eliminar_documentacion');
    }

    public function restore(User $user, Documento $documento): bool
    {
        return $user->can('reemplazar_documentacion');
    }

    public function forceDelete(User $user, Documento $documento): bool
    {
        return false;
    }

    public function download(User $user, Documento $documento): bool
    {
        if ($user->hasRole('Profesional')) {
            return $user->can('descargar_documentacion')
                && $documento->proceso?->profesional_id === $user->id;
        }

        return $user->can('descargar_documentacion');
    }
}
