<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'dni' => ['required', 'string', 'max:20', 'unique:clientes,dni,'.$clienteId],
            'telefono' => ['required', 'string', 'max:20'],
            'correo' => ['required', 'email', 'max:255', 'unique:clientes,correo,'.$clienteId],
            'domicilio' => ['required', 'string', 'max:255'],
            'fecha_nacimiento' => ['required', 'date', 'before:today', 'after:1900-01-01'],
        ];
    }
}
