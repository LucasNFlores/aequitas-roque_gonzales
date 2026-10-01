<?php

namespace App\Livewire\Turnos;

use App\Models\Cliente;
use App\Models\Proceso;
use App\Models\Turno;
use App\Models\User;
use App\Services\Turnos\TurnoScheduler;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Form extends Component
{
    #[Locked]
    public ?int $turnoId = null;

    public string $clienteId = '';

    public string $profesionalId = '';

    public string $procesoId = '';

    public string $fechaHora = '';

    public string $tipo = 'consulta_inicial';

    public string $successMessage = '';

    public function mount(?Turno $turno = null): void
    {
        if ($turno instanceof Turno) {
            Gate::authorize('update', $turno);
            abort_if($turno->es_externo, 404);

            $this->turnoId = $turno->id;
            $this->fechaHora = $turno->fecha_hora?->format('Y-m-d\TH:i') ?? '';

            return;
        }

        Gate::authorize('create', Turno::class);
        $this->fechaHora = now()->addHour()->format('Y-m-d\TH:i');
    }

    public function save(TurnoScheduler $scheduler): mixed
    {
        if ($this->turnoId !== null) {
            return $this->reschedule($scheduler);
        }

        Gate::authorize('create', Turno::class);

        $validated = $this->validate([
            'clienteId' => ['required', 'integer', Rule::exists('clientes', 'id')->whereNull('deleted_at')],
            'profesionalId' => ['required', 'integer', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'procesoId' => ['nullable', 'integer', Rule::exists('procesos', 'id')->whereNull('deleted_at')],
            'fechaHora' => ['required', 'date', 'after:now'],
            'tipo' => ['required', Rule::in(['consulta_inicial', 'seguimiento'])],
        ], [
            'clienteId.required' => 'Selecciona un cliente.',
            'profesionalId.required' => 'Selecciona un profesional.',
            'fechaHora.required' => 'Indica fecha y hora del turno.',
        ]);

        $this->validateRelationships($validated['procesoId'] ?: null);
        Gate::authorize($validated['tipo'] === 'seguimiento'
            ? 'agendar_turnos_seguimiento'
            : 'agendar_turnos_internos');

        $turno = $scheduler->schedule([
            'cliente_id' => (int) $validated['clienteId'],
            'profesional_id' => (int) $validated['profesionalId'],
            'proceso_id' => $validated['procesoId'] !== '' ? (int) $validated['procesoId'] : null,
            'fecha_hora' => $validated['fechaHora'],
            'tipo' => $validated['tipo'],
        ]);

        return redirect()->route('turnos.show', $turno)
            ->with('success', 'Turno registrado correctamente.');
    }

    public function render(): View
    {
        $clientes = Cliente::query()->orderBy('apellido')->orderBy('nombre')->get();
        $profesionales = User::query()->role('Profesional')->orderBy('name')->get(['id', 'name']);
        $procesos = Proceso::query()
            ->with('cliente:id,nombre,apellido')
            ->whereNotIn('estado', Turno::PROCESO_ESTADOS_INACTIVOS)
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('livewire.turnos.form', compact('clientes', 'profesionales', 'procesos'));
    }

    private function reschedule(TurnoScheduler $scheduler): mixed
    {
        $turno = Turno::query()
            ->whereKey($this->turnoId)
            ->where('es_externo', false)
            ->firstOrFail();

        Gate::authorize('update', $turno);
        $validated = $this->validate(['fechaHora' => ['required', 'date', 'after:now']]);
        $scheduler->reschedule($turno, $validated['fechaHora']);

        return redirect()->route('turnos.show', $turno)
            ->with('success', 'Turno reprogramado correctamente. Se liberó la disponibilidad anterior.');
    }

    private function validateRelationships(int|string|null $procesoId): void
    {
        $validator = validator([
            'clienteId' => $this->clienteId,
            'profesionalId' => $this->profesionalId,
            'procesoId' => $procesoId,
            'tipo' => $this->tipo,
        ], []);

        $validator->after(function (Validator $validator) use ($procesoId): void {
            $professional = User::query()->role('Profesional')->find($this->profesionalId);

            if (! $professional instanceof User) {
                $validator->errors()->add('profesionalId', 'El usuario seleccionado debe ser un profesional activo.');
            }

            $process = $procesoId !== null ? Proceso::query()->find($procesoId) : null;

            if ($procesoId !== null && ! $process instanceof Proceso) {
                $validator->errors()->add('procesoId', 'El proceso seleccionado no está disponible.');
            }

            if ($process instanceof Proceso && (int) $process->cliente_id !== (int) $this->clienteId) {
                $validator->errors()->add('clienteId', 'El cliente no coincide con el proceso seleccionado.');
            }

            if (
                $process instanceof Proceso
                && $process->profesional_id !== null
                && (int) $process->profesional_id !== (int) $this->profesionalId
            ) {
                $validator->errors()->add('profesionalId', 'El profesional no coincide con el proceso seleccionado.');
            }

            if ($this->tipo === 'seguimiento') {
                if (! $process instanceof Proceso) {
                    $validator->errors()->add('procesoId', 'El turno de seguimiento requiere un proceso activo.');
                } elseif (in_array($process->estado, Turno::PROCESO_ESTADOS_INACTIVOS, true)) {
                    $validator->errors()->add('procesoId', 'El proceso seleccionado no está activo.');
                }
            }
        });

        $validator->validate();
    }
}
