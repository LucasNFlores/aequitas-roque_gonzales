<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-aequitas-900">Legajo jurídico</p>
                <h1 class="mt-1 text-xl font-semibold leading-tight text-ink">{{ $cliente->apellido }}, {{ $cliente->nombre }}</h1>
            </div>
            <a href="{{ route('legajos.index') }}" class="inline-flex items-center justify-center rounded-control border border-line bg-white min-h-11 px-4 py-2 text-sm font-medium text-ink shadow-panel transition hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">
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
                        <h2 class="text-lg font-semibold text-ink">Datos del cliente</h2>
                        <p class="mt-1 text-sm text-muted">Información de contacto asociada a los procesos visibles para tu rol.</p>
                    </div>
                    <span class="inline-flex w-fit items-center rounded-full bg-aequitas-50 px-3 py-1 text-xs font-semibold text-aequitas-900">{{ $procesos->count() }} {{ $procesos->count() === 1 ? 'proceso visible' : 'procesos visibles' }}</span>
                </div>

                <dl class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">DNI</dt>
                        <dd class="mt-1 text-sm text-ink">{{ $cliente->dni }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Teléfono</dt>
                        <dd class="mt-1 text-sm text-ink">{{ $cliente->telefono ?: 'Sin teléfono registrado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Correo</dt>
                        <dd class="mt-1 break-all text-sm text-ink">{{ $cliente->correo ?: 'Sin correo registrado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Domicilio</dt>
                        <dd class="mt-1 text-sm text-ink">{{ $cliente->domicilio ?: 'Sin domicilio registrado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Fecha de nacimiento</dt>
                        <dd class="mt-1 text-sm text-ink">{{ $cliente->fecha_nacimiento?->format('d/m/Y') ?? 'Sin fecha registrada' }}</dd>
                    </div>
                </dl>
            </section>

            @forelse($procesos as $proceso)
                <article class="overflow-hidden rounded-panel border border-line border-l-4 border-l-gold-600 bg-white shadow-panel" wire:key="legajo-proceso-{{ $proceso->id }}">
                    <header class="border-b border-line bg-canvas px-5 py-5 sm:px-7">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-aequitas-900">{{ $proceso->servicio?->nombre ?? 'Sin servicio' }} · {{ $proceso->tipo }}</p>
                                <h2 class="mt-1 text-xl font-semibold text-ink">{{ $proceso->nombre }}</h2>
                                <p class="mt-2 max-w-3xl whitespace-pre-line text-sm leading-6 text-muted">{{ $proceso->descripcion ?: 'Sin descripción registrada.' }}</p>
                            </div>
                            <span class="inline-flex w-fit items-center rounded-full bg-gold-50 px-3 py-1 text-sm font-semibold text-gold-700 ring-1 ring-inset ring-gold-100">{{ str_replace('_', ' ', ucfirst($proceso->estado)) }}</span>
                        </div>

                        <dl class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Inicio</dt>
                                <dd class="mt-1 text-sm text-ink">{{ $proceso->fecha_inicio?->format('d/m/Y') ?? 'Sin fecha' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Coordinador</dt>
                                <dd class="mt-1 text-sm text-ink">{{ $proceso->coordinador?->name ?? 'Sin asignar' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Profesional</dt>
                                <dd class="mt-1 text-sm text-ink">{{ $proceso->profesional?->name ?? 'Sin asignar' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Honorarios</dt>
                                <dd class="mt-1 text-sm text-ink">{{ $proceso->honorarios !== null ? '$ '.number_format((float) $proceso->honorarios, 2, ',', '.') : 'Sin definir' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Motivo de rechazo</dt>
                                <dd class="mt-1 whitespace-pre-line text-sm text-ink">{{ $proceso->motivo_rechazo ?: '—' }}</dd>
                            </div>
                        </dl>
                    </header>

                    <div class="space-y-8 px-5 py-6 sm:px-7">
                        <section aria-labelledby="turnos-proceso-{{ $proceso->id }}">
                            <div class="flex items-baseline justify-between gap-4">
                                <div>
                                    <h3 id="turnos-proceso-{{ $proceso->id }}" class="text-base font-semibold text-ink">Turnos relacionados</h3>
                                    <p class="mt-1 text-sm text-muted">Turnos vinculados a este proceso.</p>
                                </div>
                            </div>
                            <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                                @forelse($proceso->turnos as $turno)
                                    <div class="rounded-card border border-line p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <p class="font-medium text-ink">{{ str_replace('_', ' ', ucfirst($turno->tipo)) }}</p>
                                            <span class="rounded-full bg-aequitas-50 px-2.5 py-1 text-xs font-medium text-ink">{{ ucfirst($turno->estado) }}</span>
                                        </div>
                                        <p class="mt-2 text-sm text-ink">{{ $turno->fecha_hora?->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>
                                        <p class="mt-1 text-sm text-muted">{{ $turno->profesional?->name ?? $turno->coordinador?->name ?? 'Sin responsable' }}</p>
                                        @if($turno->es_externo && $turno->detalle_externo)
                                            <p class="mt-2 text-sm text-muted">{{ $turno->detalle_externo }}</p>
                                        @endif
                                    </div>
                                @empty
                                    <p class="rounded-card bg-canvas px-4 py-5 text-sm text-muted md:col-span-2">Este proceso todavía no tiene turnos vinculados.</p>
                                @endforelse
                            </div>
                        </section>

                        <section aria-labelledby="documentos-proceso-{{ $proceso->id }}" class="border-t border-line pt-7">
                            <div>
                                <h3 id="documentos-proceso-{{ $proceso->id }}" class="text-base font-semibold text-ink">Documentos</h3>
                                <p class="mt-1 text-sm text-muted">Categoría, tipo e historial de versiones. El acceso a los archivos se controla por separado.</p>
                            </div>
                            <div class="mt-4 space-y-3">
                                @forelse($proceso->documentos as $documento)
                                    <div class="rounded-card border border-line p-4">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <p class="font-medium text-ink">{{ $documento->nombre }}</p>
                                                <p class="mt-1 text-sm text-muted">Categoría: {{ $documento->categoria?->nombre ?? 'Categoría histórica' }} · Tipo: {{ $documento->tipo_documento }}</p>
                                                <p class="mt-1 text-xs text-muted">Cargado el {{ $documento->created_at?->format('d/m/Y') ?? '—' }}</p>
                                            </div>
                                            <div class="flex flex-wrap gap-2">
                                                @can('view', $documento)
                                                    <a href="{{ route('procesos.documentos.show', [$proceso, $documento]) }}" class="rounded-control bg-aequitas-50 min-h-11 px-3 py-2 text-xs font-semibold text-aequitas-900 hover:bg-aequitas-100">Ver PDF</a>
                                                @endcan
                                                @can('download', $documento)
                                                    <a href="{{ route('procesos.documentos.download', [$proceso, $documento]) }}" class="rounded-control bg-aequitas-50 min-h-11 px-3 py-2 text-xs font-semibold text-ink hover:bg-aequitas-100">Descargar</a>
                                                @endcan
                                            </div>
                                        </div>

                                        @if($documento->versiones->isNotEmpty())
                                            <details class="mt-4 border-t border-line pt-3">
                                                <summary class="cursor-pointer text-sm font-medium text-aequitas-900">Historial · {{ $documento->versiones->count() }} {{ $documento->versiones->count() === 1 ? 'versión anterior' : 'versiones anteriores' }}</summary>
                                                <ul class="mt-3 space-y-3">
                                                    @foreach($documento->versiones as $version)
                                                        <li class="flex flex-col gap-3 rounded-control bg-canvas p-3 sm:flex-row sm:items-center sm:justify-between" wire:key="legajo-documento-version-{{ $version->id }}">
                                                            <div>
                                                                <p class="text-sm font-medium text-ink">{{ $version->nombre }} · {{ $version->categoria_nombre ?: $version->categoria?->nombre ?: 'Sin categoría' }}</p>
                                                                <p class="mt-1 text-xs text-muted">{{ $version->tipo_documento }} · Reemplazado {{ $version->fecha_reemplazo?->format('d/m/Y H:i') ?? '—' }} · {{ $version->usuario?->name ?? 'Usuario eliminado' }}</p>
                                                            </div>
                                                            <div class="flex flex-wrap gap-2">
                                                                @can('view', $documento)
                                                                    <a href="{{ route('procesos.documentos.versiones.show', [$proceso, $documento, $version]) }}" class="rounded-control bg-white min-h-11 px-3 py-2 text-xs font-semibold text-aequitas-900 ring-1 ring-inset ring-line hover:bg-aequitas-50">Ver versión</a>
                                                                @endcan
                                                                @can('download', $documento)
                                                                    <a href="{{ route('procesos.documentos.versiones.download', [$proceso, $documento, $version]) }}" class="rounded-control bg-white min-h-11 px-3 py-2 text-xs font-semibold text-ink ring-1 ring-inset ring-line hover:bg-aequitas-50">Descargar versión</a>
                                                                @endcan
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </details>
                                        @else
                                            <p class="mt-3 text-xs text-muted">Sin versiones anteriores.</p>
                                        @endif
                                    </div>
                                @empty
                                    <p class="rounded-card bg-canvas px-4 py-5 text-sm text-muted">Este proceso todavía no tiene documentos.</p>
                                @endforelse
                            </div>
                        </section>

                        <section aria-labelledby="reportes-proceso-{{ $proceso->id }}" class="border-t border-line pt-7">
                            <div>
                                <h3 id="reportes-proceso-{{ $proceso->id }}" class="text-base font-semibold text-ink">Reportes</h3>
                                <p class="mt-1 text-sm text-muted">Reuniones, avances y acciones registradas en el proceso.</p>
                            </div>

                            @can('createFor', $proceso)
                                <details class="mt-4 rounded-card border border-aequitas-100 bg-aequitas-50/50 p-4">
                                    <summary class="cursor-pointer font-medium text-aequitas-900">Registrar reporte</summary>
                                    <form method="POST" action="{{ route('procesos.reportes.store', $proceso) }}" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        @csrf
                                        <input type="hidden" name="proceso_id" value="{{ $proceso->id }}" />
                                        <div>
                                            <label for="reporte-fecha-{{ $proceso->id }}" class="block text-sm font-medium text-ink">Fecha</label>
                                            <input id="reporte-fecha-{{ $proceso->id }}" type="date" name="fecha" max="{{ now()->toDateString() }}" required class="mt-1 block w-full rounded-control border-line shadow-panel focus:border-gold-600 focus:ring-gold-600" />
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label for="reporte-contenido-{{ $proceso->id }}" class="block text-sm font-medium text-ink">Contenido</label>
                                            <textarea id="reporte-contenido-{{ $proceso->id }}" name="contenido" rows="4" maxlength="20000" required class="mt-1 block w-full rounded-control border-line shadow-panel focus:border-gold-600 focus:ring-gold-600"></textarea>
                                        </div>
                                        <div class="sm:col-span-2 sm:text-right">
                                            <button type="submit" class="inline-flex items-center justify-center rounded-control bg-aequitas-900 min-h-11 px-4 py-2 text-sm font-semibold text-white hover:bg-aequitas-800 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">Guardar reporte</button>
                                        </div>
                                    </form>
                                </details>
                            @endcan

                            <div class="mt-4 space-y-4">
                                @forelse($proceso->reportes as $reporte)
                                    <article class="rounded-card border border-line p-4" wire:key="legajo-reporte-{{ $reporte->id }}">
                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <p class="text-sm font-semibold text-ink">{{ $reporte->fecha?->format('d/m/Y') ?? 'Sin fecha' }}</p>
                                                <p class="mt-1 text-xs text-muted">Autor: {{ $reporte->profesional?->name ?? 'Usuario eliminado' }}@if($reporte->registrado_por_id && $reporte->registrado_por_id !== $reporte->profesional_id) · Registrado por {{ $reporte->registradoPor?->name ?? 'Usuario eliminado' }}@endif</p>
                                            </div>
                                            <div class="flex flex-wrap gap-2">
                                                @can('view', $reporte)
                                                    <a href="{{ route('procesos.reportes.historial', [$proceso, $reporte]) }}" class="rounded-control bg-aequitas-50 min-h-11 px-3 py-2 text-xs font-semibold text-aequitas-900 hover:bg-aequitas-100">Ver historial</a>
                                                @endcan
                                                @can('update', $reporte)
                                                    <details class="rounded-control bg-aequitas-50 min-h-11 px-3 py-2 text-xs font-semibold text-ink">
                                                        <summary class="cursor-pointer">Editar</summary>
                                                        <form method="POST" action="{{ route('procesos.reportes.update', [$proceso, $reporte]) }}" class="mt-3 grid min-w-64 gap-3">
                                                            @csrf
                                                            @method('PUT')
                                                            <label class="grid gap-1 text-xs font-medium text-muted">Fecha
                                                                <input type="date" name="fecha" value="{{ $reporte->fecha?->format('Y-m-d') }}" max="{{ now()->toDateString() }}" required class="rounded-control border-line text-sm shadow-panel focus:border-gold-600 focus:ring-gold-600" />
                                                            </label>
                                                            <label class="grid gap-1 text-xs font-medium text-muted">Contenido
                                                                <textarea name="contenido" rows="4" maxlength="20000" required class="rounded-control border-line text-sm shadow-panel focus:border-gold-600 focus:ring-gold-600">{{ $reporte->contenido }}</textarea>
                                                            </label>
                                                            <button type="submit" class="rounded-control bg-aequitas-900 min-h-11 px-3 py-2 text-xs font-semibold text-white hover:bg-aequitas-800">Guardar cambios</button>
                                                        </form>
                                                    </details>
                                                @endcan
                                                @can('delete', $reporte)
                                                    <form method="POST" action="{{ route('procesos.reportes.destroy', [$proceso, $reporte]) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="rounded-control bg-red-50 min-h-11 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100">Eliminar</button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </div>
                                        <p class="mt-4 whitespace-pre-line text-sm leading-6 text-ink">{{ $reporte->contenido }}</p>
                                    </article>
                                @empty
                                    <p class="rounded-card bg-canvas px-4 py-5 text-sm text-muted">No hay reportes visibles para este proceso.</p>
                                @endforelse
                            </div>
                        </section>

                        @if($user->can('ver_comprobantes_pago'))
                            <section aria-labelledby="comprobantes-proceso-{{ $proceso->id }}" class="border-t border-line pt-7">
                                <h3 id="comprobantes-proceso-{{ $proceso->id }}" class="text-base font-semibold text-ink">Comprobantes y pagos</h3>
                                <div class="mt-4 space-y-3">
                                    @forelse($comprobantes->where('proceso_id', $proceso->id) as $comprobante)
                                        <div class="flex flex-col gap-3 rounded-card border border-line p-4 sm:flex-row sm:items-center sm:justify-between" wire:key="legajo-comprobante-{{ $comprobante->id }}">
                                            <div>
                                                <p class="font-medium text-ink">{{ $comprobante->descripcion ?: 'Comprobante de pago' }}</p>
                                                <p class="mt-1 text-sm text-muted">{{ $comprobante->fecha_subida?->format('d/m/Y') ?? 'Sin fecha' }} · {{ $comprobante->proceso?->nombre ?? 'Vinculado al cliente' }}</p>
                                            </div>
                                            @can('view', $comprobante)
                                                <a href="{{ route('comprobantes.show', $comprobante) }}" class="inline-flex w-fit items-center justify-center rounded-control bg-aequitas-50 min-h-11 px-3 py-2 text-xs font-semibold text-aequitas-900 hover:bg-aequitas-100">Ver comprobante</a>
                                            @endcan
                                        </div>
                                    @empty
                                        <p class="rounded-card bg-canvas px-4 py-5 text-sm text-muted">Este proceso todavía no tiene comprobantes vinculados.</p>
                                    @endforelse
                                </div>
                            </section>
                        @endif

                        <section aria-labelledby="historial-proceso-{{ $proceso->id }}" class="border-t border-line pt-7">
                            <h3 id="historial-proceso-{{ $proceso->id }}" class="text-base font-semibold text-ink">Historial de estados</h3>
                            <div class="mt-4 overflow-x-auto rounded-card border border-line">
                                <table class="w-full min-w-[650px] text-left text-sm text-muted">
                                    <thead class="bg-canvas text-xs uppercase tracking-wide text-ink">
                                        <tr>
                                            <th scope="col" class="px-4 py-3">Estado anterior</th>
                                            <th scope="col" class="px-4 py-3">Estado nuevo</th>
                                            <th scope="col" class="px-4 py-3">Fecha</th>
                                            <th scope="col" class="px-4 py-3">Usuario</th>
                                            <th scope="col" class="px-4 py-3">Motivo</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line bg-white">
                                        @forelse($proceso->historialEstados as $historial)
                                            <tr wire:key="legajo-historial-{{ $historial->id }}">
                                                <td class="px-4 py-3">{{ $historial->estado_anterior ? str_replace('_', ' ', ucfirst($historial->estado_anterior)) : '—' }}</td>
                                                <td class="px-4 py-3 font-medium text-ink">{{ str_replace('_', ' ', ucfirst($historial->estado_nuevo)) }}</td>
                                                <td class="px-4 py-3">{{ $historial->fecha_cambio?->format('d/m/Y H:i') ?? '—' }}</td>
                                                <td class="px-4 py-3">{{ $historial->usuario?->name ?? 'Sistema' }}</td>
                                                <td class="px-4 py-3">{{ $historial->motivo ?: '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="px-4 py-6 text-center text-muted">Este proceso todavía no tiene cambios de estado.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </article>
            @empty
                <section class="rounded-panel border border-dashed border-line bg-white px-6 py-12 text-center">
                    <h2 class="text-lg font-semibold text-ink">No hay procesos visibles</h2>
                    <p class="mt-2 text-sm text-muted">Este cliente no tiene procesos disponibles para tu alcance.</p>
                </section>
            @endforelse

            @if($turnosSinProceso->isNotEmpty())
                <section class="rounded-panel border border-line bg-white p-5 shadow-panel sm:p-7">
                    <h2 class="text-base font-semibold text-ink">Turnos del cliente sin proceso asociado</h2>
                    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                        @foreach($turnosSinProceso as $turno)
                            <div class="rounded-card border border-line p-4" wire:key="legajo-turno-cliente-{{ $turno->id }}">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="font-medium text-ink">{{ str_replace('_', ' ', ucfirst($turno->tipo)) }}</p>
                                    <span class="rounded-full bg-aequitas-50 px-2.5 py-1 text-xs font-medium text-ink">{{ ucfirst($turno->estado) }}</span>
                                </div>
                                <p class="mt-2 text-sm text-ink">{{ $turno->fecha_hora?->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>
                                <p class="mt-1 text-sm text-muted">{{ $turno->profesional?->name ?? $turno->coordinador?->name ?? 'Sin responsable' }}</p>
                                @if($turno->es_externo && $turno->detalle_externo)
                                    <p class="mt-2 text-sm text-muted">{{ $turno->detalle_externo }}</p>
                                @endif
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
                            <div class="flex flex-col gap-3 rounded-card border border-line p-4 sm:flex-row sm:items-center sm:justify-between" wire:key="legajo-comprobante-cliente-{{ $comprobante->id }}">
                                <div>
                                    <p class="font-medium text-ink">{{ $comprobante->descripcion ?: 'Comprobante de pago' }}</p>
                                    <p class="mt-1 text-sm text-muted">{{ $comprobante->fecha_subida?->format('d/m/Y') ?? 'Sin fecha' }}</p>
                                </div>
                                @can('view', $comprobante)
                                    <a href="{{ route('comprobantes.show', $comprobante) }}" class="inline-flex w-fit items-center justify-center rounded-control bg-aequitas-50 min-h-11 px-3 py-2 text-xs font-semibold text-aequitas-900 hover:bg-aequitas-100">Ver comprobante</a>
                                @endcan
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
