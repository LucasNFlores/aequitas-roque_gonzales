<?php

namespace App\Http\Requests;

use App\Models\Documento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $documento = $this->route('documento');

        return $documento instanceof Documento
            && ($this->user()?->can('update', $documento) ?? false);
    }

    public function rules(): array
    {
        return [
            'proceso_id' => ['sometimes', 'required', 'integer', Rule::exists('procesos', 'id')->whereNull('deleted_at')],
            'categoria_id' => ['sometimes', 'required', 'integer', Rule::exists('categorias_documento', 'id')->where('activo', true)],
            'archivo' => ['sometimes', 'file', 'mimes:pdf', 'max:20480'],
            'tipo_documento' => ['sometimes', 'required', 'string', 'max:100'],
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'categoria_id.exists' => 'La categoría seleccionada no está disponible para nuevas cargas.',
        ];
    }
}
