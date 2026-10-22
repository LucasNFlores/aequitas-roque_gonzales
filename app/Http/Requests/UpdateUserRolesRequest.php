<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user();
        $target = $this->route('user');

        if (! $actor instanceof User || ! $target instanceof User) {
            return false;
        }

        if (! $actor->can('manageRoles', $target)) {
            return false;
        }

        $role = $this->input('role');

        return $actor->hasRole('Administrador')
            || $role !== 'Administrador';
    }

    public function rules(): array
    {
        return [
            'role' => ['nullable', 'string', 'exists:roles,name'],
            'roles' => ['prohibited'],
        ];
    }
}
