<?php

namespace App\Http\Requests;

use App\Models\Turno;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateTurnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $turno = $this->route('turno');

        return $turno instanceof Turno
            && ($this->user()?->can('update', $turno) ?? false);
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['sometimes', 'required', 'integer', Rule::exists('clientes', 'id')->whereNull('deleted_at')],
            'profesional_id' => ['sometimes', 'required', 'integer', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'proceso_id' => ['sometimes', 'nullable', 'integer', Rule::exists('procesos', 'id')->whereNull('deleted_at')],
            'fecha_hora' => ['sometimes', 'required', 'date'],
            'es_externo' => ['sometimes', 'boolean'],
            'detalle_externo' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'tipo' => ['sometimes', 'required', Rule::in(Turno::TIPOS)],
        ];
    }

    /**
     * Keep the external flag, type and detail consistent on updates.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $turno = $this->route('turno');
            $isExternal = $this->has('es_externo')
                ? $this->boolean('es_externo')
                : (bool) $turno?->es_externo;
            $type = $this->input('tipo', $turno?->tipo);
            $detail = $this->input('detalle_externo', $turno?->detalle_externo);

            if ($isExternal !== ($type === 'externo')) {
                $validator->errors()->add('tipo', 'El tipo de turno no coincide con el indicador de turno externo.');
            }

            if ($isExternal && blank($detail)) {
                $validator->errors()->add('detalle_externo', 'El detalle es obligatorio para un turno externo.');
            }

            if (! $isExternal && filled($detail)) {
                $validator->errors()->add('detalle_externo', 'El detalle externo solo corresponde a turnos externos.');
            }
        }];
    }
}
