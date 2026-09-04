<?php

namespace App\Http\Requests;

use App\Models\ComprobantePago;
use Illuminate\Foundation\Http\FormRequest;

class UpdateComprobantePagoRequest extends StoreComprobantePagoRequest
{
    public function authorize(): bool
    {
        $comprobantePago = $this->route('comprobante_pago');

        return $comprobantePago instanceof ComprobantePago
            && ($this->user()?->can('update', $comprobantePago) ?? false);
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['prohibited'],
            'archivo' => ['sometimes', 'file', 'mimes:pdf', 'max:20480'],
            'fecha_subida' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
            'descripcion' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }
}
