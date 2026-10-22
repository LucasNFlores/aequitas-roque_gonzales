<?php

namespace App\Http\Requests;

use App\Models\EstadoProceso;
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

            if (is_string($estado) && ! EstadoProceso::query()->active()->where('slug', $estado)->exists()) {
                $validator->errors()->add('estado', 'El estado seleccionado no está activo.');
            }
        }];
    }
}
