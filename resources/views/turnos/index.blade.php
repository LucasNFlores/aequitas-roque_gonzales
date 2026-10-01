<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Gestión de Turnos') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-b border-gray-200">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Listado de Turnos</h3>
                        <p class="text-sm text-gray-500 mt-1">Internos y de seguimiento. La cancelación conserva el historial.</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('turnos.agenda') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">Ver agenda</a>
                        @can('create', App\Models\Turno::class)
                            <a href="{{ route('turnos.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">Nuevo Turno</a>
                        @endcan
                    </div>
                </div>

                <form method="GET" action="{{ route('turnos.index') }}" class="flex flex-wrap gap-3 mb-6">
                    <select name="estado" class="border-gray-300 rounded-md text-sm">
                        <option value="">Todos los estados</option>
                        <option value="programado" @selected(request('estado') === 'programado')>Programados</option>
                        <option value="cancelado" @selected(request('estado') === 'cancelado')>Cancelados</option>
                    </select>
                    <select name="tipo" class="border-gray-300 rounded-md text-sm">
                        <option value="">Todos los tipos</option>
                        <option value="consulta_inicial" @selected(request('tipo') === 'consulta_inicial')>Consulta inicial</option>
                        <option value="seguimiento" @selected(request('tipo') === 'seguimiento')>Seguimiento</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-gray-100 rounded-md text-xs font-semibold uppercase">Filtrar</button>
                </form>

                <div class="overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">Cliente</th>
                                <th class="px-6 py-3">Profesional</th>
                                <th class="px-6 py-3">Proceso</th>
                                <th class="px-6 py-3">Tipo</th>
                                <th class="px-6 py-3">Fecha/Hora</th>
                                <th class="px-6 py-3">Estado</th>
                                <th class="px-6 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($turnos as $turno)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $turno->cliente?->apellido }}, {{ $turno->cliente?->nombre }}</td>
                                    <td class="px-6 py-4">{{ $turno->profesional?->name }}</td>
                                    <td class="px-6 py-4">{{ $turno->proceso?->nombre ?? '—' }}</td>
                                    <td class="px-6 py-4">{{ $turno->tipo }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $turno->fecha_hora?->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4">
                                        @if($turno->estado === 'cancelado')
                                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">cancelado</span>
                                        @else
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">programado</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center space-x-2">
                                        @can('view', $turno)
                                            <a href="{{ route('turnos.show', $turno) }}" class="font-medium text-gray-600 hover:text-gray-900 bg-gray-50 px-3 py-2 rounded-md transition">Ver</a>
                                        @endcan
                                        @can('update', $turno)
                                            @if($turno->estado === 'programado')
                                                <a href="{{ route('turnos.edit', $turno) }}" class="font-medium text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-2 rounded-md transition">Reprogramar</a>
                                            @endif
                                        @endcan
                                        @can('delete', $turno)
                                            @if($turno->estado === 'programado')
                                                <form action="{{ route('turnos.destroy', $turno) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Confirmás la cancelación? Se conservará en el historial y se liberará el horario.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="font-medium text-red-600 hover:text-red-900 bg-red-50 px-3 py-2 rounded-md transition">Cancelar</button>
                                                </form>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr class="bg-white"><td colspan="7" class="px-6 py-4 text-center text-gray-500 italic">No hay turnos cargados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $turnos->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
