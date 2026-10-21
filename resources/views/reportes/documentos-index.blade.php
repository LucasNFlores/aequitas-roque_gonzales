<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-ink">Informe de documentos por categoría</h2>
            <p class="mt-1 text-sm text-muted">Resumen de cargas documentales en el período seleccionado.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-card border border-line bg-white p-5 shadow-panel sm:p-6">
                <form method="GET" action="{{ route('reportes.documentos.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                    <div>
                        <label for="fecha_desde" class="block text-sm font-medium text-muted">Fecha desde</label>
                        <input id="fecha_desde" name="fecha_desde" type="date" value="{{ old('fecha_desde', $filtros['fecha_desde']) }}" max="{{ today()->toDateString() }}" aria-describedby="fecha_desde_error" class="mt-1 block w-full rounded-control border-line shadow-sm focus:border-gold-600 focus:ring-gold-600">
                        @error('fecha_desde')
                            <p id="fecha_desde_error" class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="fecha_hasta" class="block text-sm font-medium text-muted">Fecha hasta</label>
                        <input id="fecha_hasta" name="fecha_hasta" type="date" value="{{ old('fecha_hasta', $filtros['fecha_hasta']) }}" max="{{ today()->toDateString() }}" aria-describedby="fecha_hasta_error" class="mt-1 block w-full rounded-control border-line shadow-sm focus:border-gold-600 focus:ring-gold-600">
                        @error('fecha_hasta')
                            <p id="fecha_hasta_error" class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="estado" class="block text-sm font-medium text-muted">Estado de categoría</label>
                        <select id="estado" name="estado" class="mt-1 block w-full rounded-control border-line shadow-sm focus:border-gold-600 focus:ring-gold-600">
                            <option value="todas" @selected(old('estado', $filtros['estado']) === 'todas')>Todas</option>
                            <option value="activas" @selected(old('estado', $filtros['estado']) === 'activas')>Activas</option>
                            <option value="inactivas" @selected(old('estado', $filtros['estado']) === 'inactivas')>Inactivas</option>
                        </select>
                        @error('estado')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="categoria_id" class="block text-sm font-medium text-muted">Categoría</label>
                        <select id="categoria_id" name="categoria_id" class="mt-1 block w-full rounded-control border-line shadow-sm focus:border-gold-600 focus:ring-gold-600">
                            <option value="">Todas</option>
                            @foreach ($todasCategorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected((int) old('categoria_id', $filtros['categoria_id']) === $categoria->id)>{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                        @error('categoria_id')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit" class="inline-flex flex-1 justify-center rounded-control bg-aequitas-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-aequitas-800 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">Aplicar</button>
                        <a href="{{ route('reportes.documentos.index') }}" class="inline-flex justify-center rounded-control bg-white px-4 py-2 text-sm font-semibold text-muted shadow-sm ring-1 ring-inset ring-line hover:bg-canvas">Limpiar</a>
                    </div>
                </form>

                <p class="mt-3 text-sm text-muted">Sin fechas personalizadas se usan los últimos 30 días calendario. El período manual debe incluir ambas fechas, no puede ser futuro y admite hasta 366 días.</p>
            </section>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-3" aria-label="Totales del informe">
                <article class="rounded-card border border-line bg-white p-5 shadow-panel">
                    <p class="text-sm font-medium text-muted">Documentos en el período</p>
                    <p class="mt-2 text-2xl font-semibold text-ink">{{ $totales['total_documentos'] }}</p>
                </article>
                <article class="rounded-card border border-line bg-white p-5 shadow-panel">
                    <p class="text-sm font-medium text-muted">Categorías incluidas</p>
                    <p class="mt-2 text-2xl font-semibold text-ink">{{ $totales['total_categorias'] }}</p>
                </article>
                <article class="rounded-card border border-line bg-white p-5 shadow-panel">
                    <p class="text-sm font-medium text-muted">Período consultado</p>
                    <p class="mt-2 text-lg font-semibold text-ink">{{ \Illuminate\Support\Carbon::parse($filtros['fecha_desde'])->format('d/m/Y') }} al {{ \Illuminate\Support\Carbon::parse($filtros['fecha_hasta'])->format('d/m/Y') }}</p>
                </article>
            </section>

            <section class="overflow-hidden rounded-card border border-line bg-white shadow-panel">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-6 py-4">
                    <div>
                        <h3 class="font-semibold text-ink">Documentos por categoría</h3>
                        <p class="mt-1 text-sm text-muted">La última carga corresponde al período mostrado.</p>
                    </div>
                    <a href="{{ route('reportes.documentos.pdf', request()->query()) }}" class="inline-flex items-center rounded-control bg-aequitas-900 px-4 py-2 text-sm font-semibold text-white hover:bg-aequitas-800 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">Descargar informe PDF</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-line">
                        <thead class="bg-canvas">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Categoría</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Estado</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Documentos</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted">Última carga</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line bg-white">
                            @forelse ($categorias as $categoria)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-ink">{{ $categoria->nombre }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-muted">{{ $categoria->activo ? 'Activa' : 'Inactiva' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-muted">{{ $categoria->documentos_en_periodo_count }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-muted">{{ $categoria->ultima_carga ? \Illuminate\Support\Carbon::parse($categoria->ultima_carga)->format('d/m/Y') : '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-sm text-muted">No hay categorías para los filtros seleccionados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-line px-6 py-4">
                    {{ $categorias->links() }}
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
