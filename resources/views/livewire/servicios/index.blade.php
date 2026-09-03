<div
    x-data="{ formOpen: false, deleteOpen: false }"
    x-on:servicio-guardado.window="formOpen = false"
    x-on:servicio-eliminado.window="deleteOpen = false"
    x-on:keydown.escape.window="formOpen = false; deleteOpen = false"
>
@if($successMessage !== '')
    <div class="mb-6 rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700" role="alert">
        {{ $successMessage }}
    </div>
@endif

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h3 class="text-lg font-medium text-gray-900">Listado de Servicios</h3>
        <p class="mt-1 text-sm text-gray-500">Gestiona los servicios sin salir de esta pantalla.</p>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('servicios-viejo.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
            Ver versión clásica
        </a>
        <button type="button" @click="formOpen = true" wire:click="createServicio" class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-700 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-indigo-900">
            Nuevo Servicio
        </button>
    </div>
</div>

<div class="mt-6">
    <label for="search" class="sr-only">Buscar servicio</label>
    <input id="search" type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-sm" />
</div>

<div class="mt-6 overflow-x-auto rounded-lg shadow-md">
    <table class="w-full text-left text-sm text-gray-500">
        <thead class="bg-gray-50 text-xs uppercase text-gray-700">
            <tr>
                <th scope="col" class="px-6 py-3">Nombre</th>
                <th scope="col" class="px-6 py-3">Costo</th>
                <th scope="col" class="px-6 py-3 text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($servicios as $servicio)
                <tr wire:key="servicio-{{ $servicio->id }}" class="border-b bg-white hover:bg-gray-50">
                    <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-900">
                        {{ $servicio->nombre }}
                    </td>
                    <td class="px-6 py-4">
                        ${{ number_format($servicio->costo_servicio, 2, ',', '.') }}
                    </td>
                    <td class="space-x-2 px-6 py-4 text-center">
                        <button type="button" @click="formOpen = true" wire:click="editServicio({{ $servicio->id }})" class="rounded-md bg-indigo-50 px-3 py-2 font-medium text-indigo-600 transition hover:text-indigo-900">
                            Editar
                        </button>
                        <button type="button" @click="deleteOpen = true" wire:click="confirmDelete({{ $servicio->id }})" class="rounded-md bg-red-50 px-3 py-2 font-medium text-red-600 transition hover:text-red-900">
                            Eliminar
                        </button>
                    </td>
                </tr>
            @empty
                <tr class="bg-white">
                    <td colspan="3" class="px-6 py-4 text-center italic text-gray-500">
                        No hay servicios cargados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $servicios->links() }}
</div>

<div x-cloak x-show="formOpen" x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="servicio-modal-title" wire:key="servicio-form-modal" @click.self="formOpen = false; $wire.closeModal()" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/60 px-4 py-6 backdrop-blur-sm sm:px-6">
        <div class="relative max-h-[calc(100vh-3rem)] w-full max-w-xl overflow-y-auto rounded-xl bg-white shadow-2xl">
            <div wire:loading.flex wire:target="createServicio,editServicio" class="absolute inset-0 z-10 items-center justify-center rounded-xl bg-white/80">
                <span class="rounded-md bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow">Cargando...</span>
            </div>
            <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-6 py-5 sm:px-8">
                <div>
                    <h2 id="servicio-modal-title" class="text-xl font-semibold text-gray-900">
                        {{ $editingServicioId ? 'Editar Servicio' : 'Nuevo Servicio' }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">Completa los datos del servicio.</p>
                </div>
                <button type="button" @click="formOpen = false; $wire.closeModal()" class="rounded-md p-1 text-2xl leading-none text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" aria-label="Cerrar modal">
                    &times;
                </button>
            </div>

            <form wire:submit="saveServicio" class="space-y-6 px-6 py-6 sm:px-8">
                <div>
                    <label for="modal-nombre" class="block text-sm font-medium text-gray-700">Nombre del servicio</label>
                    <input id="modal-nombre" type="text" wire:model.defer="nombre" class="mt-2 block w-full rounded-md border-gray-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" autofocus />
                    @error('nombre')
                        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="modal-costo-servicio" class="block text-sm font-medium text-gray-700">Costo del servicio</label>
                    <input id="modal-costo-servicio" type="number" step="0.01" min="0" wire:model.defer="costoServicio" class="mt-2 block w-full rounded-md border-gray-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('costoServicio')
                        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" @click="formOpen = false; $wire.closeModal()" class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
                        Cancelar
                    </button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 sm:w-auto">
                        <span wire:loading.remove wire:target="saveServicio">{{ $editingServicioId ? 'Guardar cambios' : 'Crear servicio' }}</span>
                        <span wire:loading wire:target="saveServicio">Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

<div x-cloak x-show="deleteOpen" x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="delete-modal-title" wire:key="servicio-delete-modal" @click.self="deleteOpen = false; $wire.closeDeleteModal()" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/60 px-4 py-6 backdrop-blur-sm sm:px-6">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl sm:p-8">
            <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600" aria-hidden="true">!</div>
                <div>
                    <h2 id="delete-modal-title" class="text-lg font-semibold text-gray-900">Eliminar servicio</h2>
                    <p class="mt-2 text-sm leading-6 text-gray-600">¿Seguro que deseas eliminar este servicio?</p>
                </div>
            </div>

            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" @click="deleteOpen = false; $wire.closeDeleteModal()" class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
                    Cancelar
                </button>
                <button type="button" wire:click="deleteServicio" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-md border border-transparent bg-red-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 sm:w-auto">
                    <span wire:loading.remove wire:target="deleteServicio">Eliminar</span>
                    <span wire:loading wire:target="deleteServicio">Eliminando...</span>
                </button>
            </div>
        </div>
    </div>
</div>
