<?php

namespace App\Policies;

use App\Models\ComprobantePago;
use App\Models\User;

class ComprobantePagoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver_comprobantes_pago');
    }

    public function view(User $user, ComprobantePago $comprobantePago): bool
    {
        return $user->can('ver_comprobantes_pago');
    }

    public function create(User $user): bool
    {
        return $user->can('registrar_comprobantes_pago');
    }

    public function update(User $user, ComprobantePago $comprobantePago): bool
    {
        return $user->can('registrar_comprobantes_pago');
    }

    public function delete(User $user, ComprobantePago $comprobantePago): bool
    {
        return false;
    }

    public function restore(User $user, ComprobantePago $comprobantePago): bool
    {
        return false;
    }

    public function forceDelete(User $user, ComprobantePago $comprobantePago): bool
    {
        return false;
    }
}
