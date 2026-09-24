<div
    x-data="{ formOpen: false, deactivateOpen: false }"
    x-on:categoria-guardada.window="formOpen = false"
    x-on:categoria-desactivada.window="deactivateOpen = false"
    x-on:keydown.escape.window="formOpen = false; deactivateOpen = false"
>
    @if($successMessage !== '')
        <div class="mb-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="alert">
            {{ $successMessage }}
        </div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h3 class="text-lg font-medium text-gray-900">Categorías de documentos</h3>
            <p class="mt-1 text-sm text-gray-500">Clasifican la documentación de los legajos. La baja es lógica: se conserva el historial.</p>
        </div>
        <button type="button" @click="formOpen = true" wire:click="createCategoria" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Nueva categoría
        </button>
    </div>

    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-sm" />
        <select wire:model.live="filtro" class="block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-xs">
            <option value="todas">Todas</option>
            <option value="activas">Activas</option>
            <option value="inactivas">Inactivas</option>
        </select>
    </div>

    <div class="mt-6 overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
        <table class="w-full min-w-[760px] text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-700">
                <tr>
                    <th class="px-6 py-3">Nombre</th>
                    <th class="px-6 py-3">Descripción</th>
                    <th class="px-6 py-3">Situación</th>
                    <th class="px-6 py-3">Docs en uso</th>
                    <th class="px-6 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categorias as $categoria)
                    <tr wire:key="categoria-{{ $categoria->id }}" class="border-b bg-white hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $categoria->nombre }}</td>
                        <td class="px-6 py-4">{{ $categoria->descripcion ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $categoria->activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ $categoria->activo ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">{{ $categoria->documentos_count }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap justify-center gap-2">
                                <button type="button" wire:click="editCategoria({{ $categoria->id }})" @click="formOpen = true" class="rounded-md bg-indigo-50 px-3 py-2 font-medium text-indigo-700 transition hover:bg-indigo-100">Editar</button>
                                @if($categoria->activo)
                                    <button type="button" wire:click="confirmDeactivate({{ $categoria->id }})" @click="deactivateOpen = true" class="rounded-md bg-red-50 px-3 py-2 font-medium text-red-700 transition hover:bg-red-100">Desactivar</button>
                                @else
                                    <button type="button" wire:click="activateCategoria({{ $categoria->id }})" class="rounded-md bg-green-50 px-3 py-2 font-medium text-green-700 transition hover:bg-green-100">Reactivar</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white"><td colspan="5" class="px-6 py-10 text-center italic text-gray-500">No se encontraron categorías.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div x-cloak x-show="formOpen" x-transition.opacity role="dialog" aria-modal="true" @click.self="formOpen = false; $wire.closeModal()" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 px-4 py-6 backdrop-blur-sm">
        <div class="w-full max-w-xl rounded-xl bg-white shadow-2xl">
            <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-6 py-5 sm:px-8">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $editingCategoriaId ? 'Editar categoría' : 'Nueva categoría' }}</h2>
                    <p class="mt-1 text-sm text-gray-500">El nombre debe ser único. La edición no afecta el historial.</p>
                </div>
                <button type="button" @click="formOpen = false; $wire.closeModal()" class="rounded-md p-1 text-2xl leading-none text-gray-400 hover:bg-gray-100" aria-label="Cerrar">&times;</button>
            </div>
            <form wire:submit="saveCategoria" class="space-y-6 px-6 py-6 sm:px-8">
                <div>
                    <label for="cat-nombre" class="block text-sm font-medium text-gray-700">Nombre *</label>
                    <input id="cat-nombre" type="text" wire:model.defer="nombre" maxlength="100" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('nombre')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="cat-desc" class="block text-sm font-medium text-gray-700">Descripción</label>
                    <textarea id="cat-desc" wire:model.defer="descripcion" maxlength="255" rows="3" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    @error('descripcion')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
                </div>
                <div class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" @click="formOpen = false; $wire.closeModal()" class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-gray-700 sm:w-auto">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-md bg-indigo-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white sm:w-auto">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div x-cloak x-show="deactivateOpen" x-transition.opacity role="dialog" aria-modal="true" @click.self="deactivateOpen = false; $wire.closeDeactivateModal()" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 px-4 py-6 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl sm:p-8">
            <h2 class="text-lg font-semibold text-gray-900">Desactivar categoría</h2>
            <p class="mt-2 text-sm leading-6 text-gray-600">
                @if($docsEnUso > 0)
                    Tiene {{ $docsEnUso }} documentos históricos que se conservarán. Dejará de ofrecerse para nuevas cargas.
                @else
                    Dejará de ofrecerse para nuevas cargas.
                @endif
            </p>
            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" @click="deactivateOpen = false; $wire.closeDeactivateModal()" class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-gray-700 sm:w-auto">Cancelar</button>
                <button type="button" wire:click="deactivateCategoria" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-md bg-red-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white sm:w-auto">Desactivar</button>
            </div>
        </div>
    </div>
</div>
