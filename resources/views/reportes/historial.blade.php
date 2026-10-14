<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Historial del reporte</h2>
            <a href="{{ route('legajos.show', $proceso->cliente_id) }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-900">Volver al legajo</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <p class="text-sm font-medium text-gray-500">Versión actual</p>
                <p class="mt-1 text-sm text-gray-700">{{ $reporte->fecha?->format('d/m/Y') ?? 'Sin fecha' }}</p>
                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-800">{{ $reporte->contenido }}</p>
            </section>

            <section aria-labelledby="historial-ediciones" class="space-y-4">
                <h3 id="historial-ediciones" class="text-lg font-semibold text-gray-900">Versiones anteriores</h3>

                @forelse($reporte->versiones as $version)
                    <article class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <p class="text-sm font-medium text-gray-900">{{ $version->fecha_cambio->format('d/m/Y H:i') }}</p>
                            <p class="text-xs text-gray-500">Editado por {{ $version->usuario_nombre }}</p>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">Fecha del reporte: {{ $version->fecha_anterior?->format('d/m/Y') ?? 'Sin fecha' }}</p>
                        <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-700">{{ $version->contenido_anterior }}</p>
                    </article>
                @empty
                    <p class="rounded-lg bg-gray-50 px-4 py-5 text-sm text-gray-500">Este reporte todavía no tiene versiones anteriores.</p>
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>
