<?php

namespace App\Http\Requests;

use App\Models\Proceso;
use App\Models\Turno;
use App\Models\User;
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
     * HU-11: proceso activo, coherencia cliente/profesional y conflicto por rango.
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

            $clienteId = $this->input('cliente_id', $turno?->cliente_id);
            $profesionalId = $this->input('profesional_id', $turno?->profesional_id);
            $procesoId = $this->has('proceso_id') ? $this->input('proceso_id') : $turno?->proceso_id;

            if ($profesionalId !== null && $this->has('profesional_id') && ! User::role('Profesional')->whereKey($profesionalId)->exists()) {
                $validator->errors()->add('profesional_id', 'El usuario seleccionado debe tener el rol Profesional.');
            }

            $proceso = $procesoId !== null ? Proceso::query()->find($procesoId) : null;

            if ($proceso instanceof Proceso && (int) $proceso->cliente_id !== (int) $clienteId) {
                $validator->errors()->add('cliente_id', 'El cliente no coincide con el proceso seleccionado.');
            }

            if (
                $proceso instanceof Proceso
                && $proceso->profesional_id !== null
                && (int) $proceso->profesional_id !== (int) $profesionalId
            ) {
                $validator->errors()->add('profesional_id', 'El profesional no coincide con el proceso seleccionado.');
            }

            if ($type === 'seguimiento') {
                if (! $proceso instanceof Proceso) {
                    $validator->errors()->add('proceso_id', 'El turno de seguimiento requiere un proceso activo.');
                } elseif (in_array($proceso->estado, Turno::PROCESO_ESTADOS_INACTIVOS, true)) {
                    $validator->errors()->add('proceso_id', 'El proceso seleccionado no está activo.');
                }
            }

            $fechaHoraInput = $this->input('fecha_hora', $turno?->fecha_hora?->toDateTimeString());

            if (
                $profesionalId !== null
                && filled($fechaHoraInput)
                && ! $validator->errors()->hasAny(['fecha_hora', 'profesional_id'])
                && ($this->has('fecha_hora') || $this->has('profesional_id') || $this->has('es_externo'))
            ) {
                try {
                    $fechaHora = \Carbon\Carbon::parse($fechaHoraInput);

                    if ($fechaHora->isPast() && $this->has('fecha_hora')) {
                        $validator->errors()->add('fecha_hora', 'La fecha y hora deben ser futuras.');
                    } elseif (Turno::existeConflicto((int) $profesionalId, $fechaHora, $turno?->id, $isExternal)) {
                        $validator->errors()->add('fecha_hora', 'El profesional ya tiene un turno en ese horario o la fecha está bloqueada por un turno externo.');
                    }
                } catch (\Throwable) {
                    // El validador de fecha ya reporta formato inválido.
                }
            }
        }];
    }
}
