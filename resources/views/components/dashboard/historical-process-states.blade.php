@php
    $today = now()->toDateString();
    $selectedPeriod = old('period', $historyPeriod);
    $selectedFrom = old('from', $historyFrom);
    $selectedTo = old('to', $historyTo);
@endphp

<section aria-labelledby="dashboard-history-title" class="flex flex-col gap-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
    <div class="flex flex-col gap-1">
        <h2 id="dashboard-history-title" class="text-lg font-semibold text-gray-900">Cambios de estado en procesos</h2>
        <p class="text-sm leading-6 text-gray-600">Cuenta procesos distintos que entraron en cada estado según la fecha de cambio de estado. Un proceso puede aparecer en varios estados, así que las cantidades no se suman como un total único.</p>
    </div>

    <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col gap-4" data-history-filter-form>
        <div class="grid min-w-0 grid-cols-1 gap-4 md:grid-cols-2">
            <div class="flex min-w-0 flex-col gap-1.5">
                <label for="history-period" class="text-sm font-medium text-gray-800">Período</label>
                <select id="history-period" name="period" data-history-period class="min-h-11 w-full min-w-0 rounded-md border-gray-300 text-base shadow-sm focus:border-indigo-600 focus:ring-indigo-600">
                    <option value="3_months" @selected($selectedPeriod === '3_months')>Últimos 3 meses</option>
                    <option value="6_months" @selected($selectedPeriod === '6_months')>Últimos 6 meses</option>
                    <option value="custom" @selected($selectedPeriod === 'custom')>Rango personalizado</option>
                </select>
            </div>

            <fieldset class="flex min-w-0 flex-col gap-2">
                <legend class="text-sm font-medium text-gray-800">Estados terminales</legend>
                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:gap-x-5">
                    <label class="inline-flex min-h-11 items-center gap-2 text-sm text-gray-800">
                        <input type="checkbox" name="include_finalizado" value="1" @checked(old('include_finalizado', $historyIncludesFinalized)) class="h-5 w-5 rounded border-gray-300 text-indigo-700 focus:ring-indigo-600">
                        <span>Incluir finalizados</span>
                    </label>
                    <label class="inline-flex min-h-11 items-center gap-2 text-sm text-gray-800">
                        <input type="checkbox" name="include_rechazado" value="1" @checked(old('include_rechazado', $historyIncludesRejected)) class="h-5 w-5 rounded border-gray-300 text-indigo-700 focus:ring-indigo-600">
                        <span>Incluir rechazados</span>
                    </label>
                </div>
                <p class="text-sm leading-5 text-gray-600">Por defecto se incluyen los demás estados.</p>
            </fieldset>
        </div>

        <div data-history-custom-range @if ($selectedPeriod !== 'custom') hidden @endif class="grid min-w-0 grid-cols-1 gap-4 md:grid-cols-2">
            <div class="flex min-w-0 flex-col gap-1.5">
                <label for="history-from" class="text-sm font-medium text-gray-800">Desde (inclusive)</label>
                <input id="history-from" name="from" type="date" value="{{ $selectedFrom }}" max="{{ $today }}" data-history-from aria-describedby="history-from-error history-client-error" class="min-h-11 w-full min-w-0 rounded-md border-gray-300 text-base shadow-sm focus:border-indigo-600 focus:ring-indigo-600">
                <p id="history-from-error" class="text-sm text-red-700" @if (! $errors->has('from')) hidden @endif>{{ $errors->first('from') }}</p>
            </div>
            <div class="flex min-w-0 flex-col gap-1.5">
                <label for="history-to" class="text-sm font-medium text-gray-800">Hasta (inclusive)</label>
                <input id="history-to" name="to" type="date" value="{{ $selectedTo }}" max="{{ $today }}" data-history-to aria-describedby="history-to-error history-client-error" class="min-h-11 w-full min-w-0 rounded-md border-gray-300 text-base shadow-sm focus:border-indigo-600 focus:ring-indigo-600">
                <p id="history-to-error" class="text-sm text-red-700" @if (! $errors->has('to')) hidden @endif>{{ $errors->first('to') }}</p>
            </div>
            <p class="text-sm leading-5 text-gray-600 md:col-span-2">Ambas fechas son obligatorias, no pueden ser futuras y Desde debe ser igual o anterior a Hasta.</p>
        </div>
        <p id="history-client-error" data-history-client-error role="alert" aria-live="polite" class="text-sm font-medium text-red-700" hidden></p>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-md bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700">Aplicar filtros</button>
            <a href="{{ route('dashboard', ['period' => '3_months']) }}" class="inline-flex min-h-11 items-center text-sm font-semibold text-indigo-700 underline decoration-indigo-300 underline-offset-4 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700">Restablecer filtros</a>
        </div>
    </form>

    @if ($errors->hasAny(['period', 'from', 'to']))
        <p class="text-sm leading-6 text-amber-800" role="status">Los cambios no se aplicaron. Los resultados mostrados conservan el último rango válido: {{ $historyPeriodLabel }}, {{ \Illuminate\Support\Carbon::parse($historyFrom)->format('d/m/Y') }} al {{ \Illuminate\Support\Carbon::parse($historyTo)->format('d/m/Y') }}. La fecha usada es la fecha de cambio de estado.</p>
    @else
        <p class="text-sm leading-6 text-gray-600">
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
                <div class="flex min-w-0 items-center justify-between gap-3 rounded-lg bg-gray-50 px-4 py-3">
                    <dt class="break-words text-sm font-medium text-gray-700">{{ $state['label'] }}</dt>
                    <dd class="shrink-0 tabular-nums text-sm font-semibold text-gray-950">{{ number_format($state['value'], 0, ',', '.') }}</dd>
                </div>
            @endforeach
        </dl>
    @else
        <p class="rounded-lg bg-gray-50 px-4 py-3 text-sm leading-6 text-gray-700" role="status">No hay cambios de estado para el período y los filtros seleccionados.</p>
    @endif
</section>
