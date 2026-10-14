<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoriaDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('categoria')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('nombre')) {
            $this->merge(['nombre' => preg_replace('/\s+/', ' ', trim((string) $this->input('nombre')))]);
        }
    }

    public function rules(): array
    {
        $id = $this->route('categoria')?->id ?? $this->route('categoria');
        return [
            'nombre' => ['required', 'string', 'min:3', 'max:100',
                Rule::unique('categorias_documento', 'nombre')->ignore($id)],
            'descripcion' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.unique' => 'Ya existe una categoría con ese nombre. Use otro nombre o reactive la existente.',
        ];
    }
}
