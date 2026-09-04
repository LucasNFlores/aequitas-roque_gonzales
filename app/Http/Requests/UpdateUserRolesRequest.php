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

        $roles = $this->input('roles', []);

        return $actor->hasRole('Administrador')
            || ! in_array('Administrador', is_array($roles) ? $roles : [], true);
    }

    public function rules(): array
    {
        return [
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ];
    }
}
