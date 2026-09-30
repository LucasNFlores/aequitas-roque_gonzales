<?php

namespace App\Livewire\Categorias;

use App\Models\CategoriaDocumento;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Index extends Component
{
    public bool $showModal = false;

    public bool $showDeactivateModal = false;

    public bool $showDeleteModal = false;

    public bool $showRestoreModal = false;

    public ?int $editingCategoriaId = null;

    public ?int $deactivatingCategoriaId = null;

    public ?int $deletingCategoriaId = null;

    public ?int $restoringCategoriaId = null;

    public string $nombre = '';

    public string $descripcion = '';

    public string $search = '';

    public string $filtro = 'todas';

    public string $successMessage = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', CategoriaDocumento::class);
    }

    protected function rules(): array
    {
        $unique = Rule::unique('categorias_documento', 'nombre');
        if ($this->editingCategoriaId !== null) {
            $unique->ignore($this->editingCategoriaId);
        }

        return [
            'nombre' => ['required', 'string', 'min:3', 'max:100', $unique],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.unique' => 'Ya existe una categoría con ese nombre. Use otro nombre o reactive la existente.',
        ];
    }

    public function createCategoria(): void
    {
        Gate::authorize('create', CategoriaDocumento::class);
        $this->resetForm();
        $this->showModal = true;
    }

    public function editCategoria(int $categoriaId): void
    {
        $categoria = CategoriaDocumento::query()->findOrFail($categoriaId);
        Gate::authorize('update', $categoria);

        $this->editingCategoriaId = $categoria->id;
        $this->nombre = $categoria->nombre;
        $this->descripcion = $categoria->descripcion ?? '';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function saveCategoria(): void
    {
        $categoria = $this->editingCategoriaId !== null
            ? CategoriaDocumento::query()->findOrFail($this->editingCategoriaId)
            : null;

        Gate::authorize($categoria ? 'update' : 'create', $categoria ?? CategoriaDocumento::class);

        // Normalizar antes de validar (trim + colapsar espacios)
        $this->nombre = preg_replace('/\s+/', ' ', trim($this->nombre));
        $this->descripcion = trim($this->descripcion);

        // Unicidad case-insensitive también en SQLite (MySQL ya lo hace por collation)
        $duplicada = CategoriaDocumento::query()
            ->withTrashed()
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($this->nombre, 'UTF-8')])
            ->when($this->editingCategoriaId !== null, fn ($q) => $q->where('id', '!=', $this->editingCategoriaId))
            ->exists();
        if ($this->nombre !== '' && $duplicada) {
            $this->addError('nombre', 'Ya existe una categoría con ese nombre. Use otro nombre o reactive la existente.');

            return;
        }

        $validated = $this->validate();

        if ($categoria) {
            $categoria->update($validated); // el id no cambia: asociaciones intactas
            $message = 'Categoría actualizada sin perder asociaciones.';
        } else {
            CategoriaDocumento::query()->create([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'activo' => true,
            ]);
            $message = 'Categoría creada y disponible para clasificar documentos.';
        }

        $this->successMessage = $message;
        $this->closeModal();
        $this->dispatch('categoria-guardada');
    }

    public function confirmDeactivate(int $categoriaId): void
    {
        $categoria = CategoriaDocumento::query()->findOrFail($categoriaId);
        Gate::authorize('deactivate', $categoria);

        $this->deactivatingCategoriaId = $categoria->id;
        $this->resetValidation();
        $this->showDeactivateModal = true;
    }

    public function deactivateCategoria(): void
    {
        abort_unless($this->deactivatingCategoriaId !== null, 404);
        $categoria = CategoriaDocumento::query()->findOrFail($this->deactivatingCategoriaId);
        Gate::authorize('deactivate', $categoria);

        // Spec gestion-juridica: impedir desactivar categorías en uso
        // sin transición válida (mirror de EstadosProceso/Index).
        $n = $categoria->documentos()->count();
        if ($n > 0) {
            $this->addError('deactivate', "No se puede desactivar: tiene {$n} documentos asociados. Reasigne esos documentos o conserve la categoría activa.");

            return;
        }

        $categoria->update(['activo' => false]);

        $this->successMessage = 'Categoría desactivada. Ya no se ofrecerá para nuevas cargas.';
        $this->closeDeactivateModal();
        $this->dispatch('categoria-desactivada');
    }

    public function activateCategoria(int $categoriaId): void
    {
        $categoria = CategoriaDocumento::query()->findOrFail($categoriaId);
        Gate::authorize('activate', $categoria);

        $categoria->update(['activo' => true]);
        $this->successMessage = 'Categoría reactivada y disponible para nuevas cargas.';
        $this->dispatch('categoria-activada');
    }

    public function confirmDelete(int $categoriaId): void
    {
        $categoria = CategoriaDocumento::query()->findOrFail($categoriaId);
        Gate::authorize('delete', $categoria);

        $this->deletingCategoriaId = $categoria->id;
        $this->resetValidation();
        $this->showDeleteModal = true;
    }

    public function deleteCategoria(): void
    {
        abort_unless($this->deletingCategoriaId !== null, 404);

        $categoria = CategoriaDocumento::query()->findOrFail($this->deletingCategoriaId);
        Gate::authorize('delete', $categoria);

        $categoria->delete();

        $this->successMessage = 'Categoría dada de baja. Los documentos asociados y sus archivos se conservan en el historial.';
        $this->closeDeleteModal();
        $this->dispatch('categoria-eliminada');
    }

    public function confirmRestore(int $categoriaId): void
    {
        $categoria = CategoriaDocumento::withTrashed()->findOrFail($categoriaId);
        Gate::authorize('restore', $categoria);

        abort_unless($categoria->trashed(), 404);

        $this->restoringCategoriaId = $categoria->id;
        $this->resetValidation();
        $this->showRestoreModal = true;
    }

    public function restoreCategoria(): void
    {
        abort_unless($this->restoringCategoriaId !== null, 404);

        $categoria = CategoriaDocumento::withTrashed()->findOrFail($this->restoringCategoriaId);
        Gate::authorize('restore', $categoria);

        abort_unless($categoria->trashed(), 404);

        $categoria->restore();

        $this->successMessage = $categoria->activo
            ? 'Categoría restaurada y disponible para nuevas cargas.'
            : 'Categoría restaurada, pero permanece inactiva y no está disponible para nuevas cargas.';
        $this->closeRestoreModal();
        $this->dispatch('categoria-restaurada');
    }

    public function updatingSearch(): void
    {
        $this->successMessage = '';
    }

    public function updatingFiltro(): void
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
        $this->deactivatingCategoriaId = null;
        $this->resetValidation();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingCategoriaId = null;
        $this->resetValidation();
    }

    public function closeRestoreModal(): void
    {
        $this->showRestoreModal = false;
        $this->restoringCategoriaId = null;
        $this->resetValidation();
    }

    public function render(): View
    {
        $categorias = CategoriaDocumento::query()
            ->withCount('documentos')
            ->when($this->filtro === 'baja', fn (Builder $q) => $q->onlyTrashed())
            ->when(trim($this->search) !== '', fn (Builder $q) => $q->where('nombre', 'like', '%'.trim($this->search).'%'))
            ->when($this->filtro === 'activas', fn (Builder $q) => $q->where('activo', true))
            ->when($this->filtro === 'inactivas', fn (Builder $q) => $q->where('activo', false))
            ->orderBy('nombre')
            ->get();

        $categoriaEnConfirmacionId = $this->deletingCategoriaId ?? $this->deactivatingCategoriaId;
        $docsEnUso = $categoriaEnConfirmacionId
            ? (int) CategoriaDocumento::withTrashed()->find($categoriaEnConfirmacionId)?->documentos()->count()
            : 0;
        $docsAsociados = $this->deletingCategoriaId
            ? (int) CategoriaDocumento::withTrashed()->find($this->deletingCategoriaId)?->documentos()->withTrashed()->count()
            : $docsEnUso;

        $categoriaRestaurando = $this->restoringCategoriaId
            ? CategoriaDocumento::withTrashed()->find($this->restoringCategoriaId)
            : null;

        return view('livewire.categorias.index', compact('categorias', 'docsEnUso', 'docsAsociados', 'categoriaRestaurando'));
    }

    private function resetForm(): void
    {
        $this->editingCategoriaId = null;
        $this->nombre = '';
        $this->descripcion = '';
        $this->resetValidation();
    }
}
