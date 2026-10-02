<?php

namespace App\Livewire\Procesos;

use App\Actions\Procesos\AdmitirProceso;
use App\Actions\Procesos\AsignarProfesional;
use App\Actions\Procesos\RechazarProceso;
use App\Models\Cliente;
use App\Models\EstadoProceso;
use App\Models\HistorialEstadoProceso;
use App\Models\Proceso;
use App\Models\Servicio;
use App\Models\User;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $estadoFiltro = '';

    public string $clienteId = '';

    public string $servicioId = '';

    public string $coordinadorId = '';

    public string $profesionalId = '';

    public string $fechaDesde = '';

    public string $fechaHasta = '';

    public bool $showHistoryModal = false;

    public bool $showStateModal = false;

    public bool $showRejectionModal = false;

    public bool $showAssignmentModal = false;

    public ?int $selectedProcesoId = null;

    public string $pendingEstado = '';

    public string $motivoEstado = '';

    public string $motivoRechazo = '';

    public string $selectedProfesionalId = '';

    /** @var list<array{estado_anterior: string, estado_nuevo: string, motivo: string, usuario: string, fecha_cambio: string}> */
    public array $historial = [];

    public string $successMessage = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Proceso::class);
    }

    public function updating(string $property): void
    {
        if (in_array($property, $this->filterProperties(), true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->estadoFiltro = '';
        $this->clienteId = '';
        $this->servicioId = '';
        $this->coordinadorId = '';
        $this->profesionalId = '';
        $this->fechaDesde = '';
        $this->fechaHasta = '';
        $this->resetPage();
    }

    public function admitProcess(int $procesoId, AdmitirProceso $admitirProceso): void
    {
        $proceso = $this->authorizedProcess($procesoId, 'admit');

        if (! $this->runProcessAction(fn () => $admitirProceso->handle($proceso), 'admission')) {
            return;
        }

        $this->finishProcessAction('Proceso admitido correctamente.', stateChanged: true);
    }

    public function prepareRejection(int $procesoId): void
    {
        $proceso = $this->authorizedProcess($procesoId, 'reject');

        if ($proceso->estado !== Proceso::ESTADO_PENDIENTE) {
            $this->addError('motivoRechazo', 'Solo se pueden rechazar procesos pendientes.');

            return;
        }

        $this->selectedProcesoId = $proceso->id;
        $this->motivoRechazo = '';
        $this->resetValidation();
        $this->showRejectionModal = true;
    }

    public function saveRejection(RechazarProceso $rechazarProceso): void
    {
        $proceso = $this->authorizedSelectedProcess('reject');
        $this->motivoRechazo = trim($this->motivoRechazo);
        $validated = $this->validate([
            'motivoRechazo' => ['required', 'string', 'max:5000'],
        ], [
            'motivoRechazo.required' => 'El motivo de rechazo es obligatorio.',
            'motivoRechazo.max' => 'El motivo no puede superar los 5000 caracteres.',
        ]);

        if (! $this->runProcessAction(fn () => $rechazarProceso->handle($proceso, $validated['motivoRechazo']), 'motivoRechazo')) {
            return;
        }

        $this->finishProcessAction('Proceso rechazado correctamente.', stateChanged: true);
    }

    public function prepareProfessionalAssignment(int $procesoId): void
    {
        $proceso = $this->findVisibleProceso($procesoId);
        $ability = $proceso->profesional_id === null ? 'assignProfessional' : 'reassignProfessional';
        Gate::authorize($ability, $proceso);

        $this->selectedProcesoId = $proceso->id;
        $this->selectedProfesionalId = '';
        $this->resetValidation();
        $this->showAssignmentModal = true;
    }

    public function saveProfessionalAssignment(AsignarProfesional $asignarProfesional): void
    {
        $proceso = $this->selectedProceso();
        $ability = $proceso->profesional_id === null ? 'assignProfessional' : 'reassignProfessional';
        Gate::authorize($ability, $proceso);
        $validated = $this->validate([
            'selectedProfesionalId' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->whereNull('deleted_at'),
            ],
        ], [
            'selectedProfesionalId.required' => 'Selecciona un profesional.',
            'selectedProfesionalId.exists' => 'El usuario seleccionado ya no está activo.',
        ]);

        if (! $this->runProcessAction(fn () => $asignarProfesional->handle($proceso, (int) $validated['selectedProfesionalId']), 'selectedProfesionalId')) {
            return;
        }

        $this->finishProcessAction('Profesional asignado correctamente.');
    }

    public function prepareStateChange(int $procesoId): void
    {
        $proceso = $this->authorizedProcess($procesoId, 'updateState');

        $this->selectedProcesoId = $proceso->id;
        $this->pendingEstado = $proceso->estado;
        $this->motivoEstado = '';
        $this->resetValidation();
        $this->showStateModal = true;
    }

    public function saveStateChange(): void
    {
        $proceso = $this->authorizedSelectedProcess('updateState');
        $validated = $this->validate([
            'pendingEstado' => [
                'required',
                'string',
                Rule::exists('estados_proceso', 'slug')
                    ->where('activo', true)
                    ->whereNull('deleted_at'),
            ],
            'motivoEstado' => ['nullable', 'string', 'max:5000'],
        ], [
            'pendingEstado.required' => 'Selecciona un estado.',
            'pendingEstado.exists' => 'El estado seleccionado no está activo.',
            'motivoEstado.max' => 'El motivo no puede superar los 5000 caracteres.',
        ]);

        if (in_array($validated['pendingEstado'], [Proceso::ESTADO_ADMITIDO, Proceso::ESTADO_RECHAZADO], true)) {
            $this->addError('pendingEstado', 'Utiliza las acciones de admisión o rechazo para ese estado.');

            return;
        }

        if ($validated['pendingEstado'] === $proceso->estado) {
            $this->addError('pendingEstado', 'Selecciona un estado diferente al actual.');

            return;
        }

        $estado = EstadoProceso::query()
            ->active()
            ->where('slug', $validated['pendingEstado'])
            ->firstOrFail();

        DB::transaction(function () use ($proceso, $estado, $validated): void {
            $proceso->transitionTo($estado, $validated['motivoEstado'] ?: null);
        });

        $this->finishProcessAction('Estado del proceso actualizado correctamente.', stateChanged: true);
    }

    public function openHistory(int $procesoId): void
    {
        $proceso = $this->authorizedProcess($procesoId, 'viewHistory');
        $history = $proceso->historialEstados()->with('usuario:id,name')->get();
        $stateNames = EstadoProceso::withTrashed()
            ->whereIn('slug', $history->pluck('estado_nuevo')->merge($history->pluck('estado_anterior'))->filter()->unique())
            ->pluck('nombre', 'slug');

        $this->historial = $history->map(fn (HistorialEstadoProceso $change): array => [
            'estado_anterior' => $change->estado_anterior
                ? ($stateNames[$change->estado_anterior] ?? Str::headline($change->estado_anterior))
                : 'Sin estado previo',
            'estado_nuevo' => $stateNames[$change->estado_nuevo] ?? Str::headline($change->estado_nuevo),
            'motivo' => $change->motivo ?: 'Sin motivo registrado',
            'usuario' => $change->usuario?->name ?? 'Sistema',
            'fecha_cambio' => $change->fecha_cambio?->format('d/m/Y H:i') ?? 'Sin fecha',
        ])->all();
        $this->selectedProcesoId = $proceso->id;
        $this->showHistoryModal = true;
    }

    public function closeHistory(): void
    {
        $this->showHistoryModal = false;
        $this->selectedProcesoId = null;
        $this->historial = [];
    }

    public function closeStateModal(): void
    {
        $this->showStateModal = false;
        $this->clearSelectedProcess();
        $this->pendingEstado = '';
        $this->motivoEstado = '';
        $this->resetValidation();
    }

    public function closeRejectionModal(): void
    {
        $this->showRejectionModal = false;
        $this->clearSelectedProcess();
        $this->motivoRechazo = '';
        $this->resetValidation();
    }

    public function closeAssignmentModal(): void
    {
        $this->showAssignmentModal = false;
        $this->clearSelectedProcess();
        $this->selectedProfesionalId = '';
        $this->resetValidation();
    }

    public function render(): View
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $estados = EstadoProceso::query()->ordered()->get(['id', 'nombre', 'slug', 'activo']);
        $estadosActivos = $estados
            ->where('activo', true)
            ->reject(fn (EstadoProceso $estado): bool => in_array($estado->slug, [Proceso::ESTADO_ADMITIDO, Proceso::ESTADO_RECHAZADO], true))
            ->values();
        $servicios = Servicio::query()->orderBy('nombre')->get(['id', 'nombre']);
        $clientes = Cliente::query()
            ->whereHas('procesos', fn (Builder $query): Builder => $query->visibleTo($user))
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get(['id', 'nombre', 'apellido', 'dni']);
        $coordinadores = User::query()->role('Coordinador')->orderBy('name')->get(['id', 'name']);
        $profesionales = User::query()
            ->role('Profesional')
            ->when($user->hasRole('Profesional'), fn (Builder $query): Builder => $query->whereKey($user->id))
            ->orderBy('name')
            ->get(['id', 'name']);
        $profesionalesElegibles = $this->eligibleProfessionals();
        $search = trim($this->search);

        $procesos = Proceso::query()
            ->select([
                'id',
                'cliente_id',
                'profesional_id',
                'servicio_id',
                'coordinador_id',
                'nombre',
                'estado',
                'motivo_rechazo',
                'fecha_inicio',
                'honorarios',
                'updated_at',
            ])
            ->visibleTo($user)
            ->when($this->clienteId !== '', fn (Builder $query): Builder => $query->where('cliente_id', $this->clienteId))
            ->with([
                'cliente:id,nombre,apellido,dni',
                'servicio:id,nombre',
                'profesional:id,name',
                'coordinador:id,name',
            ])
            ->when($search !== '', fn (Builder $query): Builder => $this->applySearch($query, $search))
            ->when($this->estadoFiltro !== '', fn (Builder $query): Builder => $query->where('estado', $this->estadoFiltro))
            ->when($this->servicioId !== '', fn (Builder $query): Builder => $query->where('servicio_id', $this->servicioId))
            ->when($this->coordinadorId !== '', fn (Builder $query): Builder => $query->where('coordinador_id', $this->coordinadorId))
            ->when($this->profesionalId !== '', fn (Builder $query): Builder => $query->where('profesional_id', $this->profesionalId))
            ->when($this->fechaDesde !== '', fn (Builder $query): Builder => $query->whereDate('fecha_inicio', '>=', $this->fechaDesde))
            ->when($this->fechaHasta !== '', fn (Builder $query): Builder => $query->whereDate('fecha_inicio', '<=', $this->fechaHasta))
            ->latest('updated_at')
            ->paginate(10);

        return view('livewire.procesos.index', compact(
            'procesos',
            'estados',
            'estadosActivos',
            'servicios',
            'clientes',
            'coordinadores',
            'profesionales',
            'profesionalesElegibles',
        ));
    }

    /** @return list<string> */
    private function filterProperties(): array
    {
        return ['search', 'estadoFiltro', 'clienteId', 'servicioId', 'coordinadorId', 'profesionalId', 'fechaDesde', 'fechaHasta'];
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        $like = '%'.$search.'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query->where('nombre', 'like', $like)
                ->orWhere('estado', 'like', $like)
                ->orWhereHas('cliente', function (Builder $clientQuery) use ($like): void {
                    $clientQuery
                        ->where('nombre', 'like', $like)
                        ->orWhere('apellido', 'like', $like)
                        ->orWhere('dni', 'like', $like);
                })
                ->orWhereHas('servicio', fn (Builder $serviceQuery): Builder => $serviceQuery->where('nombre', 'like', $like))
                ->orWhereHas('profesional', fn (Builder $professionalQuery): Builder => $professionalQuery->where('name', 'like', $like))
                ->orWhereHas('coordinador', fn (Builder $coordinatorQuery): Builder => $coordinatorQuery->where('name', 'like', $like));
        });
    }

    private function eligibleProfessionals(): Collection
    {
        if (! $this->showAssignmentModal || $this->selectedProcesoId === null) {
            return User::query()->whereKey(0)->get();
        }

        $proceso = $this->findVisibleProceso($this->selectedProcesoId);

        return User::query()
            ->role('Profesional')
            ->whereHas('servicios', fn (Builder $query): Builder => $query->whereKey($proceso->servicio_id))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function authorizedProcess(int $procesoId, string $ability): Proceso
    {
        $proceso = $this->findVisibleProceso($procesoId);
        Gate::authorize($ability, $proceso);

        return $proceso;
    }

    private function authorizedSelectedProcess(string $ability): Proceso
    {
        $proceso = $this->selectedProceso();
        Gate::authorize($ability, $proceso);

        return $proceso;
    }

    private function runProcessAction(callable $action, string $errorField): bool
    {
        try {
            $action();
        } catch (DomainException $exception) {
            $this->addError($errorField, $exception->getMessage());

            return false;
        }

        return true;
    }

    private function finishProcessAction(string $message, bool $stateChanged = false): void
    {
        $this->successMessage = $message;
        $this->closeStateModal();
        $this->closeRejectionModal();
        $this->closeAssignmentModal();
        if ($stateChanged) {
            $this->dispatch('proceso-estado-actualizado');
        }
        $this->dispatch('proceso-gestionada');
    }

    private function selectedProceso(): Proceso
    {
        abort_unless($this->selectedProcesoId !== null, 404);

        return $this->findVisibleProceso($this->selectedProcesoId);
    }

    private function findVisibleProceso(int $procesoId): Proceso
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        return Proceso::query()
            ->visibleTo($user)
            ->findOrFail($procesoId);
    }

    private function clearSelectedProcess(): void
    {
        $this->selectedProcesoId = null;
    }
}
