<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $target = $this->route('user') ?? $this->route('usuario');

        if ($target) {
            return $this->user()?->can('update', $target) ?? false;
        }

        return $this->user()?->can('modificar_usuarios') ?? false;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('usuario')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:4', 'max:255'],
            'dni' => ['nullable', 'string', 'max:20', Rule::unique('users', 'dni')->ignore($userId)],
            'telefono' => ['nullable', 'string', 'max:30'],
            'domicilio' => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'fecha_ingreso' => ['nullable', 'date'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'servicios' => ['nullable', 'array'],
            'servicios.*' => ['integer', 'exists:servicios,id'],
        ];
    }
}
