<div
    x-data="{ formOpen: false, deactivateOpen: false }"
    x-on:estado-guardado.window="formOpen = false"
    x-on:estado-desactivado.window="deactivateOpen = false"
    x-on:keydown.escape.window="formOpen = false; deactivateOpen = false"
>
    @if($successMessage !== '')
        <div class="mb-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="alert">
            {{ $successMessage }}
        </div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h3 class="text-lg font-medium text-gray-900">Catálogo de estados</h3>
            <p class="mt-1 text-sm text-gray-500">Define los estados disponibles y su orden para los procesos.</p>
        </div>
        <button type="button" @click="formOpen = true" wire:click="createEstado" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Nuevo estado
        </button>
    </div>

    <div class="mt-6">
        <label for="state-search" class="sr-only">Buscar estado</label>
        <input id="state-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-sm" />
    </div>

    <div class="mt-6 overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
        <table class="w-full min-w-[760px] text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3">Orden</th>
                    <th scope="col" class="px-6 py-3">Estado</th>
                    <th scope="col" class="px-6 py-3">Clave interna</th>
                    <th scope="col" class="px-6 py-3">Situación</th>
                    <th scope="col" class="px-6 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($estados as $estado)
                    <tr wire:key="estado-{{ $estado->id }}" class="border-b bg-white hover:bg-gray-50">
                        <td class="px-6 py-4 font-semibold text-gray-900">{{ $estado->posicion }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $estado->nombre }}</td>
                        <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $estado->slug }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $estado->activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ $estado->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap justify-center gap-2">
                                @if($estado->activo)
                                    <button type="button" wire:click="moveStateUp({{ $estado->id }})" class="rounded-md bg-gray-100 px-3 py-2 font-medium text-gray-700 transition hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2" aria-label="Mover {{ $estado->nombre }} hacia arriba">↑</button>
                                    <button type="button" wire:click="moveStateDown({{ $estado->id }})" class="rounded-md bg-gray-100 px-3 py-2 font-medium text-gray-700 transition hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2" aria-label="Mover {{ $estado->nombre }} hacia abajo">↓</button>
                                @endif
                                <button type="button" wire:click="editEstado({{ $estado->id }})" @click="formOpen = true" class="rounded-md bg-indigo-50 px-3 py-2 font-medium text-indigo-700 transition hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Editar</button>
                                @if($estado->activo)
                    <button type="button" wire:click="confirmDeactivate({{ $estado->id }})" @click="deactivateOpen = true" class="rounded-md bg-red-50 px-3 py-2 font-medium text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">Eliminar</button>
                                @else
                                    <button type="button" wire:click="activateEstado({{ $estado->id }})" class="rounded-md bg-green-50 px-3 py-2 font-medium text-green-700 transition hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">Activar</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white">
                        <td colspan="5" class="px-6 py-10 text-center italic text-gray-500">No se encontraron estados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div x-cloak x-show="formOpen" x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="state-form-title" @click.self="formOpen = false; $wire.closeModal()" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 px-4 py-6 backdrop-blur-sm">
        <div class="w-full max-w-xl rounded-xl bg-white shadow-2xl">
            <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-6 py-5 sm:px-8">
                <div>
                    <h2 id="state-form-title" class="text-xl font-semibold text-gray-900">{{ $editingEstadoId ? 'Editar estado' : 'Nuevo estado' }}</h2>
                    <p class="mt-1 text-sm text-gray-500">La clave interna se genera al crear el estado.</p>
                </div>
                <button type="button" @click="formOpen = false; $wire.closeModal()" class="rounded-md p-1 text-2xl leading-none text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500" aria-label="Cerrar formulario">&times;</button>
            </div>

            <form wire:submit="saveEstado" class="space-y-6 px-6 py-6 sm:px-8">
                <div>
                    <label for="state-name" class="block text-sm font-medium text-gray-700">Nombre del estado</label>
                    <input id="state-name" type="text" wire:model.defer="nombre" placeholder="Ej. En revisión" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" autofocus />
                    @error('nombre')
                        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" @click="formOpen = false; $wire.closeModal()" class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 sm:w-auto">
                        <span wire:loading.remove wire:target="saveEstado">Guardar estado</span>
                        <span wire:loading wire:target="saveEstado">Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-cloak x-show="deactivateOpen" x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="deactivate-state-title" @click.self="deactivateOpen = false; $wire.closeDeactivateModal()" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 px-4 py-6 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl sm:p-8">
            <h2 id="deactivate-state-title" class="text-lg font-semibold text-gray-900">Eliminar estado</h2>
            <p class="mt-2 text-sm leading-6 text-gray-600">El estado se quitará de las opciones disponibles y se conservará como inactivo para mantener el historial. Los procesos que lo utilicen impiden la eliminación.</p>
            @error('deactivate')
                <p class="mt-4 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>
            @enderror
            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" @click="deactivateOpen = false; $wire.closeDeactivateModal()" class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">Cancelar</button>
                <button type="button" wire:click="deactivateEstado" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-md border border-transparent bg-red-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 sm:w-auto">
                    <span wire:loading.remove wire:target="deactivateEstado">Eliminar</span>
                    <span wire:loading wire:target="deactivateEstado">Eliminando...</span>
                </button>
            </div>
        </div>
    </div>
</div>
