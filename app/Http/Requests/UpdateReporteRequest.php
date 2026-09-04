<?php

namespace App\Http\Requests;

use App\Models\Reporte;

class UpdateReporteRequest extends StoreReporteRequest
{
    public function authorize(): bool
    {
        $reporte = $this->route('reporte');

        return $reporte instanceof Reporte
            && ($this->user()?->can('update', $reporte) ?? false);
    }

    public function rules(): array
    {
        return [
            'proceso_id' => ['prohibited'],
            'profesional_id' => ['prohibited'],
            'contenido' => ['sometimes', 'required', 'string', 'max:20000'],
            'fecha' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
        ];
    }

    /**
     * Report updates are authorized against the report owner, not its current process assignment.
     *
     * @return array<int, callable(\Illuminate\Validation\Validator): void>
     */
    public function after(): array
    {
        return [];
    }
}
