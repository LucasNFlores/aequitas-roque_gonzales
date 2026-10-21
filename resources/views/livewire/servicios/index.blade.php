{{--
    EXPLICACIÓN DE ESTA PÁGINA PARA DESARROLLO

    Esta es una vista Livewire. El componente PHP está en
    app/Livewire/Servicios/Index.php y este archivo contiene la interfaz.

    - Tailwind CSS define el aspecto visual y el diseño responsive.
    - Alpine.js controla formOpen y deleteOpen directamente en el navegador.
    - Livewire ejecuta en el servidor la autorización, validación y persistencia.
    - @click cambia la interfaz inmediatamente; wire:click ejecuta la acción de Livewire.
    - El modal no usa @if porque debe existir en el HTML desde el inicio. x-show sólo lo oculta.
    - wire:loading muestra información mientras Livewire espera una respuesta del servidor.
    - Los eventos servicio-guardado y servicio-eliminado cierran los modales después de completar la operación.
--}}
<div
    x-data="{ formOpen: false, deleteOpen: false }"
    x-on:servicio-guardado.window="formOpen = false"
    x-on:servicio-eliminado.window="deleteOpen = false"
    x-on:keydown.escape.window="formOpen = false; deleteOpen = false"
>
{{-- Mensaje que Livewire actualiza después de crear, editar o eliminar. --}}
@if($successMessage !== '')
    <div class="mb-6 rounded border border-green-400 bg-green-100 px-4 py-3 text-positive" role="alert">
        {{ $successMessage }}
    </div>
@endif

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h3 class="text-lg font-medium text-ink">Listado de Servicios</h3>
        <p class="mt-1 text-sm text-muted">Gestiona los servicios sin salir de esta pantalla.</p>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('servicios-viejo.index') }}" class="text-sm font-medium text-muted hover:text-ink">
            Ver versión clásica
        </a>
        <button type="button" @click="formOpen = true" wire:click="createServicio" class="inline-flex items-center rounded-control border border-transparent bg-aequitas-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-aequitas-800 focus:bg-aequitas-800 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2 active:bg-aequitas-950">
            Nuevo Servicio
        </button>
    </div>
</div>

{{-- wire:model.live actualiza la búsqueda sin recargar toda la página. --}}
<div class="mt-6">
    <label for="search" class="sr-only">Buscar servicio</label>
    <input id="search" type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre..." class="block w-full rounded-control border-line shadow-sm focus:border-gold-600 focus:ring-gold-600 sm:max-w-sm" />
</div>

{{-- Livewire reemplaza sólo las partes necesarias de esta tabla al buscar o paginar. --}}
<div class="mt-6 overflow-x-auto rounded-card shadow-panel">
    <table class="w-full text-left text-sm text-muted">
        <thead class="bg-canvas text-xs uppercase text-muted">
            <tr>
                <th scope="col" class="px-6 py-3">Nombre</th>
                <th scope="col" class="px-6 py-3">Costo</th>
                <th scope="col" class="px-6 py-3 text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($servicios as $servicio)
                <tr wire:key="servicio-{{ $servicio->id }}" class="border-b bg-white hover:bg-canvas">
                    <td class="whitespace-nowrap px-6 py-4 font-medium text-ink">
                        {{ $servicio->nombre }}
                    </td>
                    <td class="px-6 py-4">
                        ${{ number_format($servicio->costo_servicio, 2, ',', '.') }}
                    </td>
                    <td class="space-x-2 px-6 py-4 text-center">
                        <button type="button" @click="formOpen = true" wire:click="editServicio({{ $servicio->id }})" class="rounded-control bg-aequitas-50 px-3 py-2 font-medium text-aequitas-900 transition hover:text-aequitas-900">
                            Editar
                        </button>
                        <button type="button" @click="deleteOpen = true" wire:click="confirmDelete({{ $servicio->id }})" class="rounded-control bg-red-50 px-3 py-2 font-medium text-red-600 transition hover:text-red-900">
                            Eliminar
                        </button>
                    </td>
                </tr>
            @empty
                <tr class="bg-white">
                    <td colspan="3" class="px-6 py-4 text-center italic text-muted">
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

{{-- Modal de alta/edición: Alpine lo abre localmente; Livewire procesa los datos. --}}
<div x-cloak x-show="formOpen" x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="servicio-modal-title" wire:key="servicio-form-modal" @click.self="formOpen = false; $wire.closeModal()" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-aequitas-900/60 px-4 py-6 backdrop-blur-sm sm:px-6">
        <div class="relative max-h-[calc(100vh-3rem)] w-full max-w-xl overflow-y-auto rounded-panel bg-white shadow-2xl">
            <div wire:loading.flex wire:target="createServicio,editServicio" class="absolute inset-0 z-10 items-center justify-center rounded-panel bg-white/80">
                <span class="rounded-control bg-white px-4 py-3 text-sm font-medium text-muted shadow">Cargando...</span>
            </div>
            <div class="flex items-start justify-between gap-4 border-b border-line px-6 py-5 sm:px-8">
                <div>
                    <h2 id="servicio-modal-title" class="text-xl font-semibold text-ink">
                        {{ $editingServicioId ? 'Editar Servicio' : 'Nuevo Servicio' }}
                    </h2>
                    <p class="mt-1 text-sm text-muted">Completa los datos del servicio.</p>
                </div>
                <button type="button" @click="formOpen = false; $wire.closeModal()" class="rounded-control p-1 text-2xl leading-none text-muted transition hover:bg-canvas hover:text-muted focus:outline-none focus:ring-2 focus:ring-gold-600" aria-label="Cerrar modal">
                    &times;
                </button>
            </div>

            {{-- wire:submit evita una recarga y envía los campos al método saveServicio. --}}
            <form wire:submit="saveServicio" class="space-y-6 px-6 py-6 sm:px-8">
                <div>
                    <label for="modal-nombre" class="block text-sm font-medium text-muted">Nombre del servicio</label>
                    <input id="modal-nombre" type="text" wire:model.defer="nombre" class="mt-2 block w-full rounded-control border-line px-3 py-2.5 shadow-sm focus:border-gold-600 focus:ring-gold-600" autofocus />
                    @error('nombre')
                        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="modal-costo-servicio" class="block text-sm font-medium text-muted">Costo del servicio</label>
                    <input id="modal-costo-servicio" type="number" step="0.01" min="0" wire:model.defer="costoServicio" class="mt-2 block w-full rounded-control border-line px-3 py-2.5 shadow-sm focus:border-gold-600 focus:ring-gold-600" />
                    @error('costoServicio')
                        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-line pt-5 sm:flex-row sm:justify-end">
                    <button type="button" @click="formOpen = false; $wire.closeModal()" class="inline-flex w-full items-center justify-center rounded-control border border-line bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-muted shadow-sm transition hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2 sm:w-auto">
                        Cancelar
                    </button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-control border border-transparent bg-aequitas-900 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-aequitas-800 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2 disabled:opacity-50 sm:w-auto">
                        <span wire:loading.remove wire:target="saveServicio">{{ $editingServicioId ? 'Guardar cambios' : 'Crear servicio' }}</span>
                        <span wire:loading wire:target="saveServicio">Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

{{-- Modal de confirmación: la apertura es local y la eliminación se confirma en Livewire. --}}
<div x-cloak x-show="deleteOpen" x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="delete-modal-title" wire:key="servicio-delete-modal" @click.self="deleteOpen = false; $wire.closeDeleteModal()" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-aequitas-900/60 px-4 py-6 backdrop-blur-sm sm:px-6">
        <div class="w-full max-w-md rounded-panel bg-white p-6 shadow-2xl sm:p-8">
            <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600" aria-hidden="true">!</div>
                <div>
                    <h2 id="delete-modal-title" class="text-lg font-semibold text-ink">Eliminar servicio</h2>
                    <p class="mt-2 text-sm leading-6 text-muted">¿Seguro que deseas eliminar este servicio?</p>
                </div>
            </div>

            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" @click="deleteOpen = false; $wire.closeDeleteModal()" class="inline-flex w-full items-center justify-center rounded-control border border-line bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-muted shadow-sm transition hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2 sm:w-auto">
                    Cancelar
                </button>
                <button type="button" wire:click="deleteServicio" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-control border border-transparent bg-red-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 sm:w-auto">
                    <span wire:loading.remove wire:target="deleteServicio">Eliminar</span>
                    <span wire:loading wire:target="deleteServicio">Eliminando...</span>
                </button>
            </div>
        </div>
    </div>
</div>
