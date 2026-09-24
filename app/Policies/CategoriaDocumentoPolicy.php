<?php

namespace App\Policies;

use App\Models\CategoriaDocumento;
use App\Models\User;

class CategoriaDocumentoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('gestionar_categorias_documentos');
    }

    public function create(User $user): bool
    {
        return $user->can('gestionar_categorias_documentos');
    }

    public function update(User $user, CategoriaDocumento $categoria): bool
    {
        return $user->can('gestionar_categorias_documentos');
    }

    public function activate(User $user, CategoriaDocumento $categoria): bool
    {
        return $user->can('gestionar_categorias_documentos');
    }

    public function deactivate(User $user, CategoriaDocumento $categoria): bool
    {
        return $user->can('gestionar_categorias_documentos');
    }
}
