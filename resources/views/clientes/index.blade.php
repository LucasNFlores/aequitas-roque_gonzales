<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-[#172538]">
            {{ __('Gestión de Clientes') }}
        </h2>
    </x-slot>

    <div class="px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <section class="rounded-panel border border-[#dde2e8] bg-white p-4 shadow-panel sm:p-6">
                @if(session('success'))
                    <div class="mb-5 rounded-control border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-[#172538]">Listado de clientes</h3>
                        <p class="mt-1 text-sm text-[#5b6470]">Busca por nombre, apellido o DNI y filtra por estado de admisión.</p>
                    </div>
                    @can('create', App\Models\Cliente::class)
                        <a href="{{ route('clientes.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-control bg-[#172538] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#243850] focus:outline-none focus:ring-2 focus:ring-[#8c6936] focus:ring-offset-2">
                            Nuevo cliente
                        </a>
                    @endcan
                </div>

                <form method="GET" action="{{ route('clientes.index') }}" class="mb-6 grid grid-cols-1 items-end gap-4 md:grid-cols-3">
                    <div>
                        <label for="search" class="block text-sm font-medium text-[#172538]">Buscar cliente</label>
                        <input id="search" name="search" type="search" value="{{ $search }}" placeholder="Nombre, apellido o DNI" class="mt-1 block min-h-11 w-full rounded-control border-[#c7ced7] bg-white px-3 py-2 text-[#172538] shadow-sm placeholder:text-[#697483] focus:border-[#8c6936] focus:ring-[#8c6936]">
                    </div>

                    <div>
                        <label for="estado" class="block text-sm font-medium text-[#172538]">Estado de admisión</label>
                        <select id="estado" name="estado" class="mt-1 block min-h-11 w-full rounded-control border-[#c7ced7] bg-white px-3 py-2 text-[#172538] shadow-sm focus:border-[#8c6936] focus:ring-[#8c6936]">
                            <option value="">Todos los estados</option>
                            @foreach($estados as $opcionEstado)
                                <option value="{{ $opcionEstado->slug }}" @selected($estado === $opcionEstado->slug)>
                                    {{ $opcionEstado->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-control bg-[#172538] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#243850] focus:outline-none focus:ring-2 focus:ring-[#8c6936] focus:ring-offset-2">
                            Aplicar filtros
                        </button>
                        @if($search !== '' || $estado !== '')
                            <a href="{{ route('clientes.index') }}" class="rounded px-2 py-2 text-sm font-medium text-[#172538] underline decoration-[#8c6936] underline-offset-4 hover:text-[#243850] focus:outline-none focus:ring-2 focus:ring-[#8c6936]">
                                Limpiar filtros
                            </a>
                        @endif
                    </div>
                </form>

                <div class="hidden overflow-hidden rounded-card border border-[#dde2e8] sm:block">
                    <table class="w-full text-left text-sm text-[#5b6470]">
                        <thead class="bg-[#f5f6f8] text-xs uppercase tracking-wide text-[#172538]">
                            <tr>
                                <th scope="col" class="px-5 py-3">Nombre</th>
                                <th scope="col" class="px-5 py-3">DNI</th>
                                <th scope="col" class="px-5 py-3">Correo</th>
                                <th scope="col" class="px-5 py-3">Teléfono</th>
                                <th scope="col" class="px-5 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#dde2e8] bg-white">
                            @forelse($clientes as $cliente)
                                <tr class="transition hover:bg-[#f5f6f8]">
                                    <td class="px-5 py-4 font-medium text-[#172538]">
                                        <a href="{{ route('clientes.show', $cliente) }}" class="underline decoration-transparent underline-offset-4 hover:decoration-[#8c6936] focus:outline-none focus:ring-2 focus:ring-[#8c6936]">
                                            {{ $cliente->nombre }} {{ $cliente->apellido }}
                                        </a>
                                    </td>
                                    <td class="px-5 py-4">{{ $cliente->dni }}</td>
                                    <td class="max-w-xs break-words px-5 py-4">{{ $cliente->correo }}</td>
                                    <td class="px-5 py-4">{{ $cliente->telefono }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            @can('view', $cliente)
                                                <a href="{{ route('clientes.show', $cliente) }}" class="inline-flex min-h-10 items-center rounded-control border border-[#c7ced7] bg-white px-3 py-2 text-sm font-medium text-[#172538] transition hover:bg-[#f5f6f8] focus:outline-none focus:ring-2 focus:ring-[#8c6936]">Ver</a>
                                            @endcan
                                            @can('update', $cliente)
                                                <a href="{{ route('clientes.edit', $cliente) }}" class="inline-flex min-h-10 items-center rounded-control bg-[#edf1f5] px-3 py-2 text-sm font-medium text-[#172538] transition hover:bg-[#dde2e8] focus:outline-none focus:ring-2 focus:ring-[#8c6936]">Editar</a>
                                            @endcan
                                            @can('delete', $cliente)
                                                <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas dar de baja este cliente?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex min-h-10 items-center rounded-control bg-red-50 px-3 py-2 text-sm font-medium text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-600">Eliminar</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-[#5b6470]">
                                        @if($search !== '' || $estado !== '')
                                            No encontramos clientes con esos filtros. Revisá los criterios o limpialos para ver todos los clientes.
                                        @else
                                            No hay clientes cargados.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="space-y-3 sm:hidden">
                    @forelse($clientes as $cliente)
                        <article class="min-w-0 rounded-card border border-[#dde2e8] bg-white p-4 shadow-sm">
                            <h4 class="break-words text-base font-semibold text-[#172538]">
                                <a href="{{ route('clientes.show', $cliente) }}" class="underline decoration-[#8c6936] underline-offset-4 focus:outline-none focus:ring-2 focus:ring-[#8c6936]">{{ $cliente->nombre }} {{ $cliente->apellido }}</a>
                            </h4>
                            <dl class="mt-3 grid grid-cols-1 gap-2 text-sm">
                                <div class="flex min-w-0 justify-between gap-3"><dt class="shrink-0 font-medium text-[#5b6470]">DNI</dt><dd class="min-w-0 break-words text-right text-[#172538]">{{ $cliente->dni }}</dd></div>
                                <div class="flex min-w-0 justify-between gap-3"><dt class="shrink-0 font-medium text-[#5b6470]">Correo</dt><dd class="min-w-0 break-all text-right text-[#172538]">{{ $cliente->correo }}</dd></div>
                                <div class="flex min-w-0 justify-between gap-3"><dt class="shrink-0 font-medium text-[#5b6470]">Teléfono</dt><dd class="min-w-0 break-words text-right text-[#172538]">{{ $cliente->telefono }}</dd></div>
                            </dl>
                            <div class="mt-4 flex flex-wrap gap-2 border-t border-[#dde2e8] pt-3">
                                @can('view', $cliente)
                                    <a href="{{ route('clientes.show', $cliente) }}" class="inline-flex min-h-11 items-center rounded-control border border-[#c7ced7] bg-white px-3 py-2 text-sm font-medium text-[#172538] focus:outline-none focus:ring-2 focus:ring-[#8c6936]">Ver</a>
                                @endcan
                                @can('update', $cliente)
                                    <a href="{{ route('clientes.edit', $cliente) }}" class="inline-flex min-h-11 items-center rounded-control bg-[#edf1f5] px-3 py-2 text-sm font-medium text-[#172538] focus:outline-none focus:ring-2 focus:ring-[#8c6936]">Editar</a>
                                @endcan
                                @can('delete', $cliente)
                                    <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas dar de baja este cliente?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex min-h-11 items-center rounded-control bg-red-50 px-3 py-2 text-sm font-medium text-red-700 focus:outline-none focus:ring-2 focus:ring-red-600">Eliminar</button>
                                    </form>
                                @endcan
                            </div>
                        </article>
                    @empty
                        <p role="status" class="rounded-card border border-[#dde2e8] bg-[#f5f6f8] px-4 py-6 text-center text-sm text-[#5b6470]">
                            @if($search !== '' || $estado !== '')
                                No encontramos clientes con esos filtros. Revisá los criterios o limpialos para ver todos los clientes.
                            @else
                                No hay clientes cargados.
                            @endif
                        </p>
                    @endforelse
                </div>

                <div class="mt-6" aria-label="Paginación de clientes">
                    {{ $clientes->links() }}
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
