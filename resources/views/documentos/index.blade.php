<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-ink">
                Documentación — {{ $proceso->nombre }} ({{ $proceso->cliente->apellido }}, {{ $proceso->cliente->nombre }})
            </h2>
            <a href="{{ route('procesos.index') }}" class="text-sm font-medium text-aequitas-900 hover:text-aequitas-900">← Volver a procesos</a>
        </div>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-panel border border-line bg-white p-4 shadow-panel sm:p-6">
                @livewire('documentos.index', ['proceso' => $proceso])
            </div>
        </div>
    </div>
</x-app-layout>
