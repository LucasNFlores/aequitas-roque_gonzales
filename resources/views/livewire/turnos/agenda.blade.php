<div class="space-y-6">
    <form wire:submit="applyFilters" class="grid items-end gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @if ($profesionales->isNotEmpty())
            <div class="grid gap-1 text-sm font-medium text-gray-700">
                <label for="agenda-profesional">Profesional</label>
                <select id="agenda-profesional" wire:model="profesionalId" class="w-full rounded border-gray-300" @error('profesionalId') aria-invalid="true" aria-describedby="agenda-profesional-error" @enderror>
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

        <div class="grid gap-1 text-sm font-medium text-gray-700">
            <label for="agenda-desde">Desde</label>
            <input id="agenda-desde" type="date" wire:model="desde" class="w-full rounded border-gray-300" @error('desde') aria-invalid="true" aria-describedby="agenda-desde-error" @enderror>
            @error('desde')
                <span id="agenda-desde-error" role="alert" class="text-sm font-normal text-red-700">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid gap-1 text-sm font-medium text-gray-700">
            <label for="agenda-hasta">Hasta</label>
            <input id="agenda-hasta" type="date" wire:model="hasta" class="w-full rounded border-gray-300" @error('hasta') aria-invalid="true" aria-describedby="agenda-hasta-error" @enderror>
            @error('hasta')
                <span id="agenda-hasta-error" role="alert" class="text-sm font-normal text-red-700">{{ $message }}</span>
            @enderror
        </div>

        <button class="w-full rounded bg-indigo-600 px-4 py-2 font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
            Aplicar filtros
        </button>
    </form>

    <div class="space-y-3">
        @forelse ($turnos as $turno)
            <article wire:key="agenda-turno-{{ $turno->id }}" class="grid gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-8 lg:items-center">
                <div>
                    <h2 class="text-sm font-medium text-gray-500">Fecha y hora</h2>
                    <p class="font-semibold text-gray-900">{{ $turno->fecha_hora?->format('d/m/Y H:i') }}</p>
                </div>

                <div class="sm:col-span-2">
                    <h2 class="text-sm font-medium text-gray-500">Cliente / compromiso</h2>
                    <p class="font-medium text-gray-900">
                        {{ $turno->es_externo ? $turno->detalle_externo : ($turno->cliente?->apellido . ', ' . $turno->cliente?->nombre) }}
                    </p>
                </div>

                <div>
                    <h2 class="text-sm font-medium text-gray-500">Profesional</h2>
                    <p class="text-gray-900">{{ $turno->profesional?->name }}</p>
                </div>

                <div>
                    <h2 class="text-sm font-medium text-gray-500">Proceso</h2>
                    <p class="text-gray-900">{{ $turno->proceso?->nombre ?? '—' }}</p>
                </div>

                <div>
                    <h2 class="text-sm font-medium text-gray-500">Tipo</h2>
                    <p class="text-gray-900">
                        {{ $turno->es_externo ? 'Compromiso externo' : ($turno->tipo === 'seguimiento' ? 'Seguimiento' : 'Consulta inicial') }}
                    </p>
                </div>

                <div>
                    <h2 class="text-sm font-medium text-gray-500">Disponibilidad</h2>
                    @if ($turno->isCancelado())
                        <p class="text-gray-700">Cancelado (histórico, no ocupa disponibilidad)</p>
                    @elseif ($turno->es_externo)
                        <p class="font-medium text-amber-800">Bloquea la jornada completa</p>
                    @else
                        <p class="text-gray-700">Horario ocupado</p>
                    @endif
                </div>

                <div class="sm:col-span-2 lg:col-span-1 lg:text-right">
                    <a class="font-medium text-indigo-700 underline hover:text-indigo-900" href="{{ route('turnos.show', $turno) }}">Ver detalle</a>
                </div>
            </article>
        @empty
            <p role="status" class="rounded-lg border border-gray-200 bg-gray-50 p-6 text-center text-gray-700">
                No hay turnos en el período.
            </p>
        @endforelse
    </div>

    <div>{{ $turnos->links() }}</div>
</div>
