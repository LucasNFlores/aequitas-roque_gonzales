<?php

namespace App\Http\Requests;

use App\Actions\Dashboard\BuildHistoryStateOptions;
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
    public function rules(BuildHistoryStateOptions $buildHistoryStateOptions): array
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
            'states_filter_present' => ['sometimes', Rule::in(['1'])],
            'states' => ['required_if:states_filter_present,1', 'array', 'min:1'],
            'states.*' => ['required', 'string', 'distinct', Rule::in(array_column($buildHistoryStateOptions(), 'slug'))],
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
            'states.required_if' => 'Selecciona al menos un estado.',
            'states.array' => 'La selección de estados no es válida.',
            'states.min' => 'Selecciona al menos un estado.',
            'states.*.in' => 'Uno de los estados seleccionados no es válido.',
            'states.*.distinct' => 'No repitas estados en la selección.',
            'states_filter_present.in' => 'El filtro de estados no es válido.',
        ];
    }
}
