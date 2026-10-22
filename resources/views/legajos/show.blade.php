<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-aequitas-900">Legajo jurídico</p>
                <h1 class="mt-1 text-xl font-semibold leading-tight text-ink">{{ $cliente->apellido }}, {{ $cliente->nombre }}</h1>
            </div>
            <a href="{{ route('legajos.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-control border border-line bg-white px-4 py-2 text-sm font-medium text-ink shadow-panel transition hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">
                Volver a la búsqueda
            </a>
        </div>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="rounded-control border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('success') }}</div>
            @endif

            <section class="rounded-panel border border-line bg-white p-5 shadow-panel sm:p-7">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-aequitas-900">Ficha de identidad y contacto</p>
                        <h2 class="mt-1 text-lg font-semibold text-ink">Datos del cliente</h2>
                        <p class="mt-1 text-sm text-muted">Información asociada a los procesos visibles para tu rol.</p>
                    </div>
                    <span class="inline-flex w-fit items-center rounded-full bg-aequitas-50 px-3 py-1 text-xs font-semibold text-aequitas-900">{{ $procesos->count() }} {{ $procesos->count() === 1 ? 'proceso visible' : 'procesos visibles' }}</span>
                </div>

                <dl class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">DNI</dt><dd class="mt-1 text-sm text-ink">{{ $cliente->dni }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Teléfono</dt><dd class="mt-1 text-sm text-ink">{{ $cliente->telefono ?: 'Sin teléfono registrado' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Correo</dt><dd class="mt-1 break-all text-sm text-ink">{{ $cliente->correo ?: 'Sin correo registrado' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Domicilio</dt><dd class="mt-1 text-sm text-ink">{{ $cliente->domicilio ?: 'Sin domicilio registrado' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Fecha de nacimiento</dt><dd class="mt-1 text-sm text-ink">{{ $cliente->fecha_nacimiento?->format('d/m/Y') ?? 'Sin fecha registrada' }}</dd></div>
                </dl>
            </section>

            <section aria-labelledby="procesos-visibles" class="space-y-4">
                <div>
                    <h2 id="procesos-visibles" class="text-lg font-semibold text-ink">Procesos del legajo</h2>
                    <p class="mt-1 text-sm text-muted">Seleccioná un proceso para consultar su actividad y documentación.</p>
                </div>

                @forelse($procesos as $proceso)
                    <article class="overflow-hidden rounded-panel border border-line bg-white shadow-panel transition hover:border-aequitas-200 hover:shadow-lg">
                        <a href="{{ route('legajos.procesos.show', [$cliente, $proceso]) }}" class="group block rounded-panel border-l-4 border-l-gold-600 p-5 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-gold-600 sm:p-6">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-aequitas-900">{{ $proceso->servicio?->nombre ?? 'Sin servicio' }} · {{ $proceso->tipo }}</p>
                                    <h3 class="mt-1 text-lg font-semibold text-ink group-hover:text-aequitas-900">{{ $proceso->nombre }}</h3>
                                </div>
                                <span class="inline-flex w-fit items-center rounded-full bg-gold-50 px-3 py-1 text-sm font-semibold text-gold-700 ring-1 ring-inset ring-gold-100">{{ str_replace('_', ' ', ucfirst($proceso->estado)) }}</span>
                            </div>

                            <p class="mt-3 max-w-4xl text-sm leading-6 text-muted">{{ filled($proceso->descripcion) ? \Illuminate\Support\Str::limit($proceso->descripcion, 180) : 'Sin descripción registrada.' }}</p>

                            <dl class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Inicio</dt><dd class="mt-1 text-sm text-ink">{{ $proceso->fecha_inicio?->format('d/m/Y') ?? 'Sin fecha' }}</dd></div>
                                <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Coordinador</dt><dd class="mt-1 text-sm text-ink">{{ $proceso->coordinador?->nombre_completo ?? 'Sin asignar' }}</dd></div>
                                <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Profesional</dt><dd class="mt-1 text-sm text-ink">{{ $proceso->profesional?->nombre_completo ?? 'Sin asignar' }}</dd></div>
                                <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Honorarios</dt><dd class="mt-1 text-sm text-ink">{{ $proceso->honorarios !== null ? '$ '.number_format((float) $proceso->honorarios, 2, ',', '.') : 'Sin definir' }}</dd></div>
                            </dl>

                            <span class="mt-5 inline-flex min-h-11 items-center gap-2 rounded-control bg-aequitas-900 px-4 py-2 text-sm font-semibold text-white transition group-hover:bg-aequitas-800">
                                Abrir detalle del proceso <span aria-hidden="true">→</span>
                            </span>
                        </a>
                    </article>
                @empty
                    <div class="rounded-panel border border-dashed border-line bg-white px-6 py-12 text-center">
                        <h3 class="text-lg font-semibold text-ink">No hay procesos visibles</h3>
                        <p class="mt-2 text-sm text-muted">Este cliente no tiene procesos disponibles para tu alcance.</p>
                    </div>
                @endforelse
            </section>

            @if($turnosSinProceso->isNotEmpty())
                <section class="rounded-panel border border-line bg-white p-5 shadow-panel sm:p-7">
                    <h2 class="text-base font-semibold text-ink">Turnos del cliente sin proceso asociado</h2>
                    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                        @foreach($turnosSinProceso as $turno)
                            <div class="rounded-card border border-line p-4">
                                <div class="flex items-start justify-between gap-3"><p class="font-medium text-ink">{{ str_replace('_', ' ', ucfirst($turno->tipo)) }}</p><span class="rounded-full bg-aequitas-50 px-2.5 py-1 text-xs font-medium text-ink">{{ ucfirst($turno->estado) }}</span></div>
                                <p class="mt-2 text-sm text-ink">{{ $turno->fecha_hora?->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>
                                <p class="mt-1 text-sm text-muted">{{ $turno->profesional?->nombre_completo ?? $turno->coordinador?->nombre_completo ?? 'Sin responsable' }}</p>
                                @if($turno->es_externo && $turno->detalle_externo)<p class="mt-2 text-sm text-muted">{{ $turno->detalle_externo }}</p>@endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($user->can('ver_comprobantes_pago'))
                <section class="rounded-panel border border-line bg-white p-5 shadow-panel sm:p-7">
                    <h2 class="text-base font-semibold text-ink">Comprobantes del cliente sin proceso asociado</h2>
                    <div class="mt-4 space-y-3">
                        @forelse($comprobantes->whereNull('proceso_id') as $comprobante)
                            <div class="flex flex-col gap-3 rounded-card border border-line p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div><p class="font-medium text-ink">{{ $comprobante->descripcion ?: 'Comprobante de pago' }}</p><p class="mt-1 text-sm text-muted">{{ $comprobante->fecha_subida?->format('d/m/Y') ?? 'Sin fecha' }}</p></div>
                                @can('view', $comprobante)<a href="{{ route('comprobantes.show', $comprobante) }}" class="inline-flex min-h-11 w-fit items-center justify-center rounded-control bg-aequitas-50 px-3 py-2 text-xs font-semibold text-aequitas-900 hover:bg-aequitas-100">Ver comprobante</a>@endcan
                            </div>
                        @empty
                            <p class="rounded-card bg-canvas px-4 py-5 text-sm text-muted">No hay comprobantes vinculados directamente al cliente.</p>
                        @endforelse
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
