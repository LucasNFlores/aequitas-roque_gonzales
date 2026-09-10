<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UpdateUserRolesRequest;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function editRoles(User $user): View
    {
        $this->authorize('manageRoles', $user);

        $roles = Role::query()->orderBy('name')->get();

        return view('users.roles', compact('user', 'roles'));
    }

    public function updateRoles(UpdateUserRolesRequest $request, User $user): RedirectResponse
    {
        $user->syncRoles($request->validated('roles') ?? []);

        return redirect()->back()->with('success', 'Roles actualizados correctamente.');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $search = trim((string) $request->query('search', ''));

        $users = User::with(['roles', 'servicios'])
            ->when($search !== '', function ($query) use ($search): void {
                $term = '%'.$search.'%';
                $query->where(function ($q) use ($term): void {
                    $q->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('dni', 'like', $term);
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        $roles = Role::query()->orderBy('name')->get();
        $servicios = Servicio::orderBy('nombre')->get();

        return view('users.create', compact('roles', 'servicios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Validar rol Administrador solo para administradores
        if (! empty($validated['roles']) && in_array('Administrador', $validated['roles'], true)) {
            abort_unless(auth()->user()?->hasRole('Administrador'), 403);
        }

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

        if (! empty($validated['roles'])) {
            $this->authorize('manageRoles', $user);
            $user->syncRoles($validated['roles']);
        }

        if (! empty($validated['servicios'])) {
            abort_unless(auth()->user()?->can('asignar_especialidad_servicio'), 403);
            $user->refresh();
            abort_unless($user->hasRole('Profesional'), 422, 'Solo se pueden asignar servicios a usuarios con rol Profesional.');
            $user->servicios()->sync($validated['servicios']);
        }

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        $this->authorize('view', $user);

        $user->load(['roles', 'servicios']);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $roles = Role::query()->orderBy('name')->get();
        $servicios = Servicio::orderBy('nombre')->get();

        return view('users.edit', compact('user', 'roles', 'servicios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

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

        if (array_key_exists('roles', $validated) && ! empty($validated['roles']) && in_array('Administrador', $validated['roles'], true)) {
            abort_unless(auth()->user()?->hasRole('Administrador'), 403);
        }

        $user->update($data);

        if (array_key_exists('roles', $validated)) {
            $this->authorize('manageRoles', $user);
            $user->syncRoles($validated['roles'] ?? []);
        }

        if (array_key_exists('servicios', $validated)) {
            abort_unless(auth()->user()?->can('asignar_especialidad_servicio'), 403);
            if (! empty($validated['servicios'])) {
                $user->refresh();
                abort_unless($user->hasRole('Profesional'), 422, 'Solo se pueden asignar servicios a usuarios con rol Profesional.');
            }
            $user->servicios()->sync($validated['servicios'] ?? []);
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario desactivado correctamente.');
    }

    public function updateServicios(Request $request, User $user): RedirectResponse
    {
        abort_unless(auth()->user()?->can('asignar_especialidad_servicio'), 403);

        // CU34: sólo profesionales pueden tener servicios/especialidades asociadas
        abort_unless($user->hasRole('Profesional'), 422, 'Solo se pueden asignar servicios a usuarios con rol Profesional.');

        $validated = $request->validate([
            'servicios' => ['nullable', 'array'],
            'servicios.*' => ['integer', 'exists:servicios,id'],
        ]);

        $user->servicios()->sync($validated['servicios'] ?? []);

        return redirect()->back()->with('success', 'Servicios asignados correctamente.');
    }
}
