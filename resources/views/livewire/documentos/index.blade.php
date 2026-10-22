<div x-data="{ formOpen: false, deleteOpen: false }" x-on:documento-guardado.window="formOpen = false" x-on:documento-eliminado.window="deleteOpen = false" x-on:keydown.escape.window="formOpen = false; deleteOpen = false">
    @if($successMessage !== '')
        <div class="mb-6 rounded-control border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="alert">
            {{ $successMessage }}
        </div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h3 class="text-lg font-medium text-ink">Documentos del proceso</h3>
            <p class="mt-1 text-sm text-muted">Solo PDF hasta 20 MB. La categoría es obligatoria y debe estar activa.</p>
        </div>
        @can('create', \App\Models\Documento::class)
            <button type="button" @click="formOpen = true" wire:click="createDocumento" class="inline-flex items-center justify-center rounded-control border border-transparent bg-aequitas-900 min-h-11 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-aequitas-800 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">
                Nuevo documento
            </button>
        @endcan
    </div>

    <div class="mt-6">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o categoría..." class="block w-full rounded-control border-line shadow-panel focus:border-gold-600 focus:ring-gold-600 sm:max-w-sm" />
    </div>

    <div class="mt-6 overflow-x-auto rounded-card border border-line shadow-panel">
        <table class="w-full min-w-[820px] text-left text-sm text-muted">
            <thead class="bg-canvas text-xs uppercase tracking-wide text-ink">
                <tr>
                    <th class="px-6 py-3">Nombre</th>
                    <th class="px-6 py-3">Categoría</th>
                    <th class="px-6 py-3">Tipo doc (histórico)</th>
                    <th class="px-6 py-3">Fecha</th>
                    <th class="px-6 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documentos as $documento)
                    <tr wire:key="doc-{{ $documento->id }}" class="border-b bg-white hover:bg-canvas">
                        <td class="px-6 py-4 font-medium text-ink">{{ $documento->nombre }}</td>
                        <td class="px-6 py-4">
                            @if($documento->categoria)
                                <span class="inline-flex rounded-full bg-aequitas-50 px-3 py-1 text-xs font-semibold text-aequitas-900">{{ $documento->categoria->nombre }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-muted">{{ $documento->tipo_documento }}</td>
                        <td class="px-6 py-4">{{ $documento->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap justify-center gap-2">
                                @can('view', $documento)
                                    <a href="{{ route('procesos.documentos.show', [$proceso, $documento]) }}" target="_blank" rel="noopener" class="rounded-control bg-aequitas-50 min-h-11 px-3 py-2 font-medium text-aequitas-900 hover:bg-aequitas-100">Ver PDF</a>
                                @endcan
                                @can('download', $documento)
                                    <a href="{{ route('procesos.documentos.download', [$proceso, $documento]) }}" class="rounded-control bg-canvas min-h-11 px-3 py-2 font-medium text-ink hover:bg-aequitas-50">Descargar</a>
                                @endcan
                                @can('update', $documento)
                                    <button type="button" wire:click="editDocumento({{ $documento->id }})" @click="formOpen = true" class="rounded-control bg-aequitas-50 min-h-11 px-3 py-2 font-medium text-aequitas-900 hover:bg-aequitas-100">Editar</button>
                                @endcan
                                @can('delete', $documento)
                                    <button type="button" wire:click="confirmDelete({{ $documento->id }})" @click="deleteOpen = true" class="rounded-control bg-red-50 min-h-11 px-3 py-2 font-medium text-red-700 hover:bg-red-100">Eliminar</button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @if($documento->versiones->isNotEmpty())
                        <tr wire:key="doc-versions-{{ $documento->id }}" class="border-b bg-canvas">
                            <td colspan="5" class="px-6 py-3">
                                <details>
                                    <summary class="cursor-pointer text-sm font-medium text-aequitas-900">Historial · {{ $documento->versiones->count() }} {{ $documento->versiones->count() === 1 ? 'versión anterior' : 'versiones anteriores' }}</summary>
                                    <ul class="mt-3 space-y-2">
                                        @foreach($documento->versiones as $version)
                                            <li class="flex flex-col gap-2 rounded-control bg-white p-3 sm:flex-row sm:items-center sm:justify-between" wire:key="doc-version-{{ $version->id }}">
                                                <div>
                                                    <p class="text-sm font-medium text-ink">{{ $version->nombre }} · {{ $version->categoria_nombre ?: $version->categoria?->nombre ?: 'Sin categoría' }}</p>
                                                    <p class="mt-1 text-xs text-muted">{{ $version->tipo_documento }} · {{ $version->fecha_reemplazo?->format('d/m/Y H:i') ?? '—' }} · {{ $version->usuario?->nombre_completo ?? 'Usuario eliminado' }}</p>
                                                </div>
                                                <div class="flex flex-wrap gap-2">
                                                    @can('view', $documento)
                                                        <a href="{{ route('procesos.documentos.versiones.show', [$proceso, $documento, $version]) }}" target="_blank" rel="noopener" class="rounded-control bg-aequitas-50 min-h-11 px-3 py-2 text-xs font-medium text-aequitas-900 hover:bg-aequitas-100">Ver versión</a>
                                                    @endcan
                                                    @can('download', $documento)
                                                        <a href="{{ route('procesos.documentos.versiones.download', [$proceso, $documento, $version]) }}" class="rounded-control bg-canvas min-h-11 px-3 py-2 text-xs font-medium text-ink hover:bg-aequitas-50">Descargar versión</a>
                                                    @endcan
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </details>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr class="bg-white"><td colspan="5" class="px-6 py-10 text-center italic text-muted">No hay documentos para este proceso.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $documentos->links() }}</div>

    {{-- Modal crear/editar --}}
    <div x-cloak x-show="formOpen" x-transition.opacity role="dialog" aria-modal="true" @click.self="formOpen = false; $wire.closeModal()" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4 py-6 backdrop-blur-sm">
        <div class="w-full max-w-xl rounded-panel bg-white shadow-floating">
            <div class="flex items-start justify-between gap-4 border-b border-line px-6 py-5 sm:px-8">
                <div>
                    <h2 class="text-xl font-semibold text-ink">{{ $editingDocumentoId ? 'Editar documento' : 'Nuevo documento' }}</h2>
                    <p class="mt-1 text-sm text-muted">Las nuevas cargas requieren una categoría activa. Al editar, podés conservar la categoría histórica asociada. El archivo solo se reemplaza si seleccionás uno nuevo.</p>
                </div>
                <button type="button" @click="formOpen = false; $wire.closeModal()" class="rounded-control min-h-11 min-w-11 p-1 text-2xl leading-none text-gray-400 hover:bg-aequitas-50" aria-label="Cerrar">&times;</button>
            </div>
            <form wire:submit="saveDocumento" class="space-y-6 px-6 py-6 sm:px-8">
                <div>
                    <label for="doc-nombre" class="block text-sm font-medium text-ink">Nombre *</label>
                    <input id="doc-nombre" type="text" wire:model.defer="nombre" maxlength="255" class="mt-2 block w-full rounded-control border-line shadow-panel focus:border-gold-600 focus:ring-gold-600" />
                    @error('nombre')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="doc-categoria" class="block text-sm font-medium text-ink">Categoría {{ $editingDocumentoId ? '' : '*' }}</label>
                    <select id="doc-categoria" wire:model.defer="categoriaId" class="mt-2 block w-full rounded-control border-line shadow-panel focus:border-gold-600 focus:ring-gold-600">
                        @if($editingDocumentoId && $categoriaId === null)
                            <option value="">Sin categoría asociada (registro histórico)</option>
                        @else
                            <option value="">Seleccioná una categoría</option>
                        @endif
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nombre }}{{ ! $cat->activo || $cat->trashed() ? ' (histórica)' : '' }}</option>
                        @endforeach
                    </select>
                    @error('categoriaId')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
                    @if($categorias->isEmpty())
                        <p class="mt-2 text-sm text-amber-600">No hay categorías activas. Creá una en /categorias.</p>
                    @endif
                </div>
                <div>
                    <label for="doc-archivo" class="block text-sm font-medium text-ink">Archivo PDF {{ $editingDocumentoId ? '(opcional al editar)' : '*' }}</label>
                    <input id="doc-archivo" type="file" accept="application/pdf,.pdf" wire:model="archivo" class="mt-2 block w-full cursor-pointer rounded-control border border-line bg-white min-h-11 px-3 py-2 text-sm text-ink shadow-panel file:mr-4 file:rounded-control file:border-0 file:bg-aequitas-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-aequitas-900 hover:file:bg-aequitas-100" />
                    <p wire:loading wire:target="archivo" class="mt-2 text-sm text-aequitas-900">Preparando archivo...</p>
                    @error('archivo')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
                </div>
                <div class="flex flex-col-reverse gap-3 border-t border-line pt-5 sm:flex-row sm:justify-end">
                    <button type="button" @click="formOpen = false; $wire.closeModal()" class="inline-flex w-full items-center justify-center rounded-control border border-line bg-white min-h-11 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-ink sm:w-auto">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-control bg-aequitas-900 min-h-11 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white sm:w-auto">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal eliminar --}}
    <div x-cloak x-show="deleteOpen" x-transition.opacity role="dialog" aria-modal="true" @click.self="deleteOpen = false; $wire.closeDeleteModal()" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4 py-6 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-panel bg-white p-6 shadow-floating sm:p-8">
            <h2 class="text-lg font-semibold text-ink">Eliminar documento</h2>
            <p class="mt-2 text-sm leading-6 text-muted">Se hará baja lógica. Se conserva auditoría y archivo físico.</p>
            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" @click="deleteOpen = false; $wire.closeDeleteModal()" class="inline-flex w-full items-center justify-center rounded-control border border-line bg-white min-h-11 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-ink sm:w-auto">Cancelar</button>
                <button type="button" wire:click="deleteDocumento" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-control bg-red-600 min-h-11 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white sm:w-auto">Eliminar</button>
            </div>
        </div>
    </div>
</div>
