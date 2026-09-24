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

    public ?int $editingCategoriaId = null;

    public ?int $deactivatingCategoriaId = null;

    public string $nombre = '';

    public string $descripcion = '';

    public string $search = '';

    public string $filtro = 'todas'; // todas|activas|inactivas

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

        // Baja lógica siempre permitida aun en uso: se conserva historial.
        $n = $categoria->documentos()->count();
        $categoria->update(['activo' => false]);

        $this->successMessage = $n > 0
            ? "Categoría desactivada. Se conservaron {$n} documentos históricos y dejará de ofrecerse para nuevas cargas."
            : 'Categoría desactivada. Ya no se ofrecerá para nuevas cargas.';
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

    public function render(): View
    {
        $categorias = CategoriaDocumento::query()
            ->withCount('documentos')
            ->when(trim($this->search) !== '', fn (Builder $q) => $q->where('nombre', 'like', '%'.trim($this->search).'%'))
            ->when($this->filtro === 'activas', fn (Builder $q) => $q->where('activo', true))
            ->when($this->filtro === 'inactivas', fn (Builder $q) => $q->where('activo', false))
            ->orderBy('nombre')
            ->get();

        $docsEnUso = $this->deactivatingCategoriaId
            ? (int) CategoriaDocumento::query()->find($this->deactivatingCategoriaId)?->documentos()->count()
            : 0;

        return view('livewire.categorias.index', compact('categorias', 'docsEnUso'));
    }

    private function resetForm(): void
    {
        $this->editingCategoriaId = null;
        $this->nombre = '';
        $this->descripcion = '';
        $this->resetValidation();
    }
}
