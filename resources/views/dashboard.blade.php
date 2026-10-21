<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-semibold text-gold-700">Panel de {{ $roleName ?: 'usuario interno' }}</p>
            <h1 class="text-2xl font-semibold leading-tight text-ink">Hola, {{ $user->name }}</h1>
            <p class="text-sm leading-6 text-muted">
                {{ $isAdministratorDashboard
                    ? 'Panorama global y tareas operativas disponibles para la administración.'
                    : ($isDirectivoDashboard ? 'Resumen estratégico de datos autorizados para CU 40.' : 'Indicadores y tareas disponibles según tus permisos.') }}
            </p>
        </div>
    </x-slot>

    <div class="bg-canvas py-8 sm:py-10">
        <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 sm:px-6 lg:px-8">
            <section aria-labelledby="dashboard-indicators-title" class="flex flex-col gap-4">
                <div class="flex flex-col gap-1">
                    <h2 id="dashboard-indicators-title" class="text-lg font-semibold text-ink">
                        {{ $isAdministratorDashboard
                            ? 'Panorama operativo global'
                            : ($isDirectivoDashboard ? 'Resumen de gestión' : ($isCoordinatorDashboard ? 'Indicadores de gestión' : 'Indicadores')) }}
                    </h2>
                    <p class="text-sm leading-6 text-muted">Cada cifra respeta el alcance de datos de tu rol.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 {{ $isAdministratorDashboard ? 'xl:grid-cols-3' : ($isCoordinatorDashboard ? 'xl:grid-cols-2' : 'xl:grid-cols-4') }}">
                    @foreach ($metrics as $metric)
                        <article class="flex min-h-40 flex-col justify-between gap-5 rounded-panel border border-line bg-white p-5 shadow-panel">
                            <div class="flex flex-col gap-2">
                                <h3 class="text-sm font-medium text-muted">{{ $metric['label'] }}</h3>
                                <p class="tabular-nums text-3xl font-semibold tracking-tight text-ink">{{ number_format($metric['value'], 0, ',', '.') }}</p>
                                <p class="text-sm leading-6 text-muted">{{ $metric['description'] }}</p>
                            </div>
                            <a href="{{ $metric['href'] }}" class="inline-flex min-h-11 w-fit items-center rounded-control text-sm font-semibold text-aequitas-900 underline decoration-gold-600 underline-offset-4 transition hover:text-aequitas-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-600">
                                Ver detalle
                            </a>
                        </article>
                    @endforeach
                </div>
            </section>

            @if ($isDirectivoDashboard)
                <x-dashboard.process-state-chart
                    title="Procesos activos por servicio"
                    :states="$activeProcessesByService"
                    scope-description="Distribución al corte actual; se excluyen procesos finalizados y rechazados."
                    empty-message="No hay procesos activos vigentes para mostrar por servicio."
                    values-label="Valores de procesos activos por servicio"
                    :minimum-chart-items="1"
                />
            @elseif ($isAdministratorDashboard)
                <x-dashboard.process-state-chart
                    title="Procesos activos globales por estado"
                    :states="$processStates"
                    scope-description="Al corte actual en todo el sistema; se excluyen procesos finalizados y rechazados."
                    empty-message="No hay procesos activos globales para mostrar."
                    values-label="Valores globales de procesos activos por estado"
                    :minimum-chart-items="1"
                />
            @elseif ($isSecretaryDashboard || $isProfessionalDashboard || $isCoordinatorDashboard)
                <x-dashboard.process-state-chart
                    :states="$processStates"
                    :scope-description="$isProfessionalDashboard
                        ? 'Solo procesos asignados actualmente a tu usuario; los finalizados y rechazados quedan fuera.'
                        : 'Todos los procesos visibles para tu rol, excepto los finalizados y rechazados.'"
                />
            @elseif (count($processStates) > 0)
                <section aria-labelledby="dashboard-states-title" class="flex flex-col gap-4 rounded-panel border border-line bg-white p-5 shadow-panel sm:p-6">
                    <div class="flex flex-col gap-1">
                        <h2 id="dashboard-states-title" class="text-lg font-semibold text-ink">Procesos activos por estado</h2>
                        <p class="text-sm leading-6 text-muted">Los estados finalizados y rechazados quedan fuera de este resumen.</p>
                    </div>
                    <dl class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                        @foreach ($processStates as $state)
                            <div class="flex items-center justify-between gap-3 rounded-card bg-canvas px-4 py-3">
                                <dt class="text-sm font-medium text-ink">{{ $state['label'] }}</dt>
                                <dd class="tabular-nums text-sm font-semibold text-ink">{{ number_format($state['value'], 0, ',', '.') }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endif

            @if ($isDirectivoDashboard || $isAdministratorDashboard)
                <x-dashboard.historical-process-states
                    :historical-process-states="$historicalProcessStates"
                    :history-period="$historyPeriod"
                    :history-period-label="$historyPeriodLabel"
                    :history-from="$historyFrom"
                    :history-to="$historyTo"
                    :history-state-options="$historyStateOptions"
                    :history-selected-states="$historySelectedStates"
                />
            @endif

            @if (! $isDirectivoDashboard)
                <section aria-labelledby="dashboard-work-title" class="flex flex-col gap-4">
                <div class="flex flex-col gap-1">
                    <h2 id="dashboard-work-title" class="text-lg font-semibold text-ink">{{ $isAdministratorDashboard ? 'Gestión operativa' : 'Pendientes y seguimiento' }}</h2>
                    <p class="text-sm leading-6 text-muted">
                        {{ $isAdministratorDashboard
                            ? 'Colas de admisión y asignación, junto con los turnos próximos.'
                            : 'Las tareas se muestran solo cuando puedes actuar sobre ellas.' }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    @foreach ($queues as $queue)
                        <article class="flex flex-col gap-4 rounded-panel border border-line bg-white p-5 shadow-panel sm:p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex flex-col gap-1">
                                    <h3 class="text-base font-semibold text-ink">{{ $queue['title'] }}</h3>
                                    <p class="text-sm leading-6 text-muted">{{ $queue['description'] }}</p>
                                </div>
                                <span class="inline-flex min-w-10 items-center justify-center rounded-full bg-gold-50 px-3 py-1 text-sm font-semibold text-aequitas-900" aria-label="{{ $queue['count'] }} casos">{{ number_format($queue['count'], 0, ',', '.') }}</span>
                            </div>

                            @if ($queue['items']->isNotEmpty())
                                <ul class="flex flex-col divide-y divide-line" aria-label="{{ $queue['title'] }}">
                                    @foreach ($queue['items'] as $process)
                                        <li class="flex flex-col gap-1 py-3 first:pt-0 last:pb-0">
                                            <span class="text-sm font-medium text-ink">{{ $process->nombre }}</span>
                                            <span class="text-sm text-muted">{{ $process->cliente?->nombre }} {{ $process->cliente?->apellido }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="rounded-card bg-canvas px-4 py-3 text-sm leading-6 text-ink" role="status">No hay casos en esta lista.</p>
                            @endif

                            <a href="{{ $queue['href'] }}" class="inline-flex min-h-11 w-fit items-center rounded-control text-sm font-semibold text-aequitas-900 underline decoration-gold-600 underline-offset-4 transition hover:text-aequitas-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-600">
                                Abrir bandeja de procesos
                            </a>
                        </article>
                    @endforeach

                    @if ($appointments !== null)
                        <article class="flex flex-col gap-4 rounded-panel border border-line bg-white p-5 shadow-panel sm:p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex flex-col gap-1">
                                    <h3 class="text-base font-semibold text-ink">Próximos turnos</h3>
                                    <p class="text-sm leading-6 text-muted">Hasta {{ now()->addDays(7)->format('d/m/Y') }}.</p>
                                </div>
                                <span class="inline-flex min-w-10 items-center justify-center rounded-full bg-gold-50 px-3 py-1 text-sm font-semibold text-aequitas-900" aria-label="{{ $appointments['count'] }} turnos">{{ number_format($appointments['count'], 0, ',', '.') }}</span>
                            </div>

                            @if ($appointments['items']->isNotEmpty())
                                <ul class="flex flex-col divide-y divide-line" aria-label="Turnos programados">
                                    @foreach ($appointments['items'] as $appointment)
                                        <li class="flex flex-col gap-1 py-3 first:pt-0 last:pb-0">
                                            <span class="text-sm font-medium text-ink">{{ $appointment->cliente?->nombre }} {{ $appointment->cliente?->apellido }}</span>
                                            <time class="text-sm text-muted" datetime="{{ $appointment->fecha_hora->toIso8601String() }}">{{ $appointment->fecha_hora->format('d/m/Y H:i') }}</time>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="rounded-card bg-canvas px-4 py-3 text-sm leading-6 text-ink" role="status">No tienes turnos programados en los próximos siete días.</p>
                            @endif

                            <a href="{{ $appointments['href'] }}" class="inline-flex min-h-11 w-fit items-center rounded-control text-sm font-semibold text-aequitas-900 underline decoration-gold-600 underline-offset-4 transition hover:text-aequitas-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-600">Abrir agenda</a>
                        </article>
                    @endif

                    @if (count($queues) === 0 && $appointments === null)
                        <article class="flex flex-col gap-4 rounded-panel border border-dashed border-line bg-white p-5 sm:p-6">
                            <div class="flex flex-col gap-1">
                                <h3 class="text-base font-semibold text-ink">No hay pendientes operativos para este rol</h3>
                                <p class="text-sm leading-6 text-muted">Puedes consultar los indicadores y entrar a las áreas de administración autorizadas.</p>
                            </div>
                            @if (count($managementLinks) > 0)
                                <nav aria-label="Accesos de administración" class="flex flex-wrap gap-x-5 gap-y-2">
                                    @foreach ($managementLinks as $link)
                                        <a href="{{ $link['href'] }}" class="inline-flex min-h-11 items-center rounded-control text-sm font-semibold text-aequitas-900 underline decoration-gold-600 underline-offset-4 transition hover:text-aequitas-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-600">{{ $link['label'] }}</a>
                                    @endforeach
                                </nav>
                            @endif
                        </article>
                    @endif
                </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
