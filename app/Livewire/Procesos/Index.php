<?php

namespace App\Livewire\Procesos;

use App\Models\EstadoProceso;
use App\Models\HistorialEstadoProceso;
use App\Models\Proceso;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
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

    public string $profesionalId = '';

    public bool $showHistoryModal = false;

    public bool $showStateModal = false;

    public ?int $selectedProcesoId = null;

    public string $pendingEstado = '';

    public string $motivoEstado = '';

    /** @var list<array{estado_anterior: string, estado_nuevo: string, motivo: string, usuario: string, fecha_cambio: string}> */
    public array $historial = [];

    public string $successMessage = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Proceso::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingEstadoFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingProfesionalId(): void
    {
        $this->resetPage();
    }

    public function prepareStateChange(int $procesoId): void
    {
        $proceso = $this->findVisibleProceso($procesoId);

        Gate::authorize('updateState', $proceso);

        $this->selectedProcesoId = $proceso->id;
        $this->pendingEstado = $proceso->estado;
        $this->motivoEstado = '';
        $this->resetValidation();
        $this->showStateModal = true;
    }

    public function saveStateChange(): void
    {
        $proceso = $this->selectedProceso();

        Gate::authorize('updateState', $proceso);

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

        $this->successMessage = 'Estado del proceso actualizado correctamente.';
        $this->closeStateModal();
        $this->dispatch('proceso-estado-actualizado');
    }

    public function openHistory(int $procesoId): void
    {
        $proceso = $this->findVisibleProceso($procesoId);

        Gate::authorize('viewHistory', $proceso);

        $history = $proceso->historialEstados()
            ->with('usuario:id,name')
            ->get();
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
        $this->selectedProcesoId = null;
        $this->pendingEstado = '';
        $this->motivoEstado = '';
        $this->resetValidation();
    }

    public function render(): View
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $estados = EstadoProceso::query()
            ->ordered()
            ->get(['id', 'nombre', 'slug', 'activo']);
        $estadosActivos = $estados->where('activo', true)->values();
        $profesionales = User::query()
            ->role('Profesional')
            ->orderBy('name')
            ->get(['id', 'name']);
        $search = trim($this->search);

        $procesos = Proceso::query()
            ->select([
                'id',
                'cliente_id',
                'profesional_id',
                'coordinador_id',
                'nombre',
                'estado',
                'fecha_inicio',
                'updated_at',
            ])
            ->visibleTo($user)
            ->with([
                'cliente:id,nombre,apellido,dni',
                'profesional:id,name',
                'coordinador:id,name',
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $like = '%'.$search.'%';

                $query->where(function (Builder $query) use ($like): void {
                    $query->where('nombre', 'like', $like)
                        ->orWhere('estado', 'like', $like)
                        ->orWhereHas('cliente', function (Builder $clientQuery) use ($like): void {
                            $clientQuery
                                ->where('nombre', 'like', $like)
                                ->orWhere('apellido', 'like', $like)
                                ->orWhere('dni', 'like', $like);
                        })
                        ->orWhereHas('profesional', fn (Builder $professionalQuery): Builder => $professionalQuery->where('name', 'like', $like));
                });
            })
            ->when($this->estadoFiltro !== '', fn (Builder $query): Builder => $query->where('estado', $this->estadoFiltro))
            ->when($this->profesionalId !== '', fn (Builder $query): Builder => $query->where('profesional_id', $this->profesionalId))
            ->latest('updated_at')
            ->paginate(10);

        return view('livewire.procesos.index', compact('procesos', 'estados', 'estadosActivos', 'profesionales'));
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
}
