<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DashboardHistoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if (! $this->user()->hasAnyRole(['Directivo', 'Administrador'])) {
            return [];
        }

        return [
            'period' => ['sometimes', 'required', Rule::in(['3_months', '6_months', 'custom'])],
            'from' => [
                'exclude_unless:period,custom',
                'required_if:period,custom',
                'nullable',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'to' => [
                'exclude_unless:period,custom',
                'required_if:period,custom',
                'nullable',
                'date_format:Y-m-d',
                'before_or_equal:today',
                'after_or_equal:from',
            ],
            'include_finalizado' => ['sometimes', 'boolean'],
            'include_rechazado' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'period.in' => 'Selecciona uno de los períodos disponibles.',
            'from.required_if' => 'Indica la fecha inicial del rango personalizado.',
            'from.date_format' => 'La fecha Desde no es válida.',
            'from.before_or_equal' => 'La fecha Desde no puede ser futura.',
            'to.required_if' => 'Indica la fecha final del rango personalizado.',
            'to.date_format' => 'La fecha Hasta no es válida.',
            'to.before_or_equal' => 'La fecha Hasta no puede ser futura.',
            'to.after_or_equal' => 'La fecha Hasta debe ser igual o posterior a Desde.',
            'include_finalizado.boolean' => 'El filtro para incluir finalizados no es válido.',
            'include_rechazado.boolean' => 'El filtro para incluir rechazados no es válido.',
        ];
    }
}
