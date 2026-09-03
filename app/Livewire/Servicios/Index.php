<?php

namespace App\Livewire\Servicios;

use App\Models\Servicio;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Componente Livewire de la pantalla de servicios.
 *
 * Livewire mantiene el estado y ejecuta las operaciones importantes en el
 * servidor. Alpine.js se ocupa solamente de mostrar u ocultar los modales en
 * el navegador para que la interfaz responda sin esperar una petición.
 */
class Index extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public ?int $editingServicioId = null;

    public ?int $deletingServicioId = null;

    public string $nombre = '';

    public string $costoServicio = '';

    public string $search = '';

    public string $successMessage = '';

    protected function rules(): array
    {
        // Estas reglas se ejecutan en el servidor, incluso si el navegador fue manipulado.
        return [
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('servicios', 'nombre')->ignore($this->editingServicioId),
            ],
            'costoServicio' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function updatingSearch(): void
    {
        // Si cambia el texto de búsqueda, volvemos a la primera página.
        $this->resetPage();
    }

    public function createServicio(): void
    {
        // El botón abre el modal inmediatamente con Alpine; este método prepara los datos.
        $this->authorizeManagement();
        $this->resetForm();
        $this->showModal = true;
    }

    public function editServicio(int $servicioId): void
    {
        // La ventana se muestra con Alpine y Livewire carga los datos del servicio.
        $this->authorizeManagement();

        $servicio = Servicio::findOrFail($servicioId);

        $this->editingServicioId = $servicio->id;
        $this->nombre = $servicio->nombre;
        $this->costoServicio = (string) $servicio->costo_servicio;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function confirmDelete(int $servicioId): void
    {
        // Primero validamos el permiso y que el servicio exista antes de confirmar la baja.
        $this->authorizeManagement();
        Servicio::findOrFail($servicioId);
        $this->deletingServicioId = $servicioId;
        $this->showDeleteModal = true;
    }

    public function saveServicio(): void
    {
        // Crear y editar comparten este método; la presencia del ID indica que es una edición.
        $this->authorizeManagement();
        $validated = $this->validate();

        $servicio = $this->editingServicioId
            ? Servicio::findOrFail($this->editingServicioId)
            : new Servicio;

        $servicio->fill([
            'nombre' => $validated['nombre'],
            'costo_servicio' => $validated['costoServicio'],
        ]);
        $servicio->save();

        $this->successMessage = $this->editingServicioId
            ? 'Servicio actualizado correctamente.'
            : 'Servicio creado correctamente.';

        $this->closeModal();
        $this->dispatch('servicio-guardado');
    }

    public function deleteServicio(): void
    {
        // La baja es lógica porque el modelo Servicio utiliza SoftDeletes.
        $this->authorizeManagement();

        Servicio::findOrFail($this->deletingServicioId)->delete();

        $this->showDeleteModal = false;
        $this->deletingServicioId = null;
        $this->successMessage = 'Servicio eliminado correctamente.';
        $this->dispatch('servicio-eliminado');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingServicioId = null;
    }

    public function render(): View
    {
        // Cada render consulta sólo los servicios activos y aplica la búsqueda/paginación.
        $servicios = Servicio::query()
            ->when($this->search !== '', function (Builder $query): void {
                $query->where('nombre', 'like', '%'.$this->search.'%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.servicios.index', compact('servicios'));
    }

    private function authorizeManagement(): void
    {
        abort_unless(auth()->user()?->can('gestionar_servicios'), 403);
    }

    private function resetForm(): void
    {
        $this->editingServicioId = null;
        $this->nombre = '';
        $this->costoServicio = '';
        $this->resetValidation();
    }
}
