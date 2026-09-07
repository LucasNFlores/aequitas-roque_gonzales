<?php

namespace App\Http\Requests;

use App\Models\Proceso;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProcesoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $proceso = $this->route('proceso');

        return $proceso instanceof Proceso
            && ($this->user()?->can('update', $proceso) ?? false);
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['sometimes', 'required', 'integer', Rule::exists('clientes', 'id')->whereNull('deleted_at')],
            'profesional_id' => ['sometimes', 'nullable', 'integer', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'servicio_id' => ['sometimes', 'required', 'integer', Rule::exists('servicios', 'id')->whereNull('deleted_at')],
            'coordinador_id' => ['sometimes', 'required', 'integer', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'descripcion' => ['sometimes', 'required', 'string', 'max:10000'],
            'fecha_inicio' => ['sometimes', 'required', 'date'],
            'tipo' => ['sometimes', 'required', Rule::in(Proceso::TIPOS)],
            'estado' => ['sometimes', 'required', Rule::in(Proceso::ESTADOS)],
            'motivo_rechazo' => ['sometimes', 'nullable', 'string', 'max:5000', 'required_if:estado,rechazado'],
        ];
    }

    /**
     * Validate role-specific foreign keys and rejection data.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $coordinadorId = $this->input('coordinador_id');

            if ($coordinadorId !== null && ! User::role('Coordinador')->whereKey($coordinadorId)->exists()) {
                $validator->errors()->add('coordinador_id', 'El usuario seleccionado debe tener el rol Coordinador.');
            }

            $profesionalId = $this->input('profesional_id');

            if ($profesionalId !== null && ! User::role('Profesional')->whereKey($profesionalId)->exists()) {
                $validator->errors()->add('profesional_id', 'El usuario seleccionado debe tener el rol Profesional.');
            }

            $proceso = $this->route('proceso');
            $estado = $this->input('estado', $proceso?->estado);
            $motivo = $this->input('motivo_rechazo', $proceso?->motivo_rechazo);

            if ($estado === 'rechazado' && blank($motivo)) {
                $validator->errors()->add('motivo_rechazo', 'El motivo de rechazo es obligatorio para un proceso rechazado.');
            }

            if ($this->has('motivo_rechazo') && $estado !== 'rechazado' && filled($this->input('motivo_rechazo'))) {
                $validator->errors()->add('motivo_rechazo', 'El motivo de rechazo solo corresponde a procesos rechazados.');
            }
        }];
    }
}
