<?php

namespace App\Livewire\Documentos;

use App\Models\CategoriaDocumento;
use App\Models\Documento;
use App\Models\Proceso;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithFileUploads, WithPagination;

    public Proceso $proceso;

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public ?int $editingDocumentoId = null;

    public ?int $deletingDocumentoId = null;

    public string $nombre = '';

    public ?int $categoriaId = null;

    public ?TemporaryUploadedFile $archivo = null;

    public string $search = '';

    public string $successMessage = '';

    public function mount(Proceso $proceso): void
    {
        $this->proceso = $proceso;

        Gate::authorize('view', $proceso);
        Gate::authorize('viewAny', Documento::class);
    }

    public function createDocumento(): void
    {
        Gate::authorize('create', Documento::class);
        $this->resetForm();
        $this->showModal = true;
    }

    public function editDocumento(int $documentoId): void
    {
        $documento = $this->findDocumento($documentoId);
        Gate::authorize('update', $documento);

        $this->editingDocumentoId = $documento->id;
        $this->nombre = $documento->nombre;
        $this->categoriaId = $documento->categoria_id;
        $this->archivo = null;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function confirmDelete(int $documentoId): void
    {
        $documento = $this->findDocumento($documentoId);
        Gate::authorize('delete', $documento);

        $this->deletingDocumentoId = $documento->id;
        $this->showDeleteModal = true;
    }

    public function saveDocumento(): void
    {
        $isEditing = $this->editingDocumentoId !== null;
        $documento = $isEditing ? $this->findDocumento($this->editingDocumentoId) : null;

        Gate::authorize($isEditing ? 'update' : 'create', $isEditing ? $documento : Documento::class);

        $rules = [
            'nombre' => ['required', 'string', 'max:255'],
            'categoriaId' => ['required', 'integer', Rule::exists('categorias_documento', 'id')->where('activo', true)],
            'archivo' => $isEditing
                ? ['nullable', 'file', 'mimes:pdf', 'max:20480']
                : ['required', 'file', 'mimes:pdf', 'max:20480'],
        ];

        $messages = [
            'nombre.required' => 'El nombre es obligatorio.',
            'categoriaId.required' => 'La categoría es obligatoria.',
            'categoriaId.exists' => 'La categoría seleccionada no está disponible para nuevas cargas.',
            'archivo.required' => 'Selecciona el PDF del documento.',
            'archivo.mimes' => 'El documento debe estar en formato PDF.',
            'archivo.max' => 'El PDF no puede superar los 20 MB.',
        ];

        $validated = $this->validate($rules, $messages);

        $categoria = CategoriaDocumento::query()->findOrFail($validated['categoriaId']);
        abort_unless($categoria->activo, 422, 'La categoría seleccionada no está disponible.');

        $tipoDocumento = $categoria->nombre;

        if ($isEditing) {
            $data = [
                'nombre' => $validated['nombre'],
                'categoria_id' => $categoria->id,
                'tipo_documento' => $tipoDocumento,
            ];

            if ($this->archivo) {
                $path = $this->archivo->storeAs('documentos/'.$this->proceso->id, Str::uuid().'.pdf', 'local');
                $data['archivo_path'] = $path;
            }

            $documento->update($data);
            $this->successMessage = 'Documento actualizado correctamente.';
        } else {
            $path = $validated['archivo']->storeAs('documentos/'.$this->proceso->id, Str::uuid().'.pdf', 'local');

            try {
                DB::transaction(function () use ($validated, $categoria, $tipoDocumento, $path): void {
                    Documento::query()->create([
                        'proceso_id' => $this->proceso->id,
                        'categoria_id' => $categoria->id,
                        'archivo_path' => $path,
                        'tipo_documento' => $tipoDocumento,
                        'nombre' => $validated['nombre'],
                    ]);
                });
            } catch (\Throwable $exception) {
                Storage::disk('local')->delete($path);
                report($exception);
                $this->addError('archivo', 'No pudimos guardar el documento. Intentalo nuevamente.');

                return;
            }

            $this->successMessage = 'Documento cargado correctamente.';
        }

        $this->closeModal();
        $this->dispatch('documento-guardado');
    }

    public function deleteDocumento(): void
    {
        abort_unless($this->deletingDocumentoId !== null, 404);
        $documento = $this->findDocumento($this->deletingDocumentoId);
        Gate::authorize('delete', $documento);

        $documento->delete();

        $this->successMessage = 'Documento eliminado. Se conserva auditoría y archivo físico.';
        $this->closeDeleteModal();
        $this->dispatch('documento-eliminado');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingDocumentoId = null;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->successMessage = '';
    }

    public function render(): View
    {
        Gate::authorize('view', $this->proceso);

        $categorias = CategoriaDocumento::paraCargaNueva();

        $documentos = Documento::query()
            ->where('proceso_id', $this->proceso->id)
            ->with('categoria')
            ->when(trim($this->search) !== '', function ($query) {
                $search = trim($this->search);
                $query->where(function ($q) use ($search): void {
                    $q->where('nombre', 'like', '%'.$search.'%')
                        ->orWhere('tipo_documento', 'like', '%'.$search.'%')
                        ->orWhereHas('categoria', fn ($cq) => $cq->where('nombre', 'like', '%'.$search.'%'));
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.documentos.index', compact('documentos', 'categorias'));
    }

    private function findDocumento(int $documentoId): Documento
    {
        return Documento::query()
            ->where('proceso_id', $this->proceso->id)
            ->findOrFail($documentoId);
    }

    private function resetForm(): void
    {
        $this->editingDocumentoId = null;
        $this->nombre = '';
        $this->categoriaId = null;
        $this->archivo = null;
        $this->resetValidation();
    }
}
