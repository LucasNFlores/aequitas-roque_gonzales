<?php

namespace App\Livewire\Profesionales;

use App\Models\Servicio;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Servicios extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $selectedProfessionalId = null;

    /** @var array<int,int> */
    public array $selectedServicios = [];

    public string $successMessage = '';

    public function mount(): void
    {
        Gate::authorize('asignar_especialidad_servicio');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->successMessage = '';
    }

    public function openServices(int $professionalId): void
    {
        Gate::authorize('asignar_especialidad_servicio');

        $professional = User::query()
            ->role('Profesional')
            ->with('servicios')
            ->findOrFail($professionalId);

        $this->selectedProfessionalId = $professional->id;
        $this->selectedServicios = $professional->servicios->modelKeys();
        $this->resetValidation();
        $this->showModal = true;
    }

    public function saveServices(): void
    {
        Gate::authorize('asignar_especialidad_servicio');

        $validated = $this->validate([
            'selectedProfessionalId' => ['required', 'integer'],
            'selectedServicios' => ['array'],
            'selectedServicios.*' => [
                'integer',
                Rule::exists('servicios', 'id')->whereNull('deleted_at'),
            ],
        ]);

        $professional = User::query()
            ->role('Profesional')
            ->findOrFail($validated['selectedProfessionalId']);

        DB::transaction(function () use ($professional, $validated): void {
            $professional->servicios()->sync($validated['selectedServicios'] ?? []);
        });

        $this->showModal = false;
        $this->selectedProfessionalId = null;
        $this->selectedServicios = [];
        $this->successMessage = 'Servicios actualizados correctamente.';
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedProfessionalId = null;
        $this->selectedServicios = [];
        $this->resetValidation();
    }

    public function render(): View
    {
        Gate::authorize('asignar_especialidad_servicio');

        $search = trim($this->search);

        $profesionales = User::query()
            ->role('Profesional')
            ->with('servicios')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.profesionales.servicios', [
            'profesionales' => $profesionales,
            'servicios' => Servicio::query()->orderBy('nombre')->get(),
        ]);
    }
}
