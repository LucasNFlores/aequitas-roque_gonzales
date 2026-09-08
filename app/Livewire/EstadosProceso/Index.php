<?php

namespace App\Livewire\EstadosProceso;

use App\Models\EstadoProceso;
use App\Models\Proceso;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Index extends Component
{
    public bool $showModal = false;

    public bool $showDeactivateModal = false;

    public ?int $editingEstadoId = null;

    public ?int $deactivatingEstadoId = null;

    public string $nombre = '';

    public string $search = '';

    public string $successMessage = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', EstadoProceso::class);
    }

    protected function rules(): array
    {
        $uniqueName = Rule::unique('estados_proceso', 'nombre')->whereNull('deleted_at');

        if ($this->editingEstadoId !== null) {
            $uniqueName->ignore($this->editingEstadoId);
        }

        return [
            'nombre' => ['required', 'string', 'max:100', $uniqueName],
        ];
    }

    public function createEstado(): void
    {
        Gate::authorize('create', EstadoProceso::class);

        $this->resetForm();
        $this->showModal = true;
    }

    public function editEstado(int $estadoId): void
    {
        $estado = EstadoProceso::query()->findOrFail($estadoId);

        Gate::authorize('update', $estado);

        $this->editingEstadoId = $estado->id;
        $this->nombre = $estado->nombre;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function saveEstado(): void
    {
        $estado = $this->editingEstadoId !== null
            ? EstadoProceso::query()->findOrFail($this->editingEstadoId)
            : null;

        Gate::authorize($estado ? 'update' : 'create', $estado ?? EstadoProceso::class);

        $validated = $this->validate();

        if ($estado) {
            $estado->update(['nombre' => $validated['nombre']]);
            $message = 'Estado actualizado correctamente.';
        } else {
            $slug = Str::slug($validated['nombre'], '_');

            if ($slug === '') {
                $this->addError('nombre', 'El nombre debe contener al menos una letra o número.');

                return;
            }

            if (EstadoProceso::withTrashed()->where('slug', $slug)->exists()) {
                $this->addError('nombre', 'Ya existe un estado con un nombre equivalente.');

                return;
            }

            EstadoProceso::query()->create([
                'nombre' => $validated['nombre'],
                'slug' => $slug,
                'activo' => true,
                'posicion' => ((int) EstadoProceso::query()->max('posicion')) + 1,
            ]);
            $message = 'Estado creado correctamente.';
        }

        $this->successMessage = $message;
        $this->closeModal();
        $this->dispatch('estado-guardado');
    }

    public function confirmDeactivate(int $estadoId): void
    {
        $estado = EstadoProceso::query()->findOrFail($estadoId);

        Gate::authorize('deactivate', $estado);

        $this->deactivatingEstadoId = $estado->id;
        $this->resetValidation();
        $this->showDeactivateModal = true;
    }

    public function deactivateEstado(): void
    {
        abort_unless($this->deactivatingEstadoId !== null, 404);

        $estado = EstadoProceso::query()->findOrFail($this->deactivatingEstadoId);

        Gate::authorize('deactivate', $estado);

        if (Proceso::query()->where('estado', $estado->slug)->exists()) {
            $this->addError('deactivate', 'No se puede eliminar un estado utilizado por procesos.');

            return;
        }

        $estado->update(['activo' => false]);
        $this->successMessage = 'Estado eliminado correctamente.';
        $this->closeDeactivateModal();
        $this->dispatch('estado-desactivado');
    }

    public function activateEstado(int $estadoId): void
    {
        $estado = EstadoProceso::query()->findOrFail($estadoId);

        Gate::authorize('activate', $estado);

        $estado->update(['activo' => true]);
        $this->successMessage = 'Estado activado correctamente.';
        $this->dispatch('estado-activado');
    }

    public function moveStateUp(int $estadoId): void
    {
        $estado = EstadoProceso::query()->findOrFail($estadoId);

        Gate::authorize('reorder', $estado);

        if (! $estado->activo) {
            return;
        }

        $previous = EstadoProceso::query()
            ->active()
            ->where('posicion', '<', $estado->posicion)
            ->orderByDesc('posicion')
            ->first();

        if ($previous) {
            $this->swapPositions($estado, $previous);
        }
    }

    public function moveStateDown(int $estadoId): void
    {
        $estado = EstadoProceso::query()->findOrFail($estadoId);

        Gate::authorize('reorder', $estado);

        if (! $estado->activo) {
            return;
        }

        $next = EstadoProceso::query()
            ->active()
            ->where('posicion', '>', $estado->posicion)
            ->ordered()
            ->first();

        if ($next) {
            $this->swapPositions($estado, $next);
        }
    }

    public function updatingSearch(): void
    {
        $this->successMessage = '';
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeDeactivateModal(): void
    {
        $this->showDeactivateModal = false;
        $this->deactivatingEstadoId = null;
        $this->resetValidation();
    }

    public function render(): View
    {
        $estados = EstadoProceso::query()
            ->when(trim($this->search) !== '', function (Builder $query): void {
                $query->where('nombre', 'like', '%'.trim($this->search).'%');
            })
            ->ordered()
            ->get();

        return view('livewire.estados-proceso.index', compact('estados'));
    }

    private function swapPositions(EstadoProceso $first, EstadoProceso $second): void
    {
        DB::transaction(function () use ($first, $second): void {
            $firstPosition = $first->posicion;

            $first->update(['posicion' => $second->posicion]);
            $second->update(['posicion' => $firstPosition]);
        });
    }

    private function resetForm(): void
    {
        $this->editingEstadoId = null;
        $this->nombre = '';
        $this->resetValidation();
    }
}
