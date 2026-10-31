<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold leading-tight text-ink">Historial del reporte</h2>
            <a href="{{ route('legajos.procesos.show', [$proceso->cliente_id, $proceso]) }}" class="text-sm font-medium text-aequitas-900 hover:text-aequitas-800">Volver al proceso</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-card bg-white p-6 shadow-panel ring-1 ring-line">
                <p class="text-sm font-medium text-muted">Versión actual</p>
                <p class="mt-1 text-sm text-ink">{{ $reporte->fecha?->format('d/m/Y') ?? 'Sin fecha' }}</p>
                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-ink">{{ $reporte->contenido }}</p>
            </section>

            <section aria-labelledby="historial-ediciones" class="space-y-4">
                <h3 id="historial-ediciones" class="text-lg font-semibold text-ink">Versiones anteriores</h3>

                @forelse($reporte->versiones as $version)
                    <article class="rounded-card border border-line bg-white p-5 shadow-panel">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <p class="text-sm font-medium text-ink">{{ $version->fecha_cambio->format('d/m/Y H:i') }}</p>
                            <p class="text-xs text-muted">Editado por {{ $version->usuario_nombre }}</p>
                        </div>
                        <p class="mt-2 text-sm text-muted">Fecha del reporte: {{ $version->fecha_anterior?->format('d/m/Y') ?? 'Sin fecha' }}</p>
                        <p class="mt-3 whitespace-pre-line text-sm leading-6 text-ink">{{ $version->contenido_anterior }}</p>
                    </article>
                @empty
                    <p class="rounded-card bg-canvas px-4 py-5 text-sm text-muted">Este reporte todavía no tiene versiones anteriores.</p>
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>
