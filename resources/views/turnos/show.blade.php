<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Detalle de Turno') }} #{{ $turno->id }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">{{ session('success') }}</div>
                @endif
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><p class="text-sm font-medium text-gray-500">Cliente</p><p class="text-base text-gray-900">{{ $turno->cliente?->apellido }}, {{ $turno->cliente?->nombre }}</p></div>
                        <div><p class="text-sm font-medium text-gray-500">Profesional</p><p class="text-base text-gray-900">{{ $turno->profesional?->name }}</p></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><p class="text-sm font-medium text-gray-500">Proceso</p><p class="text-base text-gray-900">{{ $turno->proceso?->nombre ?? '—' }} ({{ $turno->proceso?->estado ?? 'sin proceso' }})</p></div>
                        <div><p class="text-sm font-medium text-gray-500">Tipo</p><p class="text-base text-gray-900">{{ $turno->tipo }}</p></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><p class="text-sm font-medium text-gray-500">Fecha/Hora</p><p class="text-base text-gray-900">{{ $turno->fecha_hora?->format('d/m/Y H:i') }}</p></div>
                        <div><p class="text-sm font-medium text-gray-500">Estado</p><p class="text-base text-gray-900">{{ $turno->estado }}</p></div>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6 mt-6 flex space-x-3">
                    @can('update', $turno)
                        @if($turno->estado === 'programado')
                            <a href="{{ route('turnos.edit', $turno) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md text-xs font-semibold uppercase hover:bg-indigo-700 transition">Reprogramar</a>
                        @endif
                    @endcan
                    @can('delete', $turno)
                        @if($turno->estado === 'programado')
                            <form action="{{ route('turnos.destroy', $turno) }}" method="POST" onsubmit="return confirm('¿Confirmás la cancelación? Se conservará en el historial.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md text-xs font-semibold uppercase hover:bg-red-700 transition">Cancelar turno</button>
                            </form>
                        @endif
                    @endcan
                    <a href="{{ route('turnos.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase hover:bg-gray-50 transition">Volver</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
