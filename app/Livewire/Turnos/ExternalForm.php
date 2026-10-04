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

class ExternalForm extends Component
{
    #[Locked]
    public ?int $turnoId = null;

    public string $clienteId = '';

    public string $profesionalId = '';

    public string $procesoId = '';

    public string $fechaHora = '';

    public string $detalleExterno = '';

    public function mount(?int $turnoId = null): void
    {
        if ($turnoId !== null) {
            $turno = Turno::query()->findOrFail($turnoId);
            Gate::authorize('update', $turno);
            abort_unless($turno->es_externo && $turno->tipo === 'externo', 404);

            $this->turnoId = $turno->id;
            $this->clienteId = (string) $turno->cliente_id;
            $this->profesionalId = (string) $turno->profesional_id;
            $this->procesoId = (string) ($turno->proceso_id ?? '');
            $this->fechaHora = $turno->fecha_hora?->format('Y-m-d\TH:i') ?? '';
            $this->detalleExterno = $turno->detalle_externo ?? '';

            return;
        }

        Gate::authorize('createExternal', Turno::class);
        $this->fechaHora = now()->addDay()->setTime(9, 0)->format('Y-m-d\TH:i');
    }

    public function save(TurnoScheduler $scheduler): mixed
    {
        if ($this->turnoId !== null) {
            return $this->reschedule($scheduler);
        }

        Gate::authorize('createExternal', Turno::class);

        $validated = $this->validate([
            'clienteId' => ['required', 'integer', Rule::exists('clientes', 'id')->whereNull('deleted_at')],
            'profesionalId' => ['required', 'integer', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'procesoId' => ['nullable', 'integer', Rule::exists('procesos', 'id')->whereNull('deleted_at')],
            'fechaHora' => ['required', 'date', 'after:now'],
            'detalleExterno' => ['required', 'string'],
        ], [
            'clienteId.required' => 'Selecciona un cliente.',
            'profesionalId.required' => 'Selecciona un profesional.',
            'fechaHora.required' => 'Indica fecha y hora del compromiso.',
            'detalleExterno.required' => 'Indica el detalle del compromiso externo.',
        ]);

        $this->validateRelationships($validated['procesoId'] ?: null);

        $turno = $scheduler->scheduleExternal([
            'cliente_id' => (int) $validated['clienteId'],
            'profesional_id' => (int) $validated['profesionalId'],
            'proceso_id' => $validated['procesoId'] !== '' ? (int) $validated['procesoId'] : null,
            'fecha_hora' => $validated['fechaHora'],
            'detalle_externo' => trim($validated['detalleExterno']),
        ]);

        return redirect()->route('turnos.show', $turno)
            ->with('success', 'Compromiso externo registrado correctamente.');
    }

    public function render(): View
    {
        $turno = $this->turnoId !== null
            ? Turno::query()->with(['cliente', 'profesional', 'proceso'])->findOrFail($this->turnoId)
            : null;

        if ($turno instanceof Turno) {
            Gate::authorize('update', $turno);
            abort_unless($turno->es_externo && $turno->tipo === 'externo', 404);
        }

        $clientes = Cliente::query()->orderBy('apellido')->orderBy('nombre')->get();
        $profesionales = User::query()->role('Profesional')->orderBy('name')->get(['id', 'name']);
        $procesos = Proceso::query()
            ->with('cliente:id,nombre,apellido')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('livewire.turnos.external-form', compact('clientes', 'profesionales', 'procesos', 'turno'));
    }

    private function reschedule(TurnoScheduler $scheduler): mixed
    {
        $turno = Turno::query()
            ->where('tipo', 'externo')
            ->where('es_externo', true)
            ->findOrFail($this->turnoId);

        Gate::authorize('update', $turno);

        $validated = $this->validate([
            'fechaHora' => ['required', 'date', 'after:now'],
            'detalleExterno' => ['required', 'string'],
        ], [
            'fechaHora.required' => 'Indica fecha y hora del compromiso.',
            'detalleExterno.required' => 'Indica el detalle del compromiso externo.',
        ]);

        $scheduler->rescheduleExternal(
            $turno,
            $validated['fechaHora'],
            trim($validated['detalleExterno']),
        );

        return redirect()->route('turnos.show', $turno)
            ->with('success', 'Compromiso externo reprogramado; se liberó la fecha anterior.');
    }

    private function validateRelationships(int|string|null $procesoId): void
    {
        $validator = validator([
            'clienteId' => $this->clienteId,
            'profesionalId' => $this->profesionalId,
            'procesoId' => $procesoId,
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
        });

        $validator->validate();
    }
}
