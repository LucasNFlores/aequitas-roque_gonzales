<?php

namespace App\Http\Requests;

use App\Models\Proceso;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProcesoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Proceso::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'integer', Rule::exists('clientes', 'id')->whereNull('deleted_at')],
            'profesional_id' => ['nullable', 'integer', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'servicio_id' => ['required', 'integer', Rule::exists('servicios', 'id')->whereNull('deleted_at')],
            'coordinador_id' => ['required', 'integer', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string', 'max:10000'],
            'fecha_inicio' => ['required', 'date'],
            'tipo' => ['required', Rule::in(Proceso::TIPOS)],
            'estado' => ['prohibited'],
            'motivo_rechazo' => ['prohibited'],
        ];
    }

    /**
     * Validate that foreign keys point to users with the expected role.
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
        }];
    }
}
