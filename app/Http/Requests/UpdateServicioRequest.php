<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255', 'unique:servicios,nombre,'.$this->route('servicio')?->id],
            'costo_servicio' => ['required', 'numeric', 'min:0'],
        ];
    }
}
