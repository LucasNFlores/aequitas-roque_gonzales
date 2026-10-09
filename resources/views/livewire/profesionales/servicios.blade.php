<div
    x-data="{ serviciosOpen: @entangle('showModal') }"
    x-on:keydown.escape.window="serviciosOpen = false; $wire.closeModal()"
>
    @if($successMessage !== '')
        <div class="mb-6 rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700" role="alert">
            {{ $successMessage }}
        </div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-lg font-medium text-gray-900">Asignar servicios a profesionales</h3>
            <p class="mt-1 text-sm text-gray-500">Selecciona los servicios que cada profesional activo puede atender.</p>
        </div>
        <label class="sr-only" for="professional-search">Buscar profesional</label>
        <input
            id="professional-search"
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar profesional..."
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-sm"
        />
    </div>

    <div class="mt-6 overflow-x-auto rounded-lg shadow-md">
        <table class="w-full text-left text-sm text-gray-500">
            <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3">Profesional</th>
                    <th scope="col" class="px-6 py-3">Servicios asignados</th>
                    <th scope="col" class="px-6 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($profesionales as $profesional)
                    <tr wire:key="professional-{{ $profesional->id }}" class="border-b bg-white hover:bg-gray-50">
                        <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-900">{{ $profesional->name }}</td>
                        <td class="px-6 py-4">
                            @forelse($profesional->servicios as $servicio)
                                <span class="mr-1 inline-flex rounded bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">{{ $servicio->nombre }}</span>
                            @empty
                                <span class="text-xs italic text-gray-400">Sin servicios asignados</span>
                            @endforelse
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button
                                type="button"
                                wire:click="openServices({{ $profesional->id }})"
                                @click="serviciosOpen = true"
                                class="rounded-md bg-indigo-50 px-3 py-2 font-medium text-indigo-700 transition hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                Gestionar servicios
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white">
                        <td colspan="3" class="px-6 py-8 text-center italic text-gray-500">No hay profesionales activos para mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $profesionales->links() }}
    </div>

    <div x-cloak x-show="serviciosOpen" x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="servicios-modal-title" @click.self="serviciosOpen = false; $wire.closeModal()" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/60 px-4 py-6 backdrop-blur-sm sm:px-6">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl sm:p-8">
            <h2 id="servicios-modal-title" class="text-lg font-semibold text-gray-900">Servicios del profesional</h2>
            <p class="mt-1 text-sm text-gray-500">Marca los servicios que puede atender. Desmarca los que quieras retirar.</p>

            <form wire:submit="saveServices" class="mt-6 space-y-4">
                <div class="max-h-64 overflow-y-auto rounded-md border border-gray-200 p-3">
                    @forelse($servicios as $servicio)
                        <label wire:key="service-{{ $servicio->id }}" class="flex items-center gap-2 py-2">
                            <input type="checkbox" value="{{ $servicio->id }}" wire:model="selectedServicios" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                            <span class="text-sm text-gray-700">{{ $servicio->nombre }}</span>
                        </label>
                    @empty
                        <span class="text-sm italic text-gray-500">No hay servicios activos disponibles.</span>
                    @endforelse
                </div>
                @error('selectedServicios.*') <span class="block text-sm text-red-600">{{ $message }}</span> @enderror

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" @click="serviciosOpen = false; $wire.closeModal()" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveServices">Guardar</span>
                        <span wire:loading wire:target="saveServices">Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
