<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('agregar_usuarios') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:4', 'max:255'],
            'dni' => ['nullable', 'string', 'max:20', 'unique:users,dni'],
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

    protected function prepareForValidation(): void
    {
        if ($this->has('roles') && ! is_array($this->input('roles'))) {
            $this->merge(['roles' => []]);
        }
    }
}
