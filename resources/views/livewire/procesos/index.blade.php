<div
    x-data="{ historyOpen: false, stateOpen: false, rejectionOpen: false, assignmentOpen: false }"
    x-on:proceso-gestionada.window="historyOpen = false; stateOpen = false; rejectionOpen = false; assignmentOpen = false"
    x-on:keydown.escape.window="if (historyOpen) { historyOpen = false; $wire.closeHistory(); } if (stateOpen) { stateOpen = false; $wire.closeStateModal(); } if (rejectionOpen) { rejectionOpen = false; $wire.closeRejectionModal(); } if (assignmentOpen) { assignmentOpen = false; $wire.closeAssignmentModal(); }"
>
    @if($successMessage !== '')
        <div class="mb-6 rounded-control border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
            {{ $successMessage }}
        </div>
    @endif

    @error('admission')
        <div class="mb-6 rounded-control border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ $message }}</div>
    @enderror

    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h3 class="text-lg font-medium text-ink">Procesos registrados</h3>
            <p class="mt-1 text-sm text-muted">Consulta, admite y asigna procesos autorizados; cada transición queda registrada.</p>
        </div>

        @can('gestionar_estados_proceso')
            <a href="{{ route('estados-proceso.index') }}" class="inline-flex items-center justify-center rounded-control border border-aequitas-100 bg-aequitas-50 min-h-11 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-aequitas-900 transition hover:bg-aequitas-100 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">
                Administrar estados
            </a>
        @endcan
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div>
            <label for="process-search" class="block text-sm font-medium text-ink">Buscar cliente o proceso</label>
            <input id="process-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Nombre, DNI, servicio, coordinador o profesional" class="mt-2 block w-full rounded-control border-line shadow-panel focus:border-gold-600 focus:ring-gold-600" />
        </div>

        <div>
            <label for="process-state-filter" class="block text-sm font-medium text-ink">Estado</label>
            <select id="process-state-filter" wire:model.live="estadoFiltro" class="mt-2 block w-full rounded-control border-line shadow-panel focus:border-gold-600 focus:ring-gold-600">
                <option value="">Todos los estados</option>
                @foreach($estados as $estado)
                    <option value="{{ $estado->slug }}">{{ $estado->nombre }}{{ $estado->activo ? '' : ' (inactivo)' }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="process-client-filter" class="block text-sm font-medium text-ink">Cliente</label>
            <select id="process-client-filter" wire:model.live="clienteId" class="mt-2 block w-full rounded-control border-line shadow-panel focus:border-gold-600 focus:ring-gold-600">
                <option value="">Todos los clientes</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}">{{ $cliente->nombre }} {{ $cliente->apellido }} · {{ $cliente->dni }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="process-service-filter" class="block text-sm font-medium text-ink">Servicio</label>
            <select id="process-service-filter" wire:model.live="servicioId" class="mt-2 block w-full rounded-control border-line shadow-panel focus:border-gold-600 focus:ring-gold-600">
                <option value="">Todos los servicios</option>
                @foreach($servicios as $servicio)
                    <option value="{{ $servicio->id }}">{{ $servicio->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="process-coordinator-filter" class="block text-sm font-medium text-ink">Coordinador</label>
            <select id="process-coordinator-filter" wire:model.live="coordinadorId" class="mt-2 block w-full rounded-control border-line shadow-panel focus:border-gold-600 focus:ring-gold-600">
                <option value="">Todos los coordinadores</option>
                @foreach($coordinadores as $coordinador)
                    <option value="{{ $coordinador->id }}">{{ $coordinador->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="process-professional-filter" class="block text-sm font-medium text-ink">Profesional</label>
            <select id="process-professional-filter" wire:model.live="profesionalId" class="mt-2 block w-full rounded-control border-line shadow-panel focus:border-gold-600 focus:ring-gold-600">
                <option value="">Todos los profesionales</option>
                @foreach($profesionales as $profesional)
                    <option value="{{ $profesional->id }}">{{ $profesional->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="process-date-from" class="block text-sm font-medium text-ink">Inicio desde</label>
            <input id="process-date-from" type="date" wire:model.live="fechaDesde" class="mt-2 block w-full rounded-control border-line shadow-panel focus:border-gold-600 focus:ring-gold-600" />
        </div>

        <div>
            <label for="process-date-to" class="block text-sm font-medium text-ink">Inicio hasta</label>
            <input id="process-date-to" type="date" wire:model.live="fechaHasta" class="mt-2 block w-full rounded-control border-line shadow-panel focus:border-gold-600 focus:ring-gold-600" />
        </div>

        <div class="flex items-end">
            <button type="button" wire:click="resetFilters" class="inline-flex w-full items-center justify-center rounded-control border border-line bg-white min-h-11 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-ink shadow-panel transition hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">
                Limpiar filtros
            </button>
        </div>
    </div>

    <div class="mt-6 overflow-x-auto rounded-card border border-line shadow-panel">
        <table class="w-full min-w-[1350px] text-left text-sm text-muted">
            <thead class="bg-canvas text-xs uppercase tracking-wide text-ink">
                <tr>
                    <th scope="col" class="px-5 py-3">Proceso</th>
                    <th scope="col" class="px-5 py-3">Cliente</th>
                    <th scope="col" class="px-5 py-3">Servicio</th>
                    <th scope="col" class="px-5 py-3">Estado</th>
                    <th scope="col" class="px-5 py-3">Coordinador</th>
                    <th scope="col" class="px-5 py-3">Profesional</th>
                    <th scope="col" class="px-5 py-3">Honorarios</th>
                    <th scope="col" class="px-5 py-3">Inicio</th>
                    <th scope="col" class="px-5 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($procesos as $proceso)
                    @php($estadoActual = $estados->firstWhere('slug', $proceso->estado))
                    <tr wire:key="proceso-{{ $proceso->id }}" class="border-b bg-white hover:bg-canvas">
                        <td class="px-5 py-4">
                            <p class="font-medium text-ink">{{ $proceso->nombre }}</p>
                            <p class="mt-1 text-xs text-muted">#{{ $proceso->id }}</p>
                        </td>
                        <td class="px-5 py-4">
                            @if($proceso->cliente)
                                <p class="font-medium text-ink">{{ $proceso->cliente->nombre }} {{ $proceso->cliente->apellido }}</p>
                                <p class="mt-1 text-xs text-muted">DNI {{ $proceso->cliente->dni }}</p>
                            @else
                                <span class="text-gray-400">Cliente no disponible</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">{{ $proceso->servicio?->nombre ?? 'Servicio no disponible' }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $estadoActual?->activo === false ? 'bg-aequitas-50 text-ink' : 'bg-aequitas-100 text-aequitas-900' }}">
                                {{ $estadoActual?->nombre ?? \Illuminate\Support\Str::headline($proceso->estado) }}
                            </span>
                            @if($proceso->motivo_rechazo)
                                <p class="mt-1 max-w-48 text-xs text-muted">{{ $proceso->motivo_rechazo }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4">{{ $proceso->coordinador?->name ?? 'Sin coordinador' }}</td>
                        <td class="px-5 py-4">{{ $proceso->profesional?->name ?? 'Sin asignar' }}</td>
                        <td class="whitespace-nowrap px-5 py-4">{{ $proceso->honorarios === null ? '—' : '$'.number_format((float) $proceso->honorarios, 2, ',', '.') }}</td>
                        <td class="whitespace-nowrap px-5 py-4">{{ $proceso->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
                        <td class="space-y-2 px-5 py-4 text-center">
                            @if($proceso->estado === \App\Models\Proceso::ESTADO_PENDIENTE)
                                @can('admit', $proceso)
                                    <button type="button" wire:click="admitProcess({{ $proceso->id }})" class="block w-full rounded-control bg-green-50 min-h-11 px-3 py-2 font-medium text-green-700 transition hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                        Admitir
                                    </button>
                                @endcan
                                @can('reject', $proceso)
                                    <button type="button" @click="rejectionOpen = true" wire:click="prepareRejection({{ $proceso->id }})" class="block w-full rounded-control bg-red-50 min-h-11 px-3 py-2 font-medium text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                        Rechazar
                                    </button>
                                @endcan
                            @endif

                            @if($proceso->profesional_id === null)
                                @can('assignProfessional', $proceso)
                                    <button type="button" @click="assignmentOpen = true" wire:click="prepareProfessionalAssignment({{ $proceso->id }})" class="block w-full rounded-control bg-amber-50 min-h-11 px-3 py-2 font-medium text-amber-800 transition hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                                        Asignar profesional
                                    </button>
                                @endcan
                            @else
                                @can('reassignProfessional', $proceso)
                                    <button type="button" @click="assignmentOpen = true" wire:click="prepareProfessionalAssignment({{ $proceso->id }})" class="block w-full rounded-control bg-amber-50 min-h-11 px-3 py-2 font-medium text-amber-800 transition hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                                        Reasignar profesional
                                    </button>
                                @endcan
                            @endif

                            @can('viewHistory', $proceso)
                                <button type="button" @click="historyOpen = true" wire:click="openHistory({{ $proceso->id }})" class="block w-full rounded-control bg-aequitas-50 min-h-11 px-3 py-2 font-medium text-ink transition hover:bg-aequitas-100 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">
                                    Ver historial
                                </button>
                            @endcan
                            @can('updateState', $proceso)
                                <button type="button" @click="stateOpen = true" wire:click="prepareStateChange({{ $proceso->id }})" class="block w-full rounded-control bg-aequitas-50 min-h-11 px-3 py-2 font-medium text-aequitas-900 transition hover:bg-aequitas-100 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">
                                    Cambiar estado
                                </button>
                            @endcan
                            @can('visualizar_documentacion')
                                <a href="{{ route('procesos.documentos.index', $proceso) }}" class="mt-2 block w-full rounded-control bg-white min-h-11 px-3 py-2 text-center font-medium text-ink ring-1 ring-line transition hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">
                                    Documentación
                                </a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white">
                        <td colspan="9" class="px-6 py-10 text-center italic text-muted">No se encontraron procesos con los filtros seleccionados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $procesos->links() }}
    </div>

    <div x-cloak x-show="historyOpen" x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="history-modal-title" @click.self="historyOpen = false; $wire.closeHistory()" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/60 px-4 py-6 backdrop-blur-sm sm:px-6">
        <div class="max-h-[calc(100vh-3rem)] w-full max-w-3xl overflow-y-auto rounded-panel bg-white shadow-floating">
            <div class="flex items-start justify-between gap-4 border-b border-line px-6 py-5 sm:px-8">
                <div>
                    <h2 id="history-modal-title" class="text-xl font-semibold text-ink">Historial de estados</h2>
                    <p class="mt-1 text-sm text-muted">Consulta las transiciones, sus motivos y responsables.</p>
                </div>
                <button type="button" @click="historyOpen = false; $wire.closeHistory()" class="rounded-control min-h-11 min-w-11 p-1 text-2xl leading-none text-gray-400 transition hover:bg-aequitas-50 hover:text-muted focus:outline-none focus:ring-2 focus:ring-gold-600" aria-label="Cerrar historial">&times;</button>
            </div>

            <div class="px-6 py-6 sm:px-8">
                @forelse($historial as $change)
                    <article class="relative border-l-2 border-aequitas-100 pb-6 pl-6 last:pb-0">
                        <span class="absolute -left-[9px] top-0 h-4 w-4 rounded-full border-4 border-white bg-aequitas-900" aria-hidden="true"></span>
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                            <p class="font-semibold text-ink">{{ $change['estado_anterior'] }} <span class="font-normal text-gray-400">→</span> {{ $change['estado_nuevo'] }}</p>
                            <time class="text-xs text-muted">{{ $change['fecha_cambio'] }}</time>
                        </div>
                        <p class="mt-1 text-sm text-muted">Por {{ $change['usuario'] }}</p>
                        <p class="mt-2 rounded-control bg-canvas min-h-11 px-3 py-2 text-sm text-ink">{{ $change['motivo'] }}</p>
                    </article>
                @empty
                    <p class="py-8 text-center italic text-muted">No existe historial disponible para este proceso.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div x-cloak x-show="rejectionOpen" x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="rejection-modal-title" @click.self="rejectionOpen = false; $wire.closeRejectionModal()" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/60 px-4 py-6 backdrop-blur-sm sm:px-6">
        <div class="w-full max-w-xl rounded-panel bg-white shadow-floating">
            <div class="flex items-start justify-between gap-4 border-b border-line px-6 py-5 sm:px-8">
                <div>
                    <h2 id="rejection-modal-title" class="text-xl font-semibold text-ink">Rechazar proceso</h2>
                    <p class="mt-1 text-sm text-muted">Indica el motivo para conservarlo en el historial.</p>
                </div>
                <button type="button" @click="rejectionOpen = false; $wire.closeRejectionModal()" class="rounded-control min-h-11 min-w-11 p-1 text-2xl leading-none text-gray-400 transition hover:bg-aequitas-50 hover:text-muted focus:outline-none focus:ring-2 focus:ring-gold-600" aria-label="Cerrar rechazo">&times;</button>
            </div>

            <form wire:submit="saveRejection" class="space-y-6 px-6 py-6 sm:px-8">
                <div>
                    <label for="rejection-reason" class="block text-sm font-medium text-ink">Motivo de rechazo <span class="text-red-600">*</span></label>
                    <textarea id="rejection-reason" wire:model.defer="motivoRechazo" rows="4" required maxlength="5000" placeholder="Describe la causal del rechazo..." class="mt-2 block w-full rounded-control border-line shadow-panel focus:border-gold-600 focus:ring-gold-600"></textarea>
                    @error('motivoRechazo')
                        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-line pt-5 sm:flex-row sm:justify-end">
                    <button type="button" @click="rejectionOpen = false; $wire.closeRejectionModal()" class="inline-flex w-full items-center justify-center rounded-control border border-line bg-white min-h-11 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-ink shadow-panel transition hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2 sm:w-auto">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-control border border-transparent bg-red-600 min-h-11 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white shadow-panel transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 sm:w-auto">
                        <span wire:loading.remove wire:target="saveRejection">Confirmar rechazo</span>
                        <span wire:loading wire:target="saveRejection">Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-cloak x-show="assignmentOpen" x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="assignment-modal-title" @click.self="assignmentOpen = false; $wire.closeAssignmentModal()" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/60 px-4 py-6 backdrop-blur-sm sm:px-6">
        <div class="w-full max-w-xl rounded-panel bg-white shadow-floating">
            <div class="flex items-start justify-between gap-4 border-b border-line px-6 py-5 sm:px-8">
                <div>
                    <h2 id="assignment-modal-title" class="text-xl font-semibold text-ink">Asignar profesional</h2>
                    <p class="mt-1 text-sm text-muted">Solo se muestran profesionales habilitados para el servicio del proceso.</p>
                </div>
                <button type="button" @click="assignmentOpen = false; $wire.closeAssignmentModal()" class="rounded-control min-h-11 min-w-11 p-1 text-2xl leading-none text-gray-400 transition hover:bg-aequitas-50 hover:text-muted focus:outline-none focus:ring-2 focus:ring-gold-600" aria-label="Cerrar asignación">&times;</button>
            </div>

            <form wire:submit="saveProfessionalAssignment" class="space-y-6 px-6 py-6 sm:px-8">
                <div>
                    <label for="assignment-professional" class="block text-sm font-medium text-ink">Profesional</label>
                    <select id="assignment-professional" wire:model.defer="selectedProfesionalId" required class="mt-2 block w-full rounded-control border-line shadow-panel focus:border-gold-600 focus:ring-gold-600">
                        <option value="">Seleccionar profesional</option>
                        @foreach($profesionalesElegibles as $profesionalElegible)
                            <option value="{{ $profesionalElegible->id }}">{{ $profesionalElegible->name }}</option>
                        @endforeach
                    </select>
                    @if($profesionalesElegibles->isEmpty())
                        <p class="mt-2 text-sm text-amber-700">No hay profesionales activos habilitados para este servicio.</p>
                    @endif
                    @error('selectedProfesionalId')
                        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-line pt-5 sm:flex-row sm:justify-end">
                    <button type="button" @click="assignmentOpen = false; $wire.closeAssignmentModal()" class="inline-flex w-full items-center justify-center rounded-control border border-line bg-white min-h-11 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-ink shadow-panel transition hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2 sm:w-auto">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" @disabled($profesionalesElegibles->isEmpty()) class="inline-flex w-full items-center justify-center rounded-control border border-transparent bg-aequitas-900 min-h-11 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white shadow-panel transition hover:bg-aequitas-800 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2 disabled:opacity-50 sm:w-auto">
                        <span wire:loading.remove wire:target="saveProfessionalAssignment">Guardar asignación</span>
                        <span wire:loading wire:target="saveProfessionalAssignment">Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-cloak x-show="stateOpen" x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="state-modal-title" @click.self="stateOpen = false; $wire.closeStateModal()" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900/60 px-4 py-6 backdrop-blur-sm sm:px-6">
        <div class="w-full max-w-xl rounded-panel bg-white shadow-floating">
            <div class="flex items-start justify-between gap-4 border-b border-line px-6 py-5 sm:px-8">
                <div>
                    <h2 id="state-modal-title" class="text-xl font-semibold text-ink">Cambiar estado del proceso</h2>
                    <p class="mt-1 text-sm text-muted">La admisión y el rechazo se registran desde sus acciones específicas.</p>
                </div>
                <button type="button" @click="stateOpen = false; $wire.closeStateModal()" class="rounded-control min-h-11 min-w-11 p-1 text-2xl leading-none text-gray-400 transition hover:bg-aequitas-50 hover:text-muted focus:outline-none focus:ring-2 focus:ring-gold-600" aria-label="Cerrar cambio de estado">&times;</button>
            </div>

            <form wire:submit="saveStateChange" class="space-y-6 px-6 py-6 sm:px-8">
                <div>
                    <label for="pending-state" class="block text-sm font-medium text-ink">Nuevo estado</label>
                    <select id="pending-state" wire:model.defer="pendingEstado" class="mt-2 block w-full rounded-control border-line shadow-panel focus:border-gold-600 focus:ring-gold-600">
                        <option value="">Seleccionar estado</option>
                        @foreach($estadosActivos as $estado)
                            <option value="{{ $estado->slug }}">{{ $estado->nombre }}</option>
                        @endforeach
                    </select>
                    @error('pendingEstado')
                        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="state-reason" class="block text-sm font-medium text-ink">Motivo <span class="font-normal text-muted">(opcional)</span></label>
                    <textarea id="state-reason" wire:model.defer="motivoEstado" rows="4" maxlength="5000" placeholder="Describe el motivo del cambio..." class="mt-2 block w-full rounded-control border-line shadow-panel focus:border-gold-600 focus:ring-gold-600"></textarea>
                    @error('motivoEstado')
                        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-line pt-5 sm:flex-row sm:justify-end">
                    <button type="button" @click="stateOpen = false; $wire.closeStateModal()" class="inline-flex w-full items-center justify-center rounded-control border border-line bg-white min-h-11 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-ink shadow-panel transition hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2 sm:w-auto">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-control border border-transparent bg-aequitas-900 min-h-11 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white shadow-panel transition hover:bg-aequitas-800 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2 disabled:opacity-50 sm:w-auto">
                        <span wire:loading.remove wire:target="saveStateChange">Guardar cambio</span>
                        <span wire:loading wire:target="saveStateChange">Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
