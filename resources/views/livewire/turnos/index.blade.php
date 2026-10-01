<div>
    @if ($successMessage !== '')
        <p role="status" class="mb-4 rounded bg-green-100 p-3 text-green-800">{{ $successMessage }}</p>
    @endif

    <div class="mb-6 flex flex-wrap gap-3">
        <select wire:model.live="estadoFiltro" aria-label="Filtrar por estado" class="rounded border-gray-300">
            <option value="">Todos los estados</option>
            <option value="programado">Programados</option>
            <option value="cancelado">Cancelados</option>
        </select>
        <select wire:model.live="tipoFiltro" aria-label="Filtrar por tipo" class="rounded border-gray-300">
            <option value="">Todos los tipos</option>
            <option value="consulta_inicial">Consulta inicial</option>
            <option value="seguimiento">Seguimiento</option>
        </select>
        @if ($profesionales->isNotEmpty())
            <select wire:model.live="profesionalId" aria-label="Filtrar por profesional" class="rounded border-gray-300">
                <option value="">Todos los profesionales</option>
                @foreach ($profesionales as $profesional)
                    <option value="{{ $profesional->id }}">{{ $profesional->name }}</option>
                @endforeach
            </select>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead><tr><th class="p-3">Cliente</th><th class="p-3">Profesional</th><th class="p-3">Proceso</th><th class="p-3">Tipo</th><th class="p-3">Fecha y hora</th><th class="p-3">Estado</th><th class="p-3">Acciones</th></tr></thead>
            <tbody>
                @forelse ($turnos as $turno)
                    <tr wire:key="turno-{{ $turno->id }}" class="border-t">
                        <td class="p-3">{{ $turno->cliente?->apellido }}, {{ $turno->cliente?->nombre }}</td>
                        <td class="p-3">{{ $turno->profesional?->name }}</td>
                        <td class="p-3">{{ $turno->proceso?->nombre ?? '—' }}</td>
                        <td class="p-3">{{ $turno->tipo === 'seguimiento' ? 'Seguimiento' : 'Consulta inicial' }}</td>
                        <td class="p-3">{{ $turno->fecha_hora?->format('d/m/Y H:i') }}</td>
                        <td class="p-3">{{ $turno->estado === 'cancelado' ? 'Cancelado (histórico)' : 'Programado' }}</td>
                        <td class="space-x-2 p-3">
                            <a href="{{ route('turnos.show', $turno) }}" class="text-indigo-700 underline">Ver</a>
                            @can('update', $turno)
                                <a href="{{ route('turnos.edit', $turno) }}" class="text-indigo-700 underline">Reprogramar</a>
                            @endcan
                            @can('delete', $turno)
                                <button wire:click="cancelTurno({{ $turno->id }})" wire:confirm="¿Cancelar el turno? Se conservará en el historial y se liberará el horario." class="text-red-700 underline">Cancelar</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-4 text-center">No hay turnos para mostrar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $turnos->links() }}</div>
</div>
