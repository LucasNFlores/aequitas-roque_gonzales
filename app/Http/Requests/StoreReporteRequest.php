<?php

namespace App\Http\Requests;

use App\Models\Proceso;
use App\Models\Reporte;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreReporteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Reporte::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'proceso_id' => ['required', 'integer', Rule::exists('procesos', 'id')->whereNull('deleted_at')],
            'profesional_id' => ['prohibited'],
            'contenido' => ['required', 'string', 'max:20000'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    /**
     * A professional may only create a report for an assigned process.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $actor = $this->user();
            $proceso = Proceso::query()->find($this->input('proceso_id'));

            if ($actor instanceof User && $proceso instanceof Proceso && ! $actor->can('createFor', $proceso)) {
                $validator->errors()->add('proceso_id', 'El Profesional solo puede registrar reportes en procesos asignados.');
            }
        }];
    }
}
