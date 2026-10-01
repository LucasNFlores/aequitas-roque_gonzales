<?php

namespace App\Actions\Procesos;

use App\Models\Cliente;
use App\Models\EstadoProceso;
use App\Models\Proceso;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CrearProcesoAdicional
{
    /**
     * Create an additional process for an existing client exactly once per form submission.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Cliente $cliente, array $attributes): Proceso
    {
        return DB::transaction(function () use ($cliente, $attributes): Proceso {
            $existingProcess = Proceso::withTrashed()
                ->where('submission_token', $attributes['submission_token'])
                ->first();

            if ($existingProcess !== null) {
                return $this->resolveExistingSubmission($existingProcess, $cliente);
            }

            $pendingState = EstadoProceso::query()
                ->where('slug', 'pendiente')
                ->where('activo', true)
                ->lockForUpdate()
                ->first();

            if ($pendingState === null) {
                throw ValidationException::withMessages([
                    'estado' => 'El estado inicial pendiente no se encuentra activo.',
                ]);
            }

            $existingProcess = Proceso::withTrashed()
                ->where('submission_token', $attributes['submission_token'])
                ->first();

            if ($existingProcess !== null) {
                return $this->resolveExistingSubmission($existingProcess, $cliente);
            }

            return $cliente->procesos()->create([
                'submission_token' => $attributes['submission_token'],
                'profesional_id' => $attributes['profesional_id'] ?? null,
                'servicio_id' => $attributes['servicio_id'],
                'coordinador_id' => $attributes['coordinador_id'],
                'nombre' => $attributes['nombre'],
                'descripcion' => $attributes['descripcion'],
                'fecha_inicio' => $attributes['fecha_inicio'],
                'tipo' => $attributes['tipo'],
                'estado' => $pendingState->slug,
                'motivo_rechazo' => null,
            ]);
        });
    }

    private function resolveExistingSubmission(Proceso $proceso, Cliente $cliente): Proceso
    {
        if ($proceso->trashed() || $proceso->cliente_id !== $cliente->id) {
            throw ValidationException::withMessages([
                'submission_token' => 'Este envío ya fue utilizado. Actualice el formulario para continuar.',
            ]);
        }

        return $proceso;
    }
}
