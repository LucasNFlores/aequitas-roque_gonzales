@props([
    'states',
    'title' => 'Procesos activos por estado',
    'scopeDescription' => 'Los estados finalizados y rechazados quedan fuera de este resumen.',
    'emptyMessage' => 'No hay procesos activos para mostrar.',
    'minimumChartItems' => 2,
    'valuesLabel' => 'Valores de procesos activos por estado',
])

<section aria-labelledby="dashboard-states-title" class="flex flex-col gap-4 rounded-panel border border-line bg-white p-5 shadow-panel sm:p-6">
    <div class="flex flex-col gap-1">
        <h2 id="dashboard-states-title" class="text-lg font-semibold text-ink">{{ $title }}</h2>
        <p class="text-sm leading-6 text-muted">{{ $scopeDescription }}</p>
    </div>

    @if (count($states) >= $minimumChartItems)
        <div data-process-state-chart-container class="h-64 w-full sm:h-72">
            <canvas
                data-process-state-chart
                data-horizontal-bar-chart
                data-labels="{{ json_encode(array_column($states, 'label'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}"
                data-values="{{ json_encode(array_column($states, 'value')) }}"
                aria-hidden="true"
            ></canvas>
        </div>
    @endif

    @if (count($states) > 0)
        <dl aria-label="{{ $valuesLabel }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($states as $state)
                <div class="flex items-center justify-between gap-3 rounded-card bg-canvas px-4 py-3">
                    <dt class="text-sm font-medium text-ink">{{ $state['label'] }}</dt>
                    <dd class="tabular-nums text-sm font-semibold text-ink">{{ number_format($state['value'], 0, ',', '.') }}</dd>
                </div>
            @endforeach
        </dl>
    @else
        <p class="rounded-card bg-canvas px-4 py-3 text-sm leading-6 text-ink" role="status">{{ $emptyMessage }}</p>
    @endif
</section>
