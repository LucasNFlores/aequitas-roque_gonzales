<?php

namespace App\Livewire\Servicios;

use App\Models\Servicio;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

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
        $this->resetPage();
    }

    public function createServicio(): void
    {
        $this->authorizeManagement();
        $this->resetForm();
        $this->showModal = true;
    }

    public function editServicio(int $servicioId): void
    {
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
        $this->authorizeManagement();
        Servicio::findOrFail($servicioId);
        $this->deletingServicioId = $servicioId;
        $this->showDeleteModal = true;
    }

    public function saveServicio(): void
    {
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
