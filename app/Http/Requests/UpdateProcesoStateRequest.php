<?php

namespace App\Http\Requests;

use App\Models\Proceso;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProcesoStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $proceso = $this->route('proceso');

        return $proceso instanceof Proceso
            && ($this->user()?->can('updateState', $proceso) ?? false);
    }

    public function rules(): array
    {
        return [
            'estado' => [
                'required',
                'string',
                Rule::exists('estados_proceso', 'slug')
                    ->where('activo', true)
                    ->whereNull('deleted_at'),
            ],
            'motivo' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'estado.required' => 'Selecciona un estado.',
            'estado.string' => 'El estado seleccionado no es válido.',
            'estado.exists' => 'El estado seleccionado ya no está disponible.',
            'motivo.string' => 'El motivo debe ser un texto.',
            'motivo.max' => 'El motivo no puede superar los 5000 caracteres.',
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $proceso = $this->route('proceso');
            $estado = $this->input('estado');

            if ($estado === $proceso?->estado) {
                $validator->errors()->add('estado', 'Selecciona un estado diferente al actual.');
            }

            if (in_array($estado, [Proceso::ESTADO_ADMITIDO, Proceso::ESTADO_RECHAZADO], true)) {
                $validator->errors()->add('estado', 'Utiliza las acciones de admisión o rechazo para ese estado.');
            }

        }];
    }
}
