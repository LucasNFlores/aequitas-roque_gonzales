<div
    x-data="{ formOpen: false, deactivateOpen: false, deleteOpen: false, restoreOpen: false }"
    x-on:categoria-guardada.window="formOpen = false"
    x-on:categoria-desactivada.window="deactivateOpen = false"
    x-on:categoria-eliminada.window="deleteOpen = false"
    x-on:categoria-restaurada.window="restoreOpen = false"
    x-on:keydown.escape.window="formOpen = false; deactivateOpen = false; deleteOpen = false; restoreOpen = false; $wire.closeModal(); $wire.closeDeactivateModal(); $wire.closeDeleteModal(); $wire.closeRestoreModal()"
>
    @if($successMessage !== '')
        <div class="mb-6 rounded-control border border-green-200 bg-green-50 px-4 py-3 text-sm text-positive" role="alert">
            {{ $successMessage }}
        </div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h3 class="text-lg font-medium text-ink">Categorías de documentos</h3>
            <p class="mt-1 text-sm text-muted">Clasifican la documentación de los legajos. Desactivar impide nuevas cargas; la baja lógica conserva asociaciones, documentos y archivos históricos.</p>
        </div>
        @can('create', \App\Models\CategoriaDocumento::class)
            <button type="button" @click="formOpen = true" wire:click="createCategoria" class="inline-flex items-center justify-center rounded-control border border-transparent bg-aequitas-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-aequitas-800 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">
                Nueva categoría
            </button>
        @endcan
    </div>

    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre..." class="block w-full rounded-control border-line shadow-sm focus:border-gold-600 focus:ring-gold-600 sm:max-w-sm" />
        <select wire:model.live="filtro" class="block rounded-control border-line shadow-sm focus:border-gold-600 focus:ring-gold-600 sm:max-w-xs">
            <option value="todas">Todas</option>
            <option value="activas">Activas</option>
            <option value="inactivas">Inactivas</option>
            <option value="baja">Dadas de baja</option>
        </select>
    </div>

    <div class="mt-6 overflow-x-auto rounded-card border border-line shadow-sm">
        <table class="w-full min-w-[760px] text-left text-sm text-muted">
            <thead class="bg-canvas text-xs uppercase tracking-wide text-muted">
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
                    <tr wire:key="categoria-{{ $categoria->id }}" class="border-b bg-white hover:bg-canvas">
                        <td class="px-6 py-4 font-medium text-ink">{{ $categoria->nombre }}</td>
                        <td class="px-6 py-4">{{ $categoria->descripcion ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $categoria->trashed() ? 'bg-red-100 text-red-700' : ($categoria->activo ? 'bg-green-100 text-positive' : 'bg-canvas text-muted') }}">
                                {{ $categoria->trashed() ? 'Dada de baja' : ($categoria->activo ? 'Activa' : 'Inactiva') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">{{ $categoria->documentos_count }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap justify-center gap-2">
                                @if($categoria->trashed())
                                    @can('restore', $categoria)
                                        <button type="button" wire:click="confirmRestore({{ $categoria->id }})" @click="restoreOpen = true" class="rounded-control bg-green-50 px-3 py-2 font-medium text-positive transition hover:bg-green-100">Restaurar</button>
                                    @endcan
                                @else
                                    @can('update', $categoria)
                                        <button type="button" wire:click="editCategoria({{ $categoria->id }})" @click="formOpen = true" class="rounded-control bg-aequitas-50 px-3 py-2 font-medium text-aequitas-900 transition hover:bg-aequitas-100">Editar</button>
                                    @endcan
                                    @if($categoria->activo)
                                        @can('deactivate', $categoria)
                                            <button type="button" wire:click="confirmDeactivate({{ $categoria->id }})" @click="deactivateOpen = true" class="rounded-control bg-amber-50 px-3 py-2 font-medium text-amber-700 transition hover:bg-amber-100">Desactivar</button>
                                        @endcan
                                    @else
                                        @can('activate', $categoria)
                                            <button type="button" wire:click="activateCategoria({{ $categoria->id }})" class="rounded-control bg-green-50 px-3 py-2 font-medium text-positive transition hover:bg-green-100">Reactivar</button>
                                        @endcan
                                    @endif
                                    @can('delete', $categoria)
                                        <button type="button" wire:click="confirmDelete({{ $categoria->id }})" @click="deleteOpen = true" class="rounded-control bg-red-50 px-3 py-2 font-medium text-red-700 transition hover:bg-red-100">Dar de baja</button>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white"><td colspan="5" class="px-6 py-10 text-center italic text-muted">No se encontraron categorías.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div x-cloak x-show="formOpen" x-transition.opacity role="dialog" aria-modal="true" @click.self="formOpen = false; $wire.closeModal()" class="fixed inset-0 z-50 flex items-center justify-center bg-aequitas-900/60 px-4 py-6 backdrop-blur-sm">
        <div class="w-full max-w-xl rounded-panel bg-white shadow-2xl">
            <div class="flex items-start justify-between gap-4 border-b border-line px-6 py-5 sm:px-8">
                <div>
                    <h2 class="text-xl font-semibold text-ink">{{ $editingCategoriaId ? 'Editar categoría' : 'Nueva categoría' }}</h2>
                    <p class="mt-1 text-sm text-muted">El nombre debe ser único. La edición no afecta el historial.</p>
                </div>
                <button type="button" @click="formOpen = false; $wire.closeModal()" class="rounded-control p-1 text-2xl leading-none text-muted hover:bg-canvas" aria-label="Cerrar">&times;</button>
            </div>
            <form wire:submit="saveCategoria" class="space-y-6 px-6 py-6 sm:px-8">
                <div>
                    <label for="cat-nombre" class="block text-sm font-medium text-muted">Nombre *</label>
                    <input id="cat-nombre" type="text" wire:model.defer="nombre" maxlength="100" class="mt-2 block w-full rounded-control border-line shadow-sm focus:border-gold-600 focus:ring-gold-600" />
                    @error('nombre')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="cat-desc" class="block text-sm font-medium text-muted">Descripción</label>
                    <textarea id="cat-desc" wire:model.defer="descripcion" maxlength="255" rows="3" class="mt-2 block w-full rounded-control border-line shadow-sm focus:border-gold-600 focus:ring-gold-600"></textarea>
                    @error('descripcion')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
                </div>
                <div class="flex flex-col-reverse gap-3 border-t border-line pt-5 sm:flex-row sm:justify-end">
                    <button type="button" @click="formOpen = false; $wire.closeModal()" class="inline-flex w-full items-center justify-center rounded-control border border-line bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-muted sm:w-auto">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-control bg-aequitas-900 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white sm:w-auto">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div x-cloak x-show="deactivateOpen" x-transition.opacity role="dialog" aria-modal="true" @click.self="deactivateOpen = false; $wire.closeDeactivateModal()" class="fixed inset-0 z-50 flex items-center justify-center bg-aequitas-900/60 px-4 py-6 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-panel bg-white p-6 shadow-2xl sm:p-8">
            <h2 class="text-lg font-semibold text-ink">Desactivar categoría</h2>
            <p class="mt-2 text-sm leading-6 text-muted">
                @if($docsEnUso > 0)
                    No se puede desactivar: tiene {{ $docsEnUso }} documentos asociados. Reasigne esos documentos o conserve la categoría activa.
                @else
                    Dejará de ofrecerse para nuevas cargas.
                @endif
            </p>
            @error('deactivate')<span class="mt-2 block text-sm text-red-600">{{ $message }}</span>@enderror
            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" @click="deactivateOpen = false; $wire.closeDeactivateModal()" class="inline-flex w-full items-center justify-center rounded-control border border-line bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-muted sm:w-auto">Cancelar</button>
                <button type="button" wire:click="deactivateCategoria" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-control bg-red-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white sm:w-auto">Desactivar</button>
            </div>
        </div>
    </div>

    <div x-cloak x-show="deleteOpen" x-transition.opacity role="dialog" aria-modal="true" @click.self="deleteOpen = false; $wire.closeDeleteModal()" class="fixed inset-0 z-50 flex items-center justify-center bg-aequitas-900/60 px-4 py-6 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-panel bg-white p-6 shadow-2xl sm:p-8">
            <h2 class="text-lg font-semibold text-ink">Dar de baja categoría</h2>
            <p class="mt-2 text-sm leading-6 text-muted">
                Se ocultará del catálogo operativo. Los {{ $docsAsociados }} documentos asociados conservarán la categoría histórica y sus archivos; no se borrará ningún documento.
            </p>
            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" @click="deleteOpen = false; $wire.closeDeleteModal()" class="inline-flex w-full items-center justify-center rounded-control border border-line bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-muted sm:w-auto">Cancelar</button>
                <button type="button" wire:click="deleteCategoria" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-control bg-red-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white sm:w-auto">Dar de baja</button>
            </div>
        </div>
    </div>

    <div x-cloak x-show="restoreOpen" x-transition.opacity role="dialog" aria-modal="true" @click.self="restoreOpen = false; $wire.closeRestoreModal()" class="fixed inset-0 z-50 flex items-center justify-center bg-aequitas-900/60 px-4 py-6 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-panel bg-white p-6 shadow-2xl sm:p-8">
            <h2 class="text-lg font-semibold text-ink">Restaurar categoría</h2>
            <p class="mt-2 text-sm leading-6 text-muted">
                Se restaurará {{ $categoriaRestaurando?->nombre }} y conservará su estado {{ $categoriaRestaurando?->activo ? 'activo' : 'inactivo' }}. Las categorías inactivas no estarán disponibles para nuevas cargas.
            </p>
            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" @click="restoreOpen = false; $wire.closeRestoreModal()" class="inline-flex w-full items-center justify-center rounded-control border border-line bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-muted sm:w-auto">Cancelar</button>
                <button type="button" wire:click="restoreCategoria" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-control bg-green-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white sm:w-auto">Restaurar</button>
            </div>
        </div>
    </div>
</div>
