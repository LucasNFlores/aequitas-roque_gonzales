<?php

namespace App\Http\Requests;

use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreNotificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Notificacion::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'cliente_id' => ['nullable', 'integer', Rule::exists('clientes', 'id')->whereNull('deleted_at')],
            'canal' => ['required', Rule::in(Notificacion::CANALES)],
            'mensaje' => ['required', 'string', 'max:10000'],
            'fecha_envio' => ['required', 'date'],
            'estado' => ['sometimes', Rule::in(Notificacion::ESTADOS)],
        ];
    }

    /**
     * Require at least one notification recipient.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $this->filled('user_id') && ! $this->filled('cliente_id')) {
                $validator->errors()->add('user_id', 'La notificación debe tener al menos un destinatario.');
            }
        }];
    }
}
