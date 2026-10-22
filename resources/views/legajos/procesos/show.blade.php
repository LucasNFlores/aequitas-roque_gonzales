<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <nav aria-label="Ruta de navegación" class="text-xs font-medium text-muted">
                    <a href="{{ route('legajos.show', $cliente) }}" class="hover:text-aequitas-900">Legajos</a>
                    <span aria-hidden="true" class="mx-1">›</span>
                    <a href="{{ route('legajos.show', $cliente) }}" class="hover:text-aequitas-900">{{ $cliente->apellido }}, {{ $cliente->nombre }}</a>
                    <span aria-hidden="true" class="mx-1">›</span>
                    <span class="text-ink">Exp. {{ $proceso->id }}-{{ strtoupper($proceso->tipo) }}</span>
                </nav>
                <h1 class="mt-2 text-xl font-semibold leading-tight text-ink">Detalle del proceso</h1>
            </div>
            <a href="{{ route('legajos.show', $cliente) }}" class="inline-flex min-h-11 items-center justify-center rounded-control border border-line bg-white px-4 py-2 text-sm font-medium text-ink shadow-panel transition hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">Volver al legajo</a>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="rounded-control border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('success') }}</div>
            @endif

            <article class="overflow-hidden rounded-panel border border-line bg-white shadow-panel">
                <header class="border-b border-line bg-white p-5 sm:p-7">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="inline-flex rounded-control bg-aequitas-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-aequitas-900">Exp. {{ $proceso->id }}-{{ strtoupper($proceso->tipo) }}</p>
                            <h2 class="mt-3 text-2xl font-semibold leading-snug text-ink sm:text-3xl">{{ $proceso->nombre }}</h2>
                            <p class="mt-2 text-sm text-muted">Cliente: <span class="font-semibold text-ink">{{ $cliente->apellido }}, {{ $cliente->nombre }}</span> · Servicio: {{ $proceso->servicio?->nombre ?? 'Sin asignar' }}</p>
                            <div class="mt-5 rounded-card border border-line bg-canvas p-4">
                                <h3 class="text-xs font-semibold uppercase tracking-wide text-muted">Objeto del proceso</h3>
                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-ink">{{ $proceso->descripcion ?: 'Sin descripción registrada.' }}</p>
                            </div>
                        </div>

                        <div class="w-full shrink-0 xl:max-w-sm">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-xs font-semibold uppercase tracking-wide text-muted">Estado actual</span>
                                <span class="rounded-full bg-gold-50 px-3 py-1 text-sm font-semibold text-gold-700 ring-1 ring-inset ring-gold-100">{{ str_replace('_', ' ', ucfirst($proceso->estado)) }}</span>
                            </div>
                            @can('updateState', $proceso)
                                <form method="POST" action="{{ route('procesos.estado.update', $proceso) }}" class="mt-3 space-y-3 rounded-card border border-line bg-canvas p-4">
                                    @csrf
                                    @method('PATCH')
                                    <label for="estado" class="block text-xs font-semibold uppercase tracking-wide text-muted">Cambiar fase del caso</label>
                                    <div class="flex flex-col gap-2 sm:flex-row">
                                        <select id="estado" name="estado" required class="min-h-11 flex-1 rounded-control border-line bg-white text-sm shadow-panel focus:border-gold-600 focus:ring-gold-600">
                                            <option value="">Seleccionar estado</option>
                                            @foreach($estados as $estado)
                                                <option value="{{ $estado->slug }}" @selected(old('estado', $proceso->estado) === $estado->slug)>{{ $estado->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-control bg-aequitas-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-aequitas-800 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">Guardar</button>
                                    </div>
                                    <label for="motivo" class="block text-xs font-medium text-muted">Motivo del cambio <span class="font-normal">(opcional)</span></label>
                                    <textarea id="motivo" name="motivo" rows="2" maxlength="5000" class="block w-full rounded-control border-line bg-white text-sm shadow-panel focus:border-gold-600 focus:ring-gold-600">{{ old('motivo') }}</textarea>
                                    @error('estado')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
                                    @error('motivo')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
                                </form>
                            @endcan
                        </div>
                    </div>

                    <dl class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-card border border-line bg-canvas p-4"><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Tipo de proceso</dt><dd class="mt-1 font-medium text-ink">{{ $proceso->tipo }}</dd></div>
                        <div class="rounded-card border border-line bg-canvas p-4"><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Modalidad de servicio</dt><dd class="mt-1 font-medium text-ink">{{ $proceso->servicio?->nombre ?? 'Sin asignar' }}</dd></div>
                        <div class="rounded-card border border-line bg-canvas p-4"><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Fecha de inicio</dt><dd class="mt-1 font-medium text-ink">{{ $proceso->fecha_inicio?->format('d/m/Y') ?? 'Sin fecha' }}</dd></div>
                        <div class="rounded-card border border-line bg-canvas p-4"><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Honorarios pactados</dt><dd class="mt-1 font-medium text-ink">{{ $proceso->honorarios !== null ? '$ '.number_format((float) $proceso->honorarios, 2, ',', '.') : 'Sin definir' }}</dd></div>
                    </dl>

                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="flex items-center gap-3 rounded-card border border-line p-4"><span aria-hidden="true" class="grid h-10 w-10 place-items-center rounded-full bg-aequitas-50 text-sm font-semibold text-aequitas-900">{{ mb_substr($proceso->coordinador?->nombre_completo ?? '—', 0, 1) }}</span><div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Coordinador responsable</dt><dd class="mt-1 text-sm font-medium text-ink">{{ $proceso->coordinador?->nombre_completo ?? 'Sin asignar' }}</dd></div></div>
                        <div class="flex items-center gap-3 rounded-card border border-line p-4"><span aria-hidden="true" class="grid h-10 w-10 place-items-center rounded-full bg-gold-50 text-sm font-semibold text-gold-700">{{ mb_substr($proceso->profesional?->nombre_completo ?? '—', 0, 1) }}</span><div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Profesional asignado</dt><dd class="mt-1 text-sm font-medium text-ink">{{ $proceso->profesional?->nombre_completo ?? 'Sin asignar' }}</dd></div></div>
                    </div>

                    @if($proceso->motivo_rechazo)
                        <div class="mt-3 rounded-card border border-red-200 bg-red-50 p-4"><h3 class="text-xs font-semibold uppercase tracking-wide text-red-800">Motivo de rechazo</h3><p class="mt-1 whitespace-pre-line text-sm text-red-900">{{ $proceso->motivo_rechazo }}</p></div>
                    @endif
                </header>
            </article>

            <section class="rounded-panel border border-line bg-white p-5 shadow-panel sm:p-7">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div><h2 class="text-lg font-semibold text-ink">Datos maestros del cliente</h2><p class="mt-1 text-sm text-muted">Identidad legal y datos de contacto.</p></div>
                    @can('update', $cliente)
                        <a href="{{ route('clientes.edit', $cliente) }}" class="inline-flex min-h-11 items-center justify-center rounded-control border border-line bg-white px-4 py-2 text-sm font-semibold text-ink transition hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">Editar datos del cliente</a>
                    @endcan
                </div>
                <dl class="mt-5 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Nombre y apellido</dt><dd class="mt-1 text-sm font-medium text-ink">{{ $cliente->nombre }} {{ $cliente->apellido }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">DNI / CUIL</dt><dd class="mt-1 text-sm font-medium text-ink">{{ $cliente->dni }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Fecha de nacimiento</dt><dd class="mt-1 text-sm font-medium text-ink">{{ $cliente->fecha_nacimiento?->format('d/m/Y') ?? 'Sin fecha registrada' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Teléfono principal</dt><dd class="mt-1 text-sm font-medium text-ink">{{ $cliente->telefono ?: 'Sin teléfono registrado' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Correo electrónico</dt><dd class="mt-1 break-all text-sm font-medium text-ink">{{ $cliente->correo ?: 'Sin correo registrado' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-muted">Domicilio</dt><dd class="mt-1 text-sm font-medium text-ink">{{ $cliente->domicilio ?: 'Sin domicilio registrado' }}</dd></div>
                </dl>
            </section>

            <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1.55fr)_minmax(18rem,0.9fr)]">
                <section aria-labelledby="turnos-proceso" class="rounded-panel border border-line bg-white p-5 shadow-panel sm:p-7">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div><h2 id="turnos-proceso" class="text-lg font-semibold text-ink">Turnos relacionados</h2><p class="mt-1 text-sm text-muted">Citas y audiencias vinculadas al proceso.</p></div>
                        @can('create', \App\Models\Turno::class)
                            <a href="{{ route('turnos.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-control bg-aequitas-900 px-4 py-2 text-sm font-semibold text-white hover:bg-aequitas-800">Agendar nuevo turno</a>
                        @endcan
                    </div>
                    <div class="mt-5 space-y-3">
                        @forelse($proceso->turnos as $turno)
                            <article class="rounded-card border border-line border-l-4 border-l-aequitas-900 bg-canvas p-4">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div><h3 class="font-semibold text-ink">{{ str_replace('_', ' ', ucfirst($turno->tipo)) }}</h3><p class="mt-1 text-sm text-muted">{{ $turno->fecha_hora?->format('d/m/Y H:i') ?? 'Sin fecha' }} · {{ $turno->profesional?->nombre_completo ?? $turno->coordinador?->nombre_completo ?? 'Sin responsable' }}</p></div>
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-ink ring-1 ring-inset ring-line">{{ ucfirst($turno->estado) }}</span>
                                </div>
                                @if($turno->es_externo && $turno->detalle_externo)<p class="mt-3 text-sm text-muted">{{ $turno->detalle_externo }}</p>@endif
                                @can('view', $turno)<a href="{{ route('turnos.show', $turno) }}" class="mt-3 inline-flex min-h-11 items-center text-sm font-semibold text-aequitas-900 underline-offset-4 hover:underline">Ver turno</a>@endcan
                                @can('update', $turno)<a href="{{ route($turno->es_externo ? 'turnos.externos.edit' : 'turnos.edit', $turno) }}" class="ml-4 inline-flex min-h-11 items-center text-sm font-semibold text-aequitas-900 underline-offset-4 hover:underline">Modificar</a>@endcan
                            </article>
                        @empty
                            <p class="rounded-card bg-canvas px-4 py-6 text-sm text-muted">Este proceso todavía no tiene turnos vinculados.</p>
                        @endforelse
                    </div>
                </section>

                <section aria-labelledby="historial-proceso" class="rounded-panel border border-line bg-white p-5 shadow-panel sm:p-7">
                    <div><h2 id="historial-proceso" class="text-lg font-semibold text-ink">Historial de estados</h2><p class="mt-1 text-sm text-muted">Trazabilidad y auditoría de fases.</p></div>
                    <ol class="mt-5 space-y-4 border-l-2 border-line pl-4">
                        @forelse($proceso->historialEstados as $historial)
                            <li class="relative"><span aria-hidden="true" class="absolute -left-[1.36rem] top-1 h-3 w-3 rounded-full bg-aequitas-900 ring-4 ring-white"></span><p class="text-xs font-semibold uppercase tracking-wide text-aequitas-900">{{ str_replace('_', ' ', ucfirst($historial->estado_nuevo)) }}</p><p class="mt-1 text-xs text-muted">{{ $historial->fecha_cambio?->format('d/m/Y H:i') ?? '—' }}</p><p class="mt-2 text-sm text-ink">{{ $historial->usuario?->nombre_completo ?? 'Sistema' }}</p>@if($historial->motivo)<p class="mt-1 whitespace-pre-line text-sm text-muted">{{ $historial->motivo }}</p>@endif</li>
                        @empty
                            <li class="text-sm text-muted">Este proceso todavía no tiene cambios de estado.</li>
                        @endforelse
                    </ol>
                </section>
            </div>

            <section aria-labelledby="reportes-proceso" class="rounded-panel border border-line bg-white p-5 shadow-panel sm:p-7">
                <div><h2 id="reportes-proceso" class="text-lg font-semibold text-ink">Actuaciones y reportes</h2><p class="mt-1 text-sm text-muted">Bitácora cronológica de gestiones y actuaciones procesales.</p></div>
                @can('create', \App\Models\Reporte::class)
                    <form method="POST" action="{{ route('procesos.reportes.store', $proceso) }}" class="mt-5 space-y-3 rounded-card border border-line bg-canvas p-4">
                        @csrf
                        <input type="hidden" name="proceso_id" value="{{ $proceso->id }}" />
                        <label for="reporte-contenido" class="block text-sm font-semibold text-ink">Registrar nueva actuación judicial o acta</label>
                        <textarea id="reporte-contenido" name="contenido" rows="4" maxlength="20000" required class="block w-full rounded-control border-line bg-white text-sm shadow-panel focus:border-gold-600 focus:ring-gold-600" placeholder="Describí la actuación o el avance del proceso.">{{ old('contenido') }}</textarea>
                        @error('contenido')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <label for="reporte-fecha" class="grid gap-1 text-xs font-medium text-muted">Fecha de actuación<input id="reporte-fecha" type="date" name="fecha" value="{{ old('fecha', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required class="min-h-11 rounded-control border-line bg-white text-sm shadow-panel focus:border-gold-600 focus:ring-gold-600" /></label>
                            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-control bg-aequitas-900 px-4 py-2 text-sm font-semibold text-white hover:bg-aequitas-800">Guardar actuación</button>
                        </div>
                    </form>
                @endcan
                <div class="mt-5 space-y-3">
                    @forelse($proceso->reportes as $reporte)
                        <article class="rounded-card border border-line bg-canvas p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div><p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ $reporte->profesional?->nombre_completo ?? 'Profesional' }} · {{ $reporte->fecha?->format('d/m/Y') ?? 'Sin fecha' }}</p><p class="mt-3 whitespace-pre-line text-sm leading-6 text-ink">{{ $reporte->contenido }}</p></div>
                                <div class="flex flex-wrap gap-2">
                                    @can('view', $reporte)<a href="{{ route('procesos.reportes.historial', [$proceso, $reporte]) }}" class="inline-flex min-h-11 items-center rounded-control bg-white px-3 py-2 text-xs font-semibold text-aequitas-900 ring-1 ring-inset ring-line">Ver historial</a>@endcan
                                    @can('update', $reporte)
                                        <details class="rounded-control bg-white px-3 py-2 text-xs font-semibold text-ink ring-1 ring-inset ring-line"><summary class="min-h-7 cursor-pointer">Editar</summary><form method="POST" action="{{ route('procesos.reportes.update', [$proceso, $reporte]) }}" class="mt-3 grid min-w-64 gap-3">@csrf @method('PUT')<label class="grid gap-1 text-xs font-medium text-muted">Fecha<input type="date" name="fecha" value="{{ $reporte->fecha?->format('Y-m-d') }}" max="{{ now()->toDateString() }}" required class="rounded-control border-line text-sm shadow-panel focus:border-gold-600 focus:ring-gold-600" /></label><label class="grid gap-1 text-xs font-medium text-muted">Contenido<textarea name="contenido" rows="4" maxlength="20000" required class="rounded-control border-line text-sm shadow-panel focus:border-gold-600 focus:ring-gold-600">{{ $reporte->contenido }}</textarea></label><button type="submit" class="min-h-11 rounded-control bg-aequitas-900 px-3 py-2 text-xs font-semibold text-white hover:bg-aequitas-800">Guardar cambios</button></form></details>
                                    @endcan
                                    @can('delete', $reporte)<form method="POST" action="{{ route('procesos.reportes.destroy', [$proceso, $reporte]) }}">@csrf @method('DELETE')<button type="submit" class="min-h-11 rounded-control bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100">Eliminar</button></form>@endcan
                                </div>
                            </div>
                        </article>
                    @empty
                        <p class="rounded-card bg-canvas px-4 py-6 text-sm text-muted">No hay reportes visibles para este proceso.</p>
                    @endforelse
                </div>
            </section>

            <section aria-labelledby="documentos-proceso" class="rounded-panel border border-line bg-white p-5 shadow-panel sm:p-7">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div><h2 id="documentos-proceso" class="text-lg font-semibold text-ink">Documentación e historial de versiones</h2><p class="mt-1 text-sm text-muted">Metadatos documentales con acceso a archivos controlado por separado.</p></div>
                    @can('viewAny', \App\Models\Documento::class)
                        <a href="{{ route('procesos.documentos.index', $proceso) }}" class="inline-flex min-h-11 items-center justify-center rounded-control bg-aequitas-900 px-4 py-2 text-sm font-semibold text-white hover:bg-aequitas-800">@can('create', \App\Models\Documento::class)Gestionar documentos @else Consultar documentos @endcan</a>
                    @endcan
                </div>
                <div class="mt-5 space-y-3">
                    @forelse($proceso->documentos as $documento)
                        <article class="flex flex-col gap-4 rounded-card border border-line p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0"><h3 class="break-words font-semibold text-ink">{{ $documento->nombre }}</h3><p class="mt-1 text-sm text-muted">{{ $documento->categoria?->nombre ?? 'Categoría histórica' }} · {{ $documento->tipo_documento }}</p><p class="mt-1 text-xs text-muted">Cargado el {{ $documento->created_at?->format('d/m/Y') ?? '—' }} · {{ $documento->versiones->count() }} versiones anteriores</p></div>
                            <div class="flex flex-wrap gap-2">
                                @can('view', $documento)<a href="{{ route('procesos.documentos.show', [$proceso, $documento]) }}" class="inline-flex min-h-11 items-center rounded-control bg-aequitas-50 px-3 py-2 text-xs font-semibold text-aequitas-900">Visualizar</a>@endcan
                                @can('download', $documento)<a href="{{ route('procesos.documentos.download', [$proceso, $documento]) }}" class="inline-flex min-h-11 items-center rounded-control bg-aequitas-50 px-3 py-2 text-xs font-semibold text-ink">Descargar</a>@endcan
                                @can('update', $documento)<a href="{{ route('procesos.documentos.index', $proceso) }}" class="inline-flex min-h-11 items-center rounded-control border border-line px-3 py-2 text-xs font-semibold text-ink">Reemplazar</a>@endcan
                            </div>
                            @if($documento->versiones->isNotEmpty())
                                <details class="w-full border-t border-line pt-3"><summary class="min-h-11 cursor-pointer text-sm font-medium text-aequitas-900">Historial de versiones</summary><ul class="mt-2 space-y-2">@foreach($documento->versiones as $version)<li class="flex flex-col gap-2 rounded-control bg-canvas p-3 text-sm sm:flex-row sm:items-center sm:justify-between"><span class="text-ink">{{ $version->nombre }} · {{ $version->categoria_nombre ?? $version->categoria?->nombre ?? 'Categoría histórica' }} · {{ $version->fecha_reemplazo?->format('d/m/Y H:i') ?? '—' }} · {{ $version->usuario?->nombre_completo ?? 'Usuario anterior' }}</span><div class="flex gap-3">@can('view', $documento)<a href="{{ route('procesos.documentos.versiones.show', [$proceso, $documento, $version]) }}" class="min-h-11 inline-flex items-center font-semibold text-aequitas-900">Ver</a>@endcan @can('download', $documento)<a href="{{ route('procesos.documentos.versiones.download', [$proceso, $documento, $version]) }}" class="min-h-11 inline-flex items-center font-semibold text-aequitas-900">Descargar</a>@endcan</div></li>@endforeach</ul></details>
                            @endif
                        </article>
                    @empty
                        <p class="rounded-card bg-canvas px-4 py-6 text-sm text-muted">No hay documentos vinculados a este proceso.</p>
                    @endforelse
                </div>
            </section>

            @if($user->can('ver_comprobantes_pago'))
                <section aria-labelledby="comprobantes-proceso" class="rounded-panel border border-line bg-white p-5 shadow-panel sm:p-7">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"><div><h2 id="comprobantes-proceso" class="text-lg font-semibold text-ink">Comprobantes de pago y facturación ARCA</h2><p class="mt-1 text-sm text-muted">Facturas electrónicas y trazabilidad de cobranzas.</p></div>@can('registrar_comprobantes_pago')<a href="{{ route('comprobantes.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-control bg-aequitas-900 px-4 py-2 text-sm font-semibold text-white hover:bg-aequitas-800">Registrar comprobante de pago</a>@endcan</div>
                    <div class="mt-5 grid grid-cols-1 gap-3 md:grid-cols-2">
                        @forelse($comprobantes as $comprobante)
                            <article class="rounded-card border border-line p-4"><div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"><div><h3 class="font-semibold text-ink">{{ $comprobante->descripcion ?: 'Comprobante de pago' }}</h3><p class="mt-2 text-sm text-muted">Subido el {{ $comprobante->fecha_subida?->format('d/m/Y') ?? 'Sin fecha' }} · Proceso {{ $proceso->nombre }}</p></div>@can('view', $comprobante)<a href="{{ route('comprobantes.show', $comprobante) }}" class="inline-flex min-h-11 items-center text-sm font-semibold text-aequitas-900 underline-offset-4 hover:underline">Ver comprobante</a>@endcan</div></article>
                        @empty
                            <p class="rounded-card bg-canvas px-4 py-6 text-sm text-muted md:col-span-2">Este proceso todavía no tiene comprobantes vinculados.</p>
                        @endforelse
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
