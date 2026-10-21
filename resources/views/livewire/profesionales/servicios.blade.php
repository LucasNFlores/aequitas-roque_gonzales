<div
    x-data="{ serviciosOpen: @entangle('showModal') }"
    x-on:keydown.escape.window="serviciosOpen = false; $wire.closeModal()"
>
    @if($successMessage !== '')
        <div class="mb-6 rounded border border-green-400 bg-green-100 px-4 py-3 text-positive" role="alert">
            {{ $successMessage }}
        </div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-lg font-medium text-ink">Asignar servicios a profesionales</h3>
            <p class="mt-1 text-sm text-muted">Selecciona los servicios que cada profesional activo puede atender.</p>
        </div>
        <label class="sr-only" for="professional-search">Buscar profesional</label>
        <input
            id="professional-search"
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar profesional..."
            class="block w-full rounded-control border-line shadow-sm focus:border-gold-600 focus:ring-gold-600 sm:max-w-sm"
        />
    </div>

    <div class="mt-6 overflow-x-auto rounded-card shadow-panel">
        <table class="w-full text-left text-sm text-muted">
            <thead class="bg-canvas text-xs uppercase text-muted">
                <tr>
                    <th scope="col" class="px-6 py-3">Profesional</th>
                    <th scope="col" class="px-6 py-3">Servicios asignados</th>
                    <th scope="col" class="px-6 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($profesionales as $profesional)
                    <tr wire:key="professional-{{ $profesional->id }}" class="border-b bg-white hover:bg-canvas">
                        <td class="whitespace-nowrap px-6 py-4 font-medium text-ink">{{ $profesional->name }}</td>
                        <td class="px-6 py-4">
                            @forelse($profesional->servicios as $servicio)
                                <span class="mr-1 inline-flex rounded bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-positive">{{ $servicio->nombre }}</span>
                            @empty
                                <span class="text-xs italic text-muted">Sin servicios asignados</span>
                            @endforelse
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button
                                type="button"
                                wire:click="openServices({{ $profesional->id }})"
                                @click="serviciosOpen = true"
                                class="rounded-control bg-aequitas-50 px-3 py-2 font-medium text-aequitas-900 transition hover:bg-aequitas-100 focus:outline-none focus:ring-2 focus:ring-gold-600"
                            >
                                Gestionar servicios
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white">
                        <td colspan="3" class="px-6 py-8 text-center italic text-muted">No hay profesionales activos para mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $profesionales->links() }}
    </div>

    <div x-cloak x-show="serviciosOpen" x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="servicios-modal-title" @click.self="serviciosOpen = false; $wire.closeModal()" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-aequitas-900/60 px-4 py-6 backdrop-blur-sm sm:px-6">
        <div class="w-full max-w-md rounded-panel bg-white p-6 shadow-2xl sm:p-8">
            <h2 id="servicios-modal-title" class="text-lg font-semibold text-ink">Servicios del profesional</h2>
            <p class="mt-1 text-sm text-muted">Marca los servicios que puede atender. Desmarca los que quieras retirar.</p>

            <form wire:submit="saveServices" class="mt-6 space-y-4">
                <div class="max-h-64 overflow-y-auto rounded-control border border-line p-3">
                    @forelse($servicios as $servicio)
                        <label wire:key="service-{{ $servicio->id }}" class="flex items-center gap-2 py-2">
                            <input type="checkbox" value="{{ $servicio->id }}" wire:model="selectedServicios" class="rounded border-line text-aequitas-900 shadow-sm focus:ring-gold-600" />
                            <span class="text-sm text-muted">{{ $servicio->nombre }}</span>
                        </label>
                    @empty
                        <span class="text-sm italic text-muted">No hay servicios activos disponibles.</span>
                    @endforelse
                </div>
                @error('selectedServicios.*') <span class="block text-sm text-red-600">{{ $message }}</span> @enderror

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" @click="serviciosOpen = false; $wire.closeModal()" class="inline-flex items-center justify-center rounded-control border border-line bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-muted shadow-sm transition hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center rounded-control border border-transparent bg-aequitas-900 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-aequitas-800 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2 disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveServices">Guardar</span>
                        <span wire:loading wire:target="saveServices">Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
