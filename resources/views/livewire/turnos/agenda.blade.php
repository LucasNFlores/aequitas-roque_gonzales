<div>
    <form wire:submit="applyFilters" class="mb-6 flex flex-wrap items-end gap-3">
        @if ($profesionales->isNotEmpty())
            <label class="grid gap-1">Profesional<select wire:model="profesionalId" class="rounded border-gray-300"><option value="">Todos</option>@foreach ($profesionales as $profesional)<option value="{{ $profesional->id }}">{{ $profesional->name }}</option>@endforeach</select></label>
        @endif
        <label class="grid gap-1">Desde<input type="date" wire:model="desde" class="rounded border-gray-300"></label>
        <label class="grid gap-1">Hasta<input type="date" wire:model="hasta" class="rounded border-gray-300"></label>
        <button class="rounded bg-indigo-600 px-4 py-2 text-white">Aplicar filtros</button>
    </form>
    @error('profesionalId')<p class="text-red-700">{{ $message }}</p>@enderror
    @error('desde')<p class="text-red-700">{{ $message }}</p>@enderror
    @error('hasta')<p class="text-red-700">{{ $message }}</p>@enderror
    <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr><th class="p-3">Fecha y hora</th><th class="p-3">Profesional</th><th class="p-3">Cliente / compromiso</th><th class="p-3">Proceso</th><th class="p-3">Tipo</th><th class="p-3">Estado</th><th class="p-3">Acción</th></tr></thead><tbody>
        @forelse ($turnos as $turno)
            <tr wire:key="agenda-turno-{{ $turno->id }}" class="border-t"><td class="p-3">{{ $turno->fecha_hora?->format('d/m/Y H:i') }}</td><td class="p-3">{{ $turno->profesional?->name }}</td><td class="p-3">{{ $turno->es_externo ? $turno->detalle_externo : ($turno->cliente?->apellido . ', ' . $turno->cliente?->nombre) }}</td><td class="p-3">{{ $turno->proceso?->nombre ?? '—' }}</td><td class="p-3">{{ $turno->es_externo ? 'Compromiso externo' : ($turno->tipo === 'seguimiento' ? 'Seguimiento' : 'Consulta inicial') }}</td><td class="p-3">{{ $turno->estado === 'cancelado' ? 'Cancelado (histórico)' : 'Programado' }}</td><td class="p-3"><a class="text-indigo-700 underline" href="{{ route('turnos.show', $turno) }}">Ver</a></td></tr>
        @empty
            <tr><td colspan="7" class="p-4 text-center">No hay turnos en el período.</td></tr>
        @endforelse
    </tbody></table></div>
    <div class="mt-4">{{ $turnos->links() }}</div>
</div>
