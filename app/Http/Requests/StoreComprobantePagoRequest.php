<?php

namespace App\Http\Requests;

use App\Models\ComprobantePago;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComprobantePagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ComprobantePago::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'integer', Rule::exists('clientes', 'id')->whereNull('deleted_at')],
            'archivo' => ['required', 'file', 'mimes:pdf', 'max:20480'],
            'fecha_subida' => ['required', 'date', 'before_or_equal:today'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
