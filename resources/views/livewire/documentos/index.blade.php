<div x-data="{ formOpen: false, deleteOpen: false }" x-on:documento-guardado.window="formOpen = false" x-on:documento-eliminado.window="deleteOpen = false" x-on:keydown.escape.window="formOpen = false; deleteOpen = false">
    @if($successMessage !== '')
        <div class="mb-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="alert">
            {{ $successMessage }}
        </div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h3 class="text-lg font-medium text-gray-900">Documentos del proceso</h3>
            <p class="mt-1 text-sm text-gray-500">Solo PDF hasta 20 MB. La categoría es obligatoria y debe estar activa.</p>
        </div>
        @can('create', \App\Models\Documento::class)
            <button type="button" @click="formOpen = true" wire:click="createDocumento" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Nuevo documento
            </button>
        @endcan
    </div>

    <div class="mt-6">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o categoría..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-sm" />
    </div>

    <div class="mt-6 overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
        <table class="w-full min-w-[820px] text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-700">
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
                    <tr wire:key="doc-{{ $documento->id }}" class="border-b bg-white hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $documento->nombre }}</td>
                        <td class="px-6 py-4">
                            @if($documento->categoria)
                                <span class="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ $documento->categoria->nombre }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ $documento->tipo_documento }}</td>
                        <td class="px-6 py-4">{{ $documento->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap justify-center gap-2">
                                @can('view', $documento)
                                    <a href="{{ route('procesos.documentos.download', [$proceso, $documento]) }}" class="rounded-md bg-gray-50 px-3 py-2 font-medium text-gray-700 hover:bg-gray-100">Descargar</a>
                                @endcan
                                @can('update', $documento)
                                    <button type="button" wire:click="editDocumento({{ $documento->id }})" @click="formOpen = true" class="rounded-md bg-indigo-50 px-3 py-2 font-medium text-indigo-700 hover:bg-indigo-100">Editar</button>
                                @endcan
                                @can('delete', $documento)
                                    <button type="button" wire:click="confirmDelete({{ $documento->id }})" @click="deleteOpen = true" class="rounded-md bg-red-50 px-3 py-2 font-medium text-red-700 hover:bg-red-100">Eliminar</button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white"><td colspan="5" class="px-6 py-10 text-center italic text-gray-500">No hay documentos para este proceso.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $documentos->links() }}</div>

    {{-- Modal crear/editar --}}
    <div x-cloak x-show="formOpen" x-transition.opacity role="dialog" aria-modal="true" @click.self="formOpen = false; $wire.closeModal()" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 px-4 py-6 backdrop-blur-sm">
        <div class="w-full max-w-xl rounded-xl bg-white shadow-2xl">
            <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-6 py-5 sm:px-8">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $editingDocumentoId ? 'Editar documento' : 'Nuevo documento' }}</h2>
                    <p class="mt-1 text-sm text-gray-500">La categoría debe estar activa. El archivo solo se reemplaza si seleccionás uno nuevo.</p>
                </div>
                <button type="button" @click="formOpen = false; $wire.closeModal()" class="rounded-md p-1 text-2xl leading-none text-gray-400 hover:bg-gray-100" aria-label="Cerrar">&times;</button>
            </div>
            <form wire:submit="saveDocumento" class="space-y-6 px-6 py-6 sm:px-8">
                <div>
                    <label for="doc-nombre" class="block text-sm font-medium text-gray-700">Nombre *</label>
                    <input id="doc-nombre" type="text" wire:model.defer="nombre" maxlength="255" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('nombre')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="doc-categoria" class="block text-sm font-medium text-gray-700">Categoría *</label>
                    <select id="doc-categoria" wire:model.defer="categoriaId" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Seleccioná una categoría</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                    @error('categoriaId')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
                    @if($categorias->isEmpty())
                        <p class="mt-2 text-sm text-amber-600">No hay categorías activas. Creá una en /categorias.</p>
                    @endif
                </div>
                <div>
                    <label for="doc-archivo" class="block text-sm font-medium text-gray-700">Archivo PDF {{ $editingDocumentoId ? '(opcional al editar)' : '*' }}</label>
                    <input id="doc-archivo" type="file" accept="application/pdf,.pdf" wire:model="archivo" class="mt-2 block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100" />
                    <p wire:loading wire:target="archivo" class="mt-2 text-sm text-indigo-600">Preparando archivo...</p>
                    @error('archivo')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
                </div>
                <div class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" @click="formOpen = false; $wire.closeModal()" class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-gray-700 sm:w-auto">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-md bg-indigo-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white sm:w-auto">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal eliminar --}}
    <div x-cloak x-show="deleteOpen" x-transition.opacity role="dialog" aria-modal="true" @click.self="deleteOpen = false; $wire.closeDeleteModal()" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 px-4 py-6 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl sm:p-8">
            <h2 class="text-lg font-semibold text-gray-900">Eliminar documento</h2>
            <p class="mt-2 text-sm leading-6 text-gray-600">Se hará baja lógica. Se conserva auditoría y archivo físico.</p>
            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" @click="deleteOpen = false; $wire.closeDeleteModal()" class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-gray-700 sm:w-auto">Cancelar</button>
                <button type="button" wire:click="deleteDocumento" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-md bg-red-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white sm:w-auto">Eliminar</button>
            </div>
        </div>
    </div>
</div>
