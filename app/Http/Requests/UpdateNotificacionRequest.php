<?php

namespace App\Http\Requests;

use App\Models\Notificacion;
use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $notificacion = $this->route('notificacion');

        return $notificacion instanceof Notificacion
            && ($this->user()?->can('update', $notificacion) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}
