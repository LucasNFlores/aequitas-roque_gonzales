<?php

namespace App\Policies;

use App\Models\CategoriaDocumento;
use App\Models\User;

class CategoriaDocumentoPolicy
{
    /** CU37: solo coordinador y administrador. */
    private function gestiona(User $user): bool
    {
        return in_array($user->rol, ['coordinador', 'administrador'], true);
    }

    public function viewAny(User $user): bool { return $this->gestiona($user); }
    public function view(User $user, CategoriaDocumento $c): bool { return $this->gestiona($user); }
    public function create(User $user): bool { return $this->gestiona($user); }
    public function update(User $user, CategoriaDocumento $c): bool { return $this->gestiona($user); }
    public function activate(User $user, CategoriaDocumento $c): bool { return $this->gestiona($user); }
    public function deactivate(User $user, CategoriaDocumento $c): bool { return $this->gestiona($user); }
}
