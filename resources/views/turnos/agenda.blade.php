<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Agenda') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-b border-gray-200">
                <form method="GET" action="{{ route('turnos.agenda') }}" class="flex flex-wrap gap-3 mb-6">
                    @if(!auth()->user()->hasRole('Profesional'))
                        <select name="profesional_id" class="border-gray-300 rounded-md text-sm">
                            <option value="">Todos los profesionales</option>
                            @foreach($profesionales as $profesional)
                                <option value="{{ $profesional->id }}" @selected((int) $profesionalId === (int) $profesional->id)>{{ $profesional->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <input type="date" name="desde" value="{{ request('desde') }}" class="border-gray-300 rounded-md text-sm" />
                    <input type="date" name="hasta" value="{{ request('hasta') }}" class="border-gray-300 rounded-md text-sm" />
                    <button type="submit" class="px-4 py-2 bg-gray-100 rounded-md text-xs font-semibold uppercase">Filtrar</button>
                </form>

                <div class="overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr><th class="px-6 py-3">Fecha/Hora</th><th class="px-6 py-3">Cliente</th><th class="px-6 py-3">Profesional</th><th class="px-6 py-3">Tipo</th><th class="px-6 py-3">Estado</th></tr>
                        </thead>
                        <tbody>
                            @forelse($turnos as $turno)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $turno->fecha_hora?->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4">{{ $turno->cliente?->apellido }}, {{ $turno->cliente?->nombre }}</td>
                                    <td class="px-6 py-4">{{ $turno->profesional?->name }}</td>
                                    <td class="px-6 py-4">{{ $turno->tipo }}</td>
                                    <td class="px-6 py-4">{{ $turno->estado }}</td>
                                </tr>
                            @empty
                                <tr class="bg-white"><td colspan="5" class="px-6 py-4 text-center italic">Sin turnos programados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $turnos->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
