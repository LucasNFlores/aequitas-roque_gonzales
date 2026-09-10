<?php

namespace App\Livewire\Usuarios;

use App\Models\Servicio;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

/**
 * Componente Livewire para la gestión de usuarios internos (CU25-27, CU34).
 *
 * Alpine.js controla la visibilidad de modales; Livewire ejecuta
 * autorización, validación y persistencia en el servidor.
 */
class Index extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public bool $showServiciosModal = false;

    public ?int $editingUserId = null;

    public ?int $deletingUserId = null;

    public ?int $managingServiciosUserId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public ?string $dni = null;

    public ?string $telefono = null;

    public ?string $domicilio = null;

    public ?string $fecha_nacimiento = null;

    public ?string $fecha_ingreso = null;

    /** @var array<int,string> */
    public array $selectedRoles = [];

    /** @var array<int,int> */
    public array $selectedServicios = [];

    public string $search = '';

    public string $successMessage = '';

    public string $errorMessage = '';

    protected function rules(): array
    {
        $emailUnique = Rule::unique('users', 'email')->ignore($this->editingUserId);
        $dniUnique = Rule::unique('users', 'dni')->ignore($this->editingUserId);

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', $emailUnique],
            'password' => [$this->editingUserId ? 'nullable' : 'nullable', 'string', 'min:4', 'max:255'],
            'dni' => ['nullable', 'string', 'max:20', $dniUnique],
            'telefono' => ['nullable', 'string', 'max:30'],
            'domicilio' => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'fecha_ingreso' => ['nullable', 'date'],
            'selectedRoles' => ['nullable', 'array'],
            'selectedRoles.*' => ['string', 'exists:roles,name'],
            'selectedServicios' => ['nullable', 'array'],
            'selectedServicios.*' => ['integer', 'exists:servicios,id'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function createUser(): void
    {
        Gate::authorize('create', User::class);
        $this->resetForm();
        $this->showModal = true;
    }

    public function editUser(int $userId): void
    {
        $user = User::with(['roles', 'servicios'])->findOrFail($userId);
        Gate::authorize('update', $user);

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->dni = $user->dni;
        $this->telefono = $user->telefono;
        $this->domicilio = $user->domicilio;
        $this->fecha_nacimiento = $user->fecha_nacimiento?->format('Y-m-d');
        $this->fecha_ingreso = $user->fecha_ingreso?->format('Y-m-d');
        $this->selectedRoles = $user->roles->pluck('name')->all();
        $this->selectedServicios = $user->servicios->pluck('id')->all();
        $this->resetValidation();
        $this->showModal = true;
    }

    public function confirmDelete(int $userId): void
    {
        $user = User::findOrFail($userId);
        Gate::authorize('delete', $user);
        $this->deletingUserId = $user->id;
        $this->showDeleteModal = true;
    }

    public function manageServicios(int $userId): void
    {
        abort_unless(auth()->user()?->can('asignar_especialidad_servicio'), 403);
        $user = User::with(['servicios', 'roles'])->findOrFail($userId);
        abort_unless($user->hasRole('Profesional'), 422, 'Solo se pueden asignar servicios a usuarios con rol Profesional.');
        $this->managingServiciosUserId = $user->id;
        $this->selectedServicios = $user->servicios->pluck('id')->all();
        $this->resetValidation();
        $this->showServiciosModal = true;
    }

    public function saveUser(): void
    {
        $validated = $this->validate();

        // Validar que roles provengan de DB y que el actor pueda asignar Administrador.
        if (! empty($validated['selectedRoles']) && in_array('Administrador', $validated['selectedRoles'], true)) {
            abort_unless(auth()->user()?->hasRole('Administrador'), 403);
        }

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            Gate::authorize('update', $user);

            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'dni' => $validated['dni'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'domicilio' => $validated['domicilio'] ?? null,
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                'fecha_ingreso' => $validated['fecha_ingreso'] ?? null,
            ];
            if (filled($validated['password'] ?? null)) {
                $data['password'] = $validated['password'];
            }
            $user->update($data);

            // Roles: requiere permiso editar_roles
            if (array_key_exists('selectedRoles', $validated)) {
                Gate::authorize('manageRoles', $user);
                $user->syncRoles($validated['selectedRoles'] ?? []);
            }

            // Servicios: requiere permiso asignar_especialidad_servicio y rol Profesional
            if (array_key_exists('selectedServicios', $validated)) {
                abort_unless(auth()->user()?->can('asignar_especialidad_servicio'), 403);
                if (! empty($validated['selectedServicios'])) {
                    $user->refresh();
                    abort_unless($user->hasRole('Profesional'), 422, 'Solo se pueden asignar servicios a usuarios con rol Profesional.');
                }
                $user->servicios()->sync($validated['selectedServicios'] ?? []);
            }

            $this->successMessage = 'Usuario actualizado correctamente.';
        } else {
            Gate::authorize('create', User::class);

            $password = filled($validated['password'] ?? null) ? $validated['password'] : '1234';

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $password,
                'dni' => $validated['dni'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'domicilio' => $validated['domicilio'] ?? null,
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                'fecha_ingreso' => $validated['fecha_ingreso'] ?? null,
            ]);

            if (! empty($validated['selectedRoles'])) {
                Gate::authorize('manageRoles', $user);
                $user->syncRoles($validated['selectedRoles']);
            }

            if (! empty($validated['selectedServicios'])) {
                abort_unless(auth()->user()?->can('asignar_especialidad_servicio'), 403);
                $user->refresh();
                abort_unless($user->hasRole('Profesional'), 422, 'Solo se pueden asignar servicios a usuarios con rol Profesional.');
                $user->servicios()->sync($validated['selectedServicios']);
            }

            $this->successMessage = 'Usuario creado correctamente.';
        }

        $this->closeModal();
        $this->dispatch('usuario-guardado');
    }

    public function saveServicios(): void
    {
        abort_unless($this->managingServiciosUserId !== null, 404);
        abort_unless(auth()->user()?->can('asignar_especialidad_servicio'), 403);

        $user = User::findOrFail($this->managingServiciosUserId);
        abort_unless($user->hasRole('Profesional'), 422, 'Solo se pueden asignar servicios a usuarios con rol Profesional.');

        $this->validate([
            'selectedServicios' => ['nullable', 'array'],
            'selectedServicios.*' => ['integer', 'exists:servicios,id'],
        ]);

        // Evita duplicados: sync ya garantiza unicidad por clave primaria compuesta.
        $user->servicios()->sync($this->selectedServicios ?? []);

        $this->successMessage = 'Servicios asignados correctamente.';
        $this->closeServiciosModal();
        $this->dispatch('servicios-asignados');
    }

    public function deleteUser(): void
    {
        abort_unless($this->deletingUserId !== null, 404);
        $user = User::findOrFail($this->deletingUserId);
        Gate::authorize('delete', $user);

        // La baja es lógica por SoftDeletes; se conserva trazabilidad.
        $user->delete();

        $this->showDeleteModal = false;
        $this->deletingUserId = null;
        $this->successMessage = 'Usuario desactivado correctamente.';
        $this->dispatch('usuario-eliminado');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingUserId = null;
    }

    public function closeServiciosModal(): void
    {
        $this->showServiciosModal = false;
        $this->managingServiciosUserId = null;
        $this->resetValidation();
    }

    public function render(): View
    {
        $users = User::with(['roles', 'servicios'])
            ->when(trim($this->search) !== '', function (Builder $query): void {
                $term = '%'.trim($this->search).'%';
                $query->where(function (Builder $q) use ($term): void {
                    $q->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('dni', 'like', $term);
                });
            })
            ->latest()
            ->paginate(10);

        $roles = Role::orderBy('name')->get();
        $servicios = Servicio::orderBy('nombre')->get();

        return view('livewire.usuarios.index', compact('users', 'roles', 'servicios'));
    }

    private function resetForm(): void
    {
        $this->editingUserId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->dni = null;
        $this->telefono = null;
        $this->domicilio = null;
        $this->fecha_nacimiento = null;
        $this->fecha_ingreso = null;
        $this->selectedRoles = [];
        $this->selectedServicios = [];
        $this->resetValidation();
    }
}
