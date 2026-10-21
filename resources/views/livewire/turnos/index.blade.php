<div class="space-y-5">
    @if ($successMessage !== '')
        <p role="status" class="rounded-control border border-green-200 bg-green-50 p-3 text-sm text-green-800">{{ $successMessage }}</p>
    @endif

    @can('createExternal', \App\Models\Turno::class)
        <div class="flex justify-start sm:justify-end">
            <a href="{{ route('turnos.externos.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-control bg-[#172538] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#243850] focus:outline-none focus:ring-2 focus:ring-[#8c6936] focus:ring-offset-2">
                Registrar compromiso externo
            </a>
        </div>
    @endcan

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <select wire:model.live="estadoFiltro" aria-label="Filtrar por estado" class="min-h-11 w-full rounded-control border-[#c7ced7] bg-white text-[#172538] shadow-sm focus:border-[#8c6936] focus:ring-[#8c6936]">
            <option value="">Todos los estados</option>
            <option value="programado">Programados</option>
            <option value="cancelado">Cancelados</option>
        </select>
        <select wire:model.live="tipoFiltro" aria-label="Filtrar por tipo" class="min-h-11 w-full rounded-control border-[#c7ced7] bg-white text-[#172538] shadow-sm focus:border-[#8c6936] focus:ring-[#8c6936]">
            <option value="">Todos los tipos</option>
            <option value="consulta_inicial">Consulta inicial</option>
            <option value="seguimiento">Seguimiento</option>
            <option value="externo">Compromiso externo</option>
        </select>
        @if ($profesionales->isNotEmpty())
            <select wire:model.live="profesionalId" aria-label="Filtrar por profesional" class="min-h-11 w-full rounded-control border-[#c7ced7] bg-white text-[#172538] shadow-sm focus:border-[#8c6936] focus:ring-[#8c6936]">
                <option value="">Todos los profesionales</option>
                @foreach ($profesionales as $profesional)
                    <option value="{{ $profesional->id }}">{{ $profesional->name }}</option>
                @endforeach
            </select>
        @endif
    </div>

    <div class="hidden overflow-x-auto rounded-card border border-[#dde2e8] lg:block">
        <table class="w-full min-w-[900px] text-left text-sm text-[#5b6470]">
            <thead class="bg-[#f5f6f8] text-xs uppercase tracking-wide text-[#172538]"><tr><th scope="col" class="p-3">Cliente</th><th scope="col" class="p-3">Profesional</th><th scope="col" class="p-3">Proceso</th><th scope="col" class="p-3">Tipo</th><th scope="col" class="p-3">Fecha y hora</th><th scope="col" class="p-3">Estado</th><th scope="col" class="p-3 text-right">Acciones</th></tr></thead>
            <tbody class="divide-y divide-[#dde2e8] bg-white">
                @forelse ($turnos as $turno)
                    <tr wire:key="turno-table-{{ $turno->id }}" class="align-top hover:bg-[#f5f6f8]">
                        <td class="max-w-[15rem] break-words p-3 font-medium text-[#172538]">{{ $turno->es_externo ? $turno->detalle_externo : ($turno->cliente?->apellido . ', ' . $turno->cliente?->nombre) }}</td>
                        <td class="p-3">{{ $turno->profesional?->name }}</td>
                        <td class="max-w-[12rem] break-words p-3">{{ $turno->proceso?->nombre ?? '—' }}</td>
                        <td class="p-3">{{ $turno->es_externo ? 'Compromiso externo' : ($turno->tipo === 'seguimiento' ? 'Seguimiento' : 'Consulta inicial') }}</td>
                        <td class="whitespace-nowrap p-3">{{ $turno->fecha_hora?->format('d/m/Y H:i') }}</td>
                        <td class="p-3">
                            @if ($turno->estado === 'cancelado')
                                <span class="inline-flex rounded-full bg-[#edf1f5] px-2.5 py-1 text-xs font-semibold text-[#5b6470]">Cancelado (histórico)</span>
                            @else
                                <span class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-800">Programado</span>
                            @endif
                        </td>
                        <td class="p-3">
                            <div class="flex flex-wrap justify-end gap-x-3 gap-y-2">
                                <a href="{{ route('turnos.show', $turno) }}" class="font-medium text-[#172538] underline decoration-[#8c6936] underline-offset-4 focus:outline-none focus:ring-2 focus:ring-[#8c6936]">Ver</a>
                                @can('update', $turno)
                                    <a href="{{ $turno->es_externo ? route('turnos.externos.edit', $turno) : route('turnos.edit', $turno) }}" class="font-medium text-[#172538] underline decoration-[#8c6936] underline-offset-4 focus:outline-none focus:ring-2 focus:ring-[#8c6936]">Reprogramar</a>
                                @endcan
                                @can('delete', $turno)
                                    <button wire:click="cancelTurno({{ $turno->id }})" wire:confirm="¿Cancelar el turno? Se conservará en el historial y se liberará el horario." class="font-medium text-red-700 underline decoration-red-300 underline-offset-4 focus:outline-none focus:ring-2 focus:ring-red-600">Cancelar</button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-6 text-center text-[#5b6470]">No hay turnos para mostrar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="space-y-3 lg:hidden">
        @forelse ($turnos as $turno)
            <article wire:key="turno-card-{{ $turno->id }}" class="min-w-0 rounded-card border border-[#dde2e8] bg-white p-4 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <h3 class="min-w-0 break-words font-semibold text-[#172538]">{{ $turno->es_externo ? $turno->detalle_externo : ($turno->cliente?->apellido . ', ' . $turno->cliente?->nombre) }}</h3>
                    @if ($turno->estado === 'cancelado')
                        <span class="inline-flex shrink-0 rounded-full bg-[#edf1f5] px-2.5 py-1 text-xs font-semibold text-[#5b6470]">Cancelado (histórico)</span>
                    @else
                        <span class="inline-flex shrink-0 rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-800">Programado</span>
                    @endif
                </div>
                <dl class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                    <div><dt class="font-medium text-[#5b6470]">Profesional</dt><dd class="break-words text-[#172538]">{{ $turno->profesional?->name }}</dd></div>
                    <div><dt class="font-medium text-[#5b6470]">Proceso</dt><dd class="break-words text-[#172538]">{{ $turno->proceso?->nombre ?? '—' }}</dd></div>
                    <div><dt class="font-medium text-[#5b6470]">Tipo</dt><dd class="text-[#172538]">{{ $turno->es_externo ? 'Compromiso externo' : ($turno->tipo === 'seguimiento' ? 'Seguimiento' : 'Consulta inicial') }}</dd></div>
                    <div><dt class="font-medium text-[#5b6470]">Fecha y hora</dt><dd class="text-[#172538]">{{ $turno->fecha_hora?->format('d/m/Y H:i') }}</dd></div>
                </dl>
                <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2 border-t border-[#dde2e8] pt-3">
                    <a href="{{ route('turnos.show', $turno) }}" class="inline-flex min-h-11 items-center font-medium text-[#172538] underline decoration-[#8c6936] underline-offset-4 focus:outline-none focus:ring-2 focus:ring-[#8c6936]">Ver</a>
                    @can('update', $turno)
                        <a href="{{ $turno->es_externo ? route('turnos.externos.edit', $turno) : route('turnos.edit', $turno) }}" class="inline-flex min-h-11 items-center font-medium text-[#172538] underline decoration-[#8c6936] underline-offset-4 focus:outline-none focus:ring-2 focus:ring-[#8c6936]">Reprogramar</a>
                    @endcan
                    @can('delete', $turno)
                        <button wire:click="cancelTurno({{ $turno->id }})" wire:confirm="¿Cancelar el turno? Se conservará en el historial y se liberará el horario." class="inline-flex min-h-11 items-center font-medium text-red-700 underline decoration-red-300 underline-offset-4 focus:outline-none focus:ring-2 focus:ring-red-600">Cancelar</button>
                    @endcan
                </div>
            </article>
        @empty
            <p role="status" class="rounded-card border border-[#dde2e8] bg-[#f5f6f8] p-6 text-center text-sm text-[#5b6470]">No hay turnos para mostrar.</p>
        @endforelse
    </div>

    <div class="pt-1">{{ $turnos->links() }}</div>
</div>
