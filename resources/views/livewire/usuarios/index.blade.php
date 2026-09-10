{{--
    Vista Livewire para gestión de usuarios internos.

    - Tailwind CSS: estilos y responsive
    - Alpine.js: controla visibilidad de modales (formOpen, deleteOpen, serviciosOpen)
    - Livewire: ejecuta autorización, validación y persistencia
--}}
<div
    x-data="{ formOpen: false, deleteOpen: false, serviciosOpen: false }"
    x-on:usuario-guardado.window="formOpen = false"
    x-on:usuario-eliminado.window="deleteOpen = false"
    x-on:servicios-asignados.window="serviciosOpen = false"
    x-on:keydown.escape.window="formOpen = false; deleteOpen = false; serviciosOpen = false"
>
@if($successMessage !== '')
    <div class="mb-6 rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700" role="alert">
        {{ $successMessage }}
    </div>
@endif
@if($errorMessage !== '')
    <div class="mb-6 rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700" role="alert">
        {{ $errorMessage }}
    </div>
@endif

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h3 class="text-lg font-medium text-gray-900">Listado de Usuarios</h3>
        <p class="mt-1 text-sm text-gray-500">Gestiona usuarios internos, sus roles y servicios asociados.</p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        @can('agregar_usuarios')
        <button type="button" @click="formOpen = true" wire:click="createUser" class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-700 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-900">
            Nuevo Usuario
        </button>
        @endcan
    </div>
</div>

<div class="mt-6">
    <label for="search" class="sr-only">Buscar usuario</label>
    <input id="search" type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, email o DNI..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-sm" />
</div>

<div class="mt-6 overflow-x-auto rounded-lg shadow-md">
    <table class="w-full text-left text-sm text-gray-500">
        <thead class="bg-gray-50 text-xs uppercase text-gray-700">
            <tr>
                <th scope="col" class="px-6 py-3">Nombre</th>
                <th scope="col" class="px-6 py-3">Email</th>
                <th scope="col" class="px-6 py-3">Roles</th>
                <th scope="col" class="px-6 py-3">Servicios</th>
                <th scope="col" class="px-6 py-3 text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr wire:key="user-{{ $user->id }}" class="border-b bg-white hover:bg-gray-50">
                    <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-900">
                        {{ $user->name }}
                        @if($user->dni)
                            <span class="block text-xs font-normal text-gray-500">DNI: {{ $user->dni }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">{{ $user->email }}</td>
                    <td class="px-6 py-4">
                        @forelse($user->roles as $role)
                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">{{ $role->name }}</span>
                        @empty
                            <span class="text-red-500 text-xs italic">Sin rol</span>
                        @endforelse
                    </td>
                    <td class="px-6 py-4">
                        @forelse($user->servicios as $servicio)
                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-0.5 rounded mr-1">{{ $servicio->nombre }}</span>
                        @empty
                            <span class="text-gray-400 text-xs italic">—</span>
                        @endforelse
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap justify-center gap-2">
                            @can('update', $user)
                            <button type="button" @click="formOpen = true" wire:click="editUser({{ $user->id }})" class="rounded-md bg-indigo-50 px-3 py-2 font-medium text-indigo-600 transition hover:text-indigo-900">Editar</button>
                            @endcan
                            @can('asignar_especialidad_servicio')
                                @if($user->hasRole('Profesional'))
                                <button type="button" @click="serviciosOpen = true" wire:click="manageServicios({{ $user->id }})" class="rounded-md bg-green-50 px-3 py-2 font-medium text-green-700 transition hover:text-green-900">Servicios</button>
                                @endif
                            @endcan
                            @can('manageRoles', $user)
                            <a href="{{ route('users.roles.edit', $user->id) }}" class="rounded-md bg-gray-50 px-3 py-2 font-medium text-gray-700 transition hover:text-gray-900">Roles</a>
                            @endcan
                            @can('delete', $user)
                            <button type="button" @click="deleteOpen = true" wire:click="confirmDelete({{ $user->id }})" class="rounded-md bg-red-50 px-3 py-2 font-medium text-red-600 transition hover:text-red-900">Desactivar</button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="bg-white">
                    <td colspan="5" class="px-6 py-4 text-center italic text-gray-500">No hay usuarios.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $users->links() }}
</div>

{{-- Modal crear/editar usuario --}}
<div x-cloak x-show="formOpen" x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="user-modal-title" wire:key="user-form-modal" @click.self="formOpen = false; $wire.closeModal()" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/60 px-4 py-6 backdrop-blur-sm sm:px-6">
    <div class="relative max-h-[calc(100vh-3rem)] w-full max-w-2xl overflow-y-auto rounded-xl bg-white shadow-2xl">
        <div wire:loading.flex wire:target="createUser,editUser" class="absolute inset-0 z-10 items-center justify-center rounded-xl bg-white/80">
            <span class="rounded-md bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow">Cargando...</span>
        </div>
        <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-6 py-5 sm:px-8">
            <div>
                <h2 id="user-modal-title" class="text-xl font-semibold text-gray-900">{{ $editingUserId ? 'Editar Usuario' : 'Nuevo Usuario' }}</h2>
                <p class="mt-1 text-sm text-gray-500">Los roles se traen desde la base de datos. El email debe ser único.</p>
            </div>
            <button type="button" @click="formOpen = false; $wire.closeModal()" class="rounded-md p-1 text-2xl leading-none text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" aria-label="Cerrar modal">&times;</button>
        </div>

        <form wire:submit="saveUser" class="space-y-6 px-6 py-6 sm:px-8">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="modal-name" class="block text-sm font-medium text-gray-700">Nombre *</label>
                    <input id="modal-name" type="text" wire:model.defer="name" class="mt-2 block w-full rounded-md border-gray-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('name') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="modal-email" class="block text-sm font-medium text-gray-700">Email *</label>
                    <input id="modal-email" type="email" wire:model.defer="email" class="mt-2 block w-full rounded-md border-gray-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('email') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="modal-password" class="block text-sm font-medium text-gray-700">Contraseña {{ $editingUserId ? '(dejar vacío para no cambiar)' : '(vacío = 1234)' }}</label>
                    <input id="modal-password" type="password" wire:model.defer="password" class="mt-2 block w-full rounded-md border-gray-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('password') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="modal-dni" class="block text-sm font-medium text-gray-700">DNI</label>
                    <input id="modal-dni" type="text" wire:model.defer="dni" class="mt-2 block w-full rounded-md border-gray-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('dni') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="modal-telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <input id="modal-telefono" type="text" wire:model.defer="telefono" class="mt-2 block w-full rounded-md border-gray-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('telefono') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="modal-domicilio" class="block text-sm font-medium text-gray-700">Domicilio</label>
                    <input id="modal-domicilio" type="text" wire:model.defer="domicilio" class="mt-2 block w-full rounded-md border-gray-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('domicilio') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="modal-fecha-nac" class="block text-sm font-medium text-gray-700">Fecha nacimiento</label>
                    <input id="modal-fecha-nac" type="date" wire:model.defer="fecha_nacimiento" class="mt-2 block w-full rounded-md border-gray-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('fecha_nacimiento') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="modal-fecha-ing" class="block text-sm font-medium text-gray-700">Fecha ingreso</label>
                    <input id="modal-fecha-ing" type="date" wire:model.defer="fecha_ingreso" class="mt-2 block w-full rounded-md border-gray-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('fecha_ingreso') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Roles (desde BD)</label>
                <div class="mt-2 space-y-2">
                    @foreach($roles as $role)
                        @if(auth()->user()->hasRole('Administrador') || $role->name !== 'Administrador')
                        <label class="flex items-center gap-2">
                            <input type="checkbox" value="{{ $role->name }}" wire:model.defer="selectedRoles" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                            <span class="text-sm text-gray-700">{{ $role->name }}</span>
                        </label>
                        @endif
                    @endforeach
                </div>
                @error('selectedRoles') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
                @error('selectedRoles.*') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            @can('asignar_especialidad_servicio')
            <div>
                <label class="block text-sm font-medium text-gray-700">Servicios / Especialidades</label>
                <div class="mt-2 max-h-32 overflow-y-auto rounded-md border border-gray-200 p-3">
                    @forelse($servicios as $servicio)
                        <label class="flex items-center gap-2 py-1">
                            <input type="checkbox" value="{{ $servicio->id }}" wire:model.defer="selectedServicios" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                            <span class="text-sm text-gray-700">{{ $servicio->nombre }} — ${{ number_format($servicio->costo_servicio, 2, ',', '.') }}</span>
                        </label>
                    @empty
                        <span class="text-sm italic text-gray-500">No hay servicios cargados.</span>
                    @endforelse
                </div>
                @error('selectedServicios') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
            @endcan

            <div class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-5 sm:flex-row sm:justify-end">
                <button type="button" @click="formOpen = false; $wire.closeModal()" class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">Cancelar</button>
                <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 sm:w-auto">
                    <span wire:loading.remove wire:target="saveUser">{{ $editingUserId ? 'Guardar cambios' : 'Crear usuario' }}</span>
                    <span wire:loading wire:target="saveUser">Guardando...</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal desactivar --}}
<div x-cloak x-show="deleteOpen" x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="delete-modal-title" wire:key="user-delete-modal" @click.self="deleteOpen = false; $wire.closeDeleteModal()" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/60 px-4 py-6 backdrop-blur-sm sm:px-6">
    <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl sm:p-8">
        <div class="flex items-start gap-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600" aria-hidden="true">!</div>
            <div>
                <h2 id="delete-modal-title" class="text-lg font-semibold text-gray-900">Desactivar usuario</h2>
                <p class="mt-2 text-sm leading-6 text-gray-600">El usuario se desactivará con baja lógica y se conservará su trazabilidad. Si posee procesos activos, la operación será rechazada.</p>
            </div>
        </div>
        <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button type="button" @click="deleteOpen = false; $wire.closeDeleteModal()" class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">Cancelar</button>
            <button type="button" wire:click="deleteUser" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-md border border-transparent bg-red-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 sm:w-auto">
                <span wire:loading.remove wire:target="deleteUser">Desactivar</span>
                <span wire:loading wire:target="deleteUser">Desactivando...</span>
            </button>
        </div>
    </div>
</div>

{{-- Modal asociar servicios --}}
<div x-cloak x-show="serviciosOpen" x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="servicios-modal-title" wire:key="servicios-modal" @click.self="serviciosOpen = false; $wire.closeServiciosModal()" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/60 px-4 py-6 backdrop-blur-sm sm:px-6">
    <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl sm:p-8">
        <h2 id="servicios-modal-title" class="text-lg font-semibold text-gray-900">Asociar servicios a profesional</h2>
        <p class="mt-1 text-sm text-gray-500">Selecciona los servicios que el profesional puede atender. Se evitan duplicados.</p>
        <form wire:submit="saveServicios" class="mt-6 space-y-4">
            <div class="max-h-64 overflow-y-auto rounded-md border border-gray-200 p-3">
                @forelse($servicios as $servicio)
                    <label class="flex items-center gap-2 py-1">
                        <input type="checkbox" value="{{ $servicio->id }}" wire:model.defer="selectedServicios" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                        <span class="text-sm text-gray-700">{{ $servicio->nombre }}</span>
                    </label>
                @empty
                    <span class="text-sm italic text-gray-500">No hay servicios disponibles.</span>
                @endforelse
            </div>
            @error('selectedServicios') <span class="block text-sm text-red-600">{{ $message }}</span> @enderror
            <div class="flex flex-col-reverse gap-3 pt-4 sm:flex-row sm:justify-end">
                <button type="button" @click="serviciosOpen = false; $wire.closeServiciosModal()" class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50 sm:w-auto">Cancelar</button>
                <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white sm:w-auto">
                    <span wire:loading.remove wire:target="saveServicios">Guardar</span>
                    <span wire:loading wire:target="saveServicios">Guardando...</span>
                </button>
            </div>
        </form>
    </div>
</div>
</div>
