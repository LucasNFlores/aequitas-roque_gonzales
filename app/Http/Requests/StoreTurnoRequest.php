<?php

namespace App\Http\Requests;

use App\Models\Proceso;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTurnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Turno::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'integer', Rule::exists('clientes', 'id')->whereNull('deleted_at')],
            'profesional_id' => ['required', 'integer', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'proceso_id' => ['nullable', 'integer', Rule::exists('procesos', 'id')->whereNull('deleted_at')],
            'fecha_hora' => ['required', 'date', 'after:now'],
            'es_externo' => ['sometimes', 'boolean'],
            'detalle_externo' => ['nullable', 'string', 'max:5000'],
            'tipo' => ['required', Rule::in(Turno::TIPOS)],
        ];
    }

    /**
     * Keep the external flag, type and detail consistent.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $isExternal = $this->boolean('es_externo');
            $type = $this->input('tipo');

            $professionalId = $this->input('profesional_id');

            if ($professionalId !== null && ! User::role('Profesional')->whereKey($professionalId)->exists()) {
                $validator->errors()->add('profesional_id', 'El usuario seleccionado debe tener el rol Profesional.');
            }

            if ($isExternal !== ($type === 'externo')) {
                $validator->errors()->add('tipo', 'El tipo de turno no coincide con el indicador de turno externo.');
            }

            if ($isExternal && blank($this->input('detalle_externo'))) {
                $validator->errors()->add('detalle_externo', 'El detalle es obligatorio para un turno externo.');
            }

            if (! $isExternal && filled($this->input('detalle_externo'))) {
                $validator->errors()->add('detalle_externo', 'El detalle externo solo corresponde a turnos externos.');
            }

            $proceso = Proceso::query()->find($this->input('proceso_id'));

            if ($proceso instanceof Proceso && (int) $proceso->cliente_id !== (int) $this->input('cliente_id')) {
                $validator->errors()->add('cliente_id', 'El cliente no coincide con el proceso seleccionado.');
            }

            if (
                $proceso instanceof Proceso
                && $proceso->profesional_id !== null
                && (int) $proceso->profesional_id !== (int) $this->input('profesional_id')
            ) {
                $validator->errors()->add('profesional_id', 'El profesional no coincide con el proceso seleccionado.');
            }

            // HU-11: seguimiento exige proceso activo; consulta inicial puede no tenerlo.
            if ($type === 'seguimiento') {
                if (! $proceso instanceof Proceso) {
                    $validator->errors()->add('proceso_id', 'El turno de seguimiento requiere un proceso activo.');
                } elseif (in_array($proceso->estado, Turno::PROCESO_ESTADOS_INACTIVOS, true)) {
                    $validator->errors()->add('proceso_id', 'El proceso seleccionado no está activo.');
                }
            }

            if ($proceso instanceof Proceso && in_array($proceso->estado, Turno::PROCESO_ESTADOS_INACTIVOS, true) && $type === 'seguimiento') {
                // Ya reportado arriba; se mantiene para claridad de regla.
            }

            // HU-11: conflicto por rango + bloqueo por externo (mismo profesional).
            if ($professionalId !== null && filled($this->input('fecha_hora')) && ! $validator->errors()->hasAny(['fecha_hora', 'profesional_id'])) {
                try {
                    $fechaHora = \Carbon\Carbon::parse($this->input('fecha_hora'));

                    if (Turno::existeConflicto((int) $professionalId, $fechaHora, null, $isExternal)) {
                        $validator->errors()->add('fecha_hora', 'El profesional ya tiene un turno en ese horario o la fecha está bloqueada por un turno externo.');
                    }
                } catch (\Throwable) {
                    // El validador de fecha ya reporta formato inválido.
                }
            }
        }];
    }
}
