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
            'archivo' => ['sometimes', 'file', 'mimes:pdf', 'max:20480'],
            'tipo_documento' => ['sometimes', 'required', 'string', 'max:100'],
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
        ];
    }
}
