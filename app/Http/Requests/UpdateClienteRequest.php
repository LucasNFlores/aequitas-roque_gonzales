<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('modificar_clientes') ?? false;
    }

    public function rules(): array
    {
        $clienteId = $this->route('cliente')?->id;

        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'apellido' => ['sometimes', 'required', 'string', 'max:255'],
            'dni' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('clientes', 'dni')->ignore($clienteId)],
            'telefono' => ['sometimes', 'required', 'string', 'max:20'],
            'correo' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('clientes', 'correo')->ignore($clienteId)],
            'domicilio' => ['sometimes', 'required', 'string', 'max:255'],
            'fecha_nacimiento' => ['sometimes', 'required', 'date', 'before:today', 'after:1900-01-01'],
        ];
    }
}
