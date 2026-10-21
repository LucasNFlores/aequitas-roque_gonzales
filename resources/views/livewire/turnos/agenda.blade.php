<div class="space-y-6">
    <form wire:submit="applyFilters" class="grid items-end gap-4 rounded-card border border-line bg-canvas p-4 sm:grid-cols-2 lg:grid-cols-4 sm:p-5">
        @if ($profesionales->isNotEmpty())
            <div class="grid gap-1 text-sm font-medium text-ink">
                <label for="agenda-profesional">Profesional</label>
                <select id="agenda-profesional" wire:model="profesionalId" class="min-h-11 w-full rounded-control border-line bg-white text-ink shadow-sm focus:border-gold-600 focus:ring-gold-600" @error('profesionalId') aria-invalid="true" aria-describedby="agenda-profesional-error" @enderror>
                    <option value="">Todos</option>
                    @foreach ($profesionales as $profesional)
                        <option value="{{ $profesional->id }}">{{ $profesional->name }}</option>
                    @endforeach
                </select>
                @error('profesionalId')
                    <span id="agenda-profesional-error" role="alert" class="text-sm font-normal text-red-700">{{ $message }}</span>
                @enderror
            </div>
        @endif

        <div class="grid gap-1 text-sm font-medium text-ink">
            <label for="agenda-desde">Desde</label>
            <input id="agenda-desde" type="date" wire:model="desde" class="min-h-11 w-full rounded-control border-line bg-white text-ink shadow-sm focus:border-gold-600 focus:ring-gold-600" @error('desde') aria-invalid="true" aria-describedby="agenda-desde-error" @enderror>
            @error('desde')
                <span id="agenda-desde-error" role="alert" class="text-sm font-normal text-red-700">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid gap-1 text-sm font-medium text-ink">
            <label for="agenda-hasta">Hasta</label>
            <input id="agenda-hasta" type="date" wire:model="hasta" class="min-h-11 w-full rounded-control border-line bg-white text-ink shadow-sm focus:border-gold-600 focus:ring-gold-600" @error('hasta') aria-invalid="true" aria-describedby="agenda-hasta-error" @enderror>
            @error('hasta')
                <span id="agenda-hasta-error" role="alert" class="text-sm font-normal text-red-700">{{ $message }}</span>
            @enderror
        </div>

        <button class="inline-flex min-h-11 w-full items-center justify-center rounded-control bg-aequitas-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-aequitas-800 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2 sm:w-auto">
            Aplicar filtros
        </button>
    </form>

    <div class="space-y-3">
        @forelse ($turnos as $turno)
            <article wire:key="agenda-turno-{{ $turno->id }}" class="grid min-w-0 gap-4 rounded-card border border-line bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-8 lg:items-center">
                <div>
                    <h2 class="text-sm font-medium text-muted">Fecha y hora</h2>
                    <p class="font-semibold text-ink">{{ $turno->fecha_hora?->format('d/m/Y H:i') }}</p>
                </div>

                <div class="min-w-0 sm:col-span-2">
                    <h2 class="text-sm font-medium text-muted">Cliente / compromiso</h2>
                    <p class="break-words font-medium text-ink">
                        {{ $turno->es_externo ? $turno->detalle_externo : ($turno->cliente?->apellido . ', ' . $turno->cliente?->nombre) }}
                    </p>
                </div>

                <div>
                    <h2 class="text-sm font-medium text-muted">Profesional</h2>
                    <p class="break-words text-ink">{{ $turno->profesional?->name }}</p>
                </div>

                <div class="min-w-0">
                    <h2 class="text-sm font-medium text-muted">Proceso</h2>
                    <p class="break-words text-ink">{{ $turno->proceso?->nombre ?? '—' }}</p>
                </div>

                <div>
                    <h2 class="text-sm font-medium text-muted">Tipo</h2>
                    <p class="text-ink">{{ $turno->es_externo ? 'Compromiso externo' : ($turno->tipo === 'seguimiento' ? 'Seguimiento' : 'Consulta inicial') }}</p>
                </div>

                <div class="lg:col-span-2">
                    <h2 class="text-sm font-medium text-muted">Disponibilidad</h2>
                    @if ($turno->isCancelado())
                        <p class="text-sm text-muted">Cancelado (histórico, no ocupa disponibilidad)</p>
                    @elseif ($turno->es_externo)
                        <p class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-sm font-medium text-amber-900">Bloquea la jornada completa</p>
                    @else
                        <p class="text-ink">Horario ocupado</p>
                    @endif
                </div>

                <div class="flex items-center border-t border-line pt-2 sm:col-span-2 lg:col-span-1 lg:justify-end lg:border-0 lg:pt-0">
                    <a class="inline-flex min-h-11 items-center font-medium text-ink underline decoration-gold-600 underline-offset-4 hover:text-aequitas-800 focus:outline-none focus:ring-2 focus:ring-gold-600" href="{{ route('turnos.show', $turno) }}">Ver detalle</a>
                </div>
            </article>
        @empty
            <p role="status" class="rounded-card border border-line bg-canvas p-6 text-center text-muted">No hay turnos en el período.</p>
        @endforelse
    </div>

    <div>{{ $turnos->links() }}</div>
</div>
