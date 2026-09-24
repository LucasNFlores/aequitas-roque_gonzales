<?php

namespace App\Http\Requests;

use App\Models\Documento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Documento::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'proceso_id' => ['required', 'integer', Rule::exists('procesos', 'id')->whereNull('deleted_at')],
            'categoria_id' => ['required', 'integer', Rule::exists('categorias_documento', 'id')->where('activo', true)],
            'archivo' => ['required', 'file', 'mimes:pdf', 'max:20480'],
            'tipo_documento' => ['required', 'string', 'max:100'],
            'nombre' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'categoria_id.required' => 'La categoría es obligatoria.',
            'categoria_id.exists' => 'La categoría seleccionada no está disponible para nuevas cargas.',
        ];
    }
}
