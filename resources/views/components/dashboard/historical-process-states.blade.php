@php
    $today = now()->toDateString();
    $selectedPeriod = old('period', $historyPeriod);
    $selectedFrom = old('from', $historyFrom);
    $selectedTo = old('to', $historyTo);
    $selectedStateSlugs = old('states', old('states_filter_present') === '1' ? [] : $historySelectedStates);
    $selectedStateSlugs = is_array($selectedStateSlugs) ? $selectedStateSlugs : [];
@endphp

<section aria-labelledby="dashboard-history-title" class="flex flex-col gap-5 rounded-panel border border-line bg-white p-5 shadow-panel sm:p-6">
    <div class="flex flex-col gap-1">
        <h2 id="dashboard-history-title" class="text-lg font-semibold text-ink">Cambios de estado en procesos</h2>
        <p class="text-sm leading-6 text-muted">Cuenta procesos distintos que entraron en cada estado según la fecha de cambio de estado. Un proceso puede aparecer en varios estados, así que las cantidades no se suman como un total único.</p>
    </div>

    <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col gap-4" data-history-filter-form>
        <input type="hidden" name="states_filter_present" value="1">
        <div class="grid min-w-0 grid-cols-1 gap-4 md:grid-cols-2">
            <div class="flex min-w-0 flex-col gap-1.5">
                <label for="history-period" class="text-sm font-medium text-ink">Período</label>
                <select id="history-period" name="period" data-history-period class="min-h-11 w-full min-w-0 rounded-control border-line text-base shadow-sm focus:border-gold-600 focus:ring-gold-600">
                    <option value="3_months" @selected($selectedPeriod === '3_months')>Últimos 3 meses</option>
                    <option value="6_months" @selected($selectedPeriod === '6_months')>Últimos 6 meses</option>
                    <option value="custom" @selected($selectedPeriod === 'custom')>Rango personalizado</option>
                </select>
            </div>

            <fieldset class="flex min-w-0 flex-col gap-2 md:col-span-2" aria-describedby="history-states-help history-states-error">
                <legend class="text-sm font-medium text-ink">Estados incluidos</legend>
                <p id="history-states-help" class="text-sm leading-5 text-muted">Por defecto se incluyen todos los estados, también finalizados y rechazados.</p>
                <div class="flex flex-wrap gap-2">
                    <button type="button" data-select-all-history-states class="inline-flex min-h-11 items-center rounded-control border border-line px-3 text-sm font-medium text-ink hover:bg-canvas focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-600">Seleccionar todos</button>
                    <button type="button" data-clear-history-states class="inline-flex min-h-11 items-center rounded-control border border-line px-3 text-sm font-medium text-ink hover:bg-canvas focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-600">Quitar todos</button>
                </div>
                <div class="grid min-w-0 grid-cols-2 gap-x-2 gap-y-1 sm:grid-cols-3">
                    @foreach ($historyStateOptions as $state)
                        <label class="inline-flex min-h-11 min-w-0 items-center gap-2 rounded-control px-2 text-sm text-ink hover:bg-canvas">
                            <input type="checkbox" name="states[]" value="{{ $state['slug'] }}" data-history-state @checked(in_array($state['slug'], $selectedStateSlugs, true)) class="h-5 w-5 shrink-0 rounded border-line text-aequitas-900 focus:ring-gold-600">
                            <span class="break-words">{{ $state['label'] }}</span>
                        </label>
                    @endforeach
                </div>
                <p id="history-states-error" class="text-sm text-negative" @if (! $errors->has('states') && ! $errors->has('states.*')) hidden @endif>{{ $errors->first('states') ?: $errors->first('states.*') }}</p>
            </fieldset>
        </div>

        <div data-history-custom-range @if ($selectedPeriod !== 'custom') hidden @endif class="grid min-w-0 grid-cols-1 gap-4 md:grid-cols-2">
            <div class="flex min-w-0 flex-col gap-1.5">
                <label for="history-from" class="text-sm font-medium text-ink">Desde (inclusive)</label>
                <input id="history-from" name="from" type="date" value="{{ $selectedFrom }}" max="{{ $today }}" data-history-from aria-describedby="history-from-error history-client-error" class="min-h-11 w-full min-w-0 rounded-control border-line text-base shadow-sm focus:border-gold-600 focus:ring-gold-600">
                <p id="history-from-error" class="text-sm text-negative" @if (! $errors->has('from')) hidden @endif>{{ $errors->first('from') }}</p>
            </div>
            <div class="flex min-w-0 flex-col gap-1.5">
                <label for="history-to" class="text-sm font-medium text-ink">Hasta (inclusive)</label>
                <input id="history-to" name="to" type="date" value="{{ $selectedTo }}" max="{{ $today }}" data-history-to aria-describedby="history-to-error history-client-error" class="min-h-11 w-full min-w-0 rounded-control border-line text-base shadow-sm focus:border-gold-600 focus:ring-gold-600">
                <p id="history-to-error" class="text-sm text-negative" @if (! $errors->has('to')) hidden @endif>{{ $errors->first('to') }}</p>
            </div>
            <p class="text-sm leading-5 text-muted md:col-span-2">Ambas fechas son obligatorias, no pueden ser futuras y Desde debe ser igual o anterior a Hasta.</p>
        </div>
        <p id="history-client-error" data-history-client-error role="alert" aria-live="polite" class="text-sm font-medium text-negative" hidden></p>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-control bg-aequitas-900 px-4 py-2 text-sm font-semibold text-white shadow-panel transition hover:bg-aequitas-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-600">Aplicar filtros</button>
            <a href="{{ route('dashboard', ['period' => '3_months']) }}" class="inline-flex min-h-11 items-center text-sm font-semibold text-aequitas-900 underline decoration-gold-600 underline-offset-4 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-600">Restablecer filtros</a>
        </div>
    </form>

    @if ($errors->hasAny(['period', 'from', 'to', 'states', 'states.*', 'states_filter_present']))
        <p class="text-sm leading-6 text-gold-700" role="status">Los cambios no se aplicaron. Los resultados mostrados conservan los filtros válidos anteriores: {{ $historyPeriodLabel }}, {{ \Illuminate\Support\Carbon::parse($historyFrom)->format('d/m/Y') }} al {{ \Illuminate\Support\Carbon::parse($historyTo)->format('d/m/Y') }}. La fecha usada es la fecha de cambio de estado.</p>
    @else
        <p class="text-sm leading-6 text-muted">
            {{ $historyPeriodLabel }}: {{ \Illuminate\Support\Carbon::parse($historyFrom)->format('d/m/Y') }} al {{ \Illuminate\Support\Carbon::parse($historyTo)->format('d/m/Y') }}.
            La fecha usada es la fecha de cambio de estado.
        </p>
    @endif

    @if (count($historicalProcessStates) > 0)
        <div data-process-state-chart-container class="h-64 w-full min-w-0 sm:h-72">
            <canvas
                data-horizontal-bar-chart
                data-historical-state-chart
                data-labels="{{ json_encode(array_column($historicalProcessStates, 'label'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}"
                data-values="{{ json_encode(array_column($historicalProcessStates, 'value')) }}"
                aria-hidden="true"
            ></canvas>
        </div>
        <dl aria-label="Procesos distintos por estado durante el período seleccionado" class="grid min-w-0 grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($historicalProcessStates as $state)
                <div class="flex min-w-0 items-center justify-between gap-3 rounded-card bg-canvas px-4 py-3">
                    <dt class="break-words text-sm font-medium text-ink">{{ $state['label'] }}</dt>
                    <dd class="shrink-0 tabular-nums text-sm font-semibold text-ink">{{ number_format($state['value'], 0, ',', '.') }}</dd>
                </div>
            @endforeach
        </dl>
    @else
        <p class="rounded-card bg-canvas px-4 py-3 text-sm leading-6 text-ink" role="status">No hay cambios de estado para el período y los filtros seleccionados.</p>
    @endif
</section>
