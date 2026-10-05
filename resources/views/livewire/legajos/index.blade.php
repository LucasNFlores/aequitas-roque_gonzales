<div class="mt-6 space-y-5">
    <label for="legajo-search" class="sr-only">Buscar por DNI, nombre o apellido</label>
    <input
        id="legajo-search"
        type="search"
        wire:model.live.debounce.300ms="search"
        placeholder="DNI, nombre o apellido"
        autocomplete="off"
        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-xl"
    />

    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="w-full min-w-[620px] text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-700">
                <tr>
                    <th scope="col" class="px-5 py-3">Cliente</th>
                    <th scope="col" class="px-5 py-3">DNI</th>
                    <th scope="col" class="px-5 py-3">Procesos visibles</th>
                    <th scope="col" class="px-5 py-3 text-right">Legajo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($clientes as $cliente)
                    <tr wire:key="legajo-cliente-{{ $cliente->id }}">
                        <td class="px-5 py-4 font-medium text-gray-900">{{ $cliente->apellido }}, {{ $cliente->nombre }}</td>
                        <td class="px-5 py-4">{{ $cliente->dni }}</td>
                        <td class="px-5 py-4">{{ $cliente->procesos_visibles_count }}</td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('legajos.show', $cliente) }}" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Consultar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-gray-500">
                            {{ trim($search) === '' ? 'No hay legajos disponibles para consultar.' : 'No encontramos clientes con esos datos.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $clientes->links() }}</div>
</div>
