<div
    x-data="{ formOpen: false, deactivateOpen: false }"
    x-on:estado-guardado.window="formOpen = false"
    x-on:estado-desactivado.window="deactivateOpen = false"
    x-on:keydown.escape.window="formOpen = false; deactivateOpen = false"
>
    @if($successMessage !== '')
        <div class="mb-6 rounded-control border border-green-200 bg-green-50 px-4 py-3 text-sm text-positive" role="alert">
            {{ $successMessage }}
        </div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h3 class="text-lg font-medium text-ink">Catálogo de estados</h3>
            <p class="mt-1 text-sm text-muted">Define los estados disponibles y su orden para los procesos.</p>
        </div>
        <button type="button" @click="formOpen = true" wire:click="createEstado" class="inline-flex items-center justify-center rounded-control border border-transparent bg-aequitas-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-aequitas-800 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">
            Nuevo estado
        </button>
    </div>

    <div class="mt-6">
        <label for="state-search" class="sr-only">Buscar estado</label>
        <input id="state-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre..." class="block w-full rounded-control border-line shadow-sm focus:border-gold-600 focus:ring-gold-600 sm:max-w-sm" />
    </div>

    <div class="mt-6 overflow-x-auto rounded-card border border-line shadow-sm">
        <table class="w-full min-w-[760px] text-left text-sm text-muted">
            <thead class="bg-canvas text-xs uppercase tracking-wide text-muted">
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
                    <tr wire:key="estado-{{ $estado->id }}" class="border-b bg-white hover:bg-canvas">
                        <td class="px-6 py-4 font-semibold text-ink">{{ $estado->posicion }}</td>
                        <td class="px-6 py-4 font-medium text-ink">{{ $estado->nombre }}</td>
                        <td class="px-6 py-4 font-mono text-xs text-muted">{{ $estado->slug }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $estado->activo ? 'bg-green-100 text-positive' : 'bg-canvas text-muted' }}">
                                {{ $estado->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap justify-center gap-2">
                                @if($estado->activo)
                                    <button type="button" wire:click="moveStateUp({{ $estado->id }})" class="rounded-control bg-canvas px-3 py-2 font-medium text-muted transition hover:bg-aequitas-50 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2" aria-label="Mover {{ $estado->nombre }} hacia arriba">↑</button>
                                    <button type="button" wire:click="moveStateDown({{ $estado->id }})" class="rounded-control bg-canvas px-3 py-2 font-medium text-muted transition hover:bg-aequitas-50 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2" aria-label="Mover {{ $estado->nombre }} hacia abajo">↓</button>
                                @endif
                                <button type="button" wire:click="editEstado({{ $estado->id }})" @click="formOpen = true" class="rounded-control bg-aequitas-50 px-3 py-2 font-medium text-aequitas-900 transition hover:bg-aequitas-100 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">Editar</button>
                                @if($estado->activo)
                    <button type="button" wire:click="confirmDeactivate({{ $estado->id }})" @click="deactivateOpen = true" class="rounded-control bg-red-50 px-3 py-2 font-medium text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">Eliminar</button>
                                @else
                                    <button type="button" wire:click="activateEstado({{ $estado->id }})" class="rounded-control bg-green-50 px-3 py-2 font-medium text-positive transition hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">Activar</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white">
                        <td colspan="5" class="px-6 py-10 text-center italic text-muted">No se encontraron estados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div x-cloak x-show="formOpen" x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="state-form-title" @click.self="formOpen = false; $wire.closeModal()" class="fixed inset-0 z-50 flex items-center justify-center bg-aequitas-900/60 px-4 py-6 backdrop-blur-sm">
        <div class="w-full max-w-xl rounded-panel bg-white shadow-2xl">
            <div class="flex items-start justify-between gap-4 border-b border-line px-6 py-5 sm:px-8">
                <div>
                    <h2 id="state-form-title" class="text-xl font-semibold text-ink">{{ $editingEstadoId ? 'Editar estado' : 'Nuevo estado' }}</h2>
                    <p class="mt-1 text-sm text-muted">La clave interna se genera al crear el estado.</p>
                </div>
                <button type="button" @click="formOpen = false; $wire.closeModal()" class="rounded-control p-1 text-2xl leading-none text-muted transition hover:bg-canvas hover:text-muted focus:outline-none focus:ring-2 focus:ring-gold-600" aria-label="Cerrar formulario">&times;</button>
            </div>

            <form wire:submit="saveEstado" class="space-y-6 px-6 py-6 sm:px-8">
                <div>
                    <label for="state-name" class="block text-sm font-medium text-muted">Nombre del estado</label>
                    <input id="state-name" type="text" wire:model.defer="nombre" placeholder="Ej. En revisión" class="mt-2 block w-full rounded-control border-line shadow-sm focus:border-gold-600 focus:ring-gold-600" autofocus />
                    @error('nombre')
                        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-line pt-5 sm:flex-row sm:justify-end">
                    <button type="button" @click="formOpen = false; $wire.closeModal()" class="inline-flex w-full items-center justify-center rounded-control border border-line bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-muted shadow-sm transition hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2 sm:w-auto">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-control border border-transparent bg-aequitas-900 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white shadow-sm transition hover:bg-aequitas-800 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2 disabled:opacity-50 sm:w-auto">
                        <span wire:loading.remove wire:target="saveEstado">Guardar estado</span>
                        <span wire:loading wire:target="saveEstado">Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-cloak x-show="deactivateOpen" x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="deactivate-state-title" @click.self="deactivateOpen = false; $wire.closeDeactivateModal()" class="fixed inset-0 z-50 flex items-center justify-center bg-aequitas-900/60 px-4 py-6 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-panel bg-white p-6 shadow-2xl sm:p-8">
            <h2 id="deactivate-state-title" class="text-lg font-semibold text-ink">Eliminar estado</h2>
            <p class="mt-2 text-sm leading-6 text-muted">El estado se quitará de las opciones disponibles y se conservará como inactivo para mantener el historial. Los procesos que lo utilicen impiden la eliminación.</p>
            @error('deactivate')
                <p class="mt-4 rounded-control bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>
            @enderror
            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" @click="deactivateOpen = false; $wire.closeDeactivateModal()" class="inline-flex w-full items-center justify-center rounded-control border border-line bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-muted shadow-sm transition hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2 sm:w-auto">Cancelar</button>
                <button type="button" wire:click="deactivateEstado" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-control border border-transparent bg-red-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 sm:w-auto">
                    <span wire:loading.remove wire:target="deactivateEstado">Eliminar</span>
                    <span wire:loading wire:target="deactivateEstado">Eliminando...</span>
                </button>
            </div>
        </div>
    </div>
</div>
