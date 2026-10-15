<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-medium text-indigo-700">Panel de {{ $roleName ?: 'usuario interno' }}</p>
            <h1 class="text-2xl font-semibold leading-tight text-gray-900">Hola, {{ $user->name }}</h1>
            <p class="text-sm leading-6 text-gray-600">Indicadores y tareas disponibles según tus permisos.</p>
        </div>
    </x-slot>

    <main class="bg-gray-50 py-8 sm:py-10" aria-label="Dashboard operativo">
        <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 sm:px-6 lg:px-8">
            <section aria-labelledby="dashboard-indicators-title" class="flex flex-col gap-4">
                <div class="flex flex-col gap-1">
                    <h2 id="dashboard-indicators-title" class="text-lg font-semibold text-gray-900">Indicadores</h2>
                    <p class="text-sm leading-6 text-gray-600">Cada cifra respeta el alcance de datos de tu rol.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($metrics as $metric)
                        <article class="flex min-h-40 flex-col justify-between gap-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex flex-col gap-2">
                                <h3 class="text-sm font-medium text-gray-600">{{ $metric['label'] }}</h3>
                                <p class="tabular-nums text-3xl font-semibold tracking-tight text-gray-950">{{ number_format($metric['value'], 0, ',', '.') }}</p>
                                <p class="text-sm leading-6 text-gray-600">{{ $metric['description'] }}</p>
                            </div>
                            <a href="{{ $metric['href'] }}" class="inline-flex min-h-11 w-fit items-center rounded-md text-sm font-semibold text-indigo-700 underline decoration-indigo-300 underline-offset-4 transition hover:text-indigo-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700">
                                Ver detalle
                            </a>
                        </article>
                    @endforeach
                </div>
            </section>

            @if ($isSecretaryDashboard)
                <x-dashboard.process-state-chart :states="$processStates" />
            @elseif (count($processStates) > 0)
                <section aria-labelledby="dashboard-states-title" class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex flex-col gap-1">
                        <h2 id="dashboard-states-title" class="text-lg font-semibold text-gray-900">Procesos activos por estado</h2>
                        <p class="text-sm leading-6 text-gray-600">Los estados finalizados y rechazados quedan fuera de este resumen.</p>
                    </div>
                    <dl class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                        @foreach ($processStates as $state)
                            <div class="flex items-center justify-between gap-3 rounded-lg bg-gray-50 px-4 py-3">
                                <dt class="text-sm font-medium text-gray-700">{{ $state['label'] }}</dt>
                                <dd class="tabular-nums text-sm font-semibold text-gray-950">{{ number_format($state['value'], 0, ',', '.') }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endif

            <section aria-labelledby="dashboard-work-title" class="flex flex-col gap-4">
                <div class="flex flex-col gap-1">
                    <h2 id="dashboard-work-title" class="text-lg font-semibold text-gray-900">Pendientes y seguimiento</h2>
                    <p class="text-sm leading-6 text-gray-600">Las tareas se muestran solo cuando puedes actuar sobre ellas.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    @foreach ($queues as $queue)
                        <article class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex flex-col gap-1">
                                    <h3 class="text-base font-semibold text-gray-900">{{ $queue['title'] }}</h3>
                                    <p class="text-sm leading-6 text-gray-600">{{ $queue['description'] }}</p>
                                </div>
                                <span class="inline-flex min-w-10 items-center justify-center rounded-full bg-indigo-50 px-3 py-1 text-sm font-semibold text-indigo-800" aria-label="{{ $queue['count'] }} casos">{{ number_format($queue['count'], 0, ',', '.') }}</span>
                            </div>

                            @if ($queue['items']->isNotEmpty())
                                <ul class="flex flex-col divide-y divide-gray-100" aria-label="{{ $queue['title'] }}">
                                    @foreach ($queue['items'] as $process)
                                        <li class="flex flex-col gap-1 py-3 first:pt-0 last:pb-0">
                                            <span class="text-sm font-medium text-gray-900">{{ $process->nombre }}</span>
                                            <span class="text-sm text-gray-600">{{ $process->cliente?->nombre }} {{ $process->cliente?->apellido }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="rounded-lg bg-gray-50 px-4 py-3 text-sm leading-6 text-gray-700" role="status">No hay casos en esta lista.</p>
                            @endif

                            <a href="{{ $queue['href'] }}" class="inline-flex min-h-11 w-fit items-center rounded-md text-sm font-semibold text-indigo-700 underline decoration-indigo-300 underline-offset-4 transition hover:text-indigo-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700">
                                Abrir bandeja de procesos
                            </a>
                        </article>
                    @endforeach

                    @if ($appointments !== null)
                        <article class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex flex-col gap-1">
                                    <h3 class="text-base font-semibold text-gray-900">Próximos turnos</h3>
                                    <p class="text-sm leading-6 text-gray-600">Hasta {{ now()->addDays(7)->format('d/m/Y') }}.</p>
                                </div>
                                <span class="inline-flex min-w-10 items-center justify-center rounded-full bg-indigo-50 px-3 py-1 text-sm font-semibold text-indigo-800" aria-label="{{ $appointments['count'] }} turnos">{{ number_format($appointments['count'], 0, ',', '.') }}</span>
                            </div>

                            @if ($appointments['items']->isNotEmpty())
                                <ul class="flex flex-col divide-y divide-gray-100" aria-label="Turnos programados">
                                    @foreach ($appointments['items'] as $appointment)
                                        <li class="flex flex-col gap-1 py-3 first:pt-0 last:pb-0">
                                            <span class="text-sm font-medium text-gray-900">{{ $appointment->cliente?->nombre }} {{ $appointment->cliente?->apellido }}</span>
                                            <time class="text-sm text-gray-600" datetime="{{ $appointment->fecha_hora->toIso8601String() }}">{{ $appointment->fecha_hora->format('d/m/Y H:i') }}</time>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="rounded-lg bg-gray-50 px-4 py-3 text-sm leading-6 text-gray-700" role="status">No tienes turnos programados en los próximos siete días.</p>
                            @endif

                            <a href="{{ $appointments['href'] }}" class="inline-flex min-h-11 w-fit items-center rounded-md text-sm font-semibold text-indigo-700 underline decoration-indigo-300 underline-offset-4 transition hover:text-indigo-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700">Abrir agenda</a>
                        </article>
                    @endif

                    @if (count($queues) === 0 && $appointments === null)
                        <article class="flex flex-col gap-4 rounded-xl border border-dashed border-gray-300 bg-white p-5 sm:p-6">
                            <div class="flex flex-col gap-1">
                                <h3 class="text-base font-semibold text-gray-900">No hay pendientes operativos para este rol</h3>
                                <p class="text-sm leading-6 text-gray-600">Puedes consultar los indicadores y entrar a las áreas de administración autorizadas.</p>
                            </div>
                            @if (count($managementLinks) > 0)
                                <nav aria-label="Accesos de administración" class="flex flex-wrap gap-x-5 gap-y-2">
                                    @foreach ($managementLinks as $link)
                                        <a href="{{ $link['href'] }}" class="inline-flex min-h-11 items-center rounded-md text-sm font-semibold text-indigo-700 underline decoration-indigo-300 underline-offset-4 transition hover:text-indigo-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700">{{ $link['label'] }}</a>
                                    @endforeach
                                </nav>
                            @endif
                        </article>
                    @endif
                </div>
            </section>
        </div>
    </main>
</x-app-layout>
