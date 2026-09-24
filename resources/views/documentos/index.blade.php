<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Documentación — {{ $proceso->nombre }} ({{ $proceso->cliente->apellido }}, {{ $proceso->cliente->nombre }})
            </h2>
            <a href="{{ route('procesos.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">← Volver a procesos</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-lg border-b border-gray-200 bg-white p-6 shadow-sm">
                @livewire('documentos.index', ['proceso' => $proceso])
            </div>
        </div>
    </div>
</x-app-layout>
