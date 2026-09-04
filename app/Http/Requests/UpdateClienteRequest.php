<?php

namespace App\Http\Requests;

use App\Models\Cliente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $cliente = $this->route('cliente');

        return $cliente instanceof Cliente
            && ($this->user()?->can('update', $cliente) ?? false);
    }

    public function rules(): array
    {
        $cliente = $this->route('cliente');

        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:100'],
            'apellido' => ['sometimes', 'required', 'string', 'max:100'],
            'dni' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('clientes', 'dni')->ignore($cliente?->id),
            ],
            'telefono' => ['sometimes', 'required', 'string', 'max:50'],
            'correo' => ['sometimes', 'required', 'email', 'max:255'],
            'domicilio' => ['sometimes', 'required', 'string', 'max:255'],
            'fecha_nacimiento' => ['sometimes', 'required', 'date', 'before:today'],
        ];
    }
}
