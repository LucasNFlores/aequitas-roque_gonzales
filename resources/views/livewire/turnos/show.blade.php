<div class="space-y-5">
    @if (session('success'))<p role="status" class="rounded-control border border-green-200 bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</p>@endif
    <dl class="grid gap-4 rounded-card border border-[#dde2e8] bg-[#f5f6f8] p-4 sm:grid-cols-2 sm:p-5">
        <div><dt class="text-sm font-medium text-[#5b6470]">Cliente</dt><dd class="break-words font-medium text-[#172538]">{{ $turno->cliente?->apellido }}, {{ $turno->cliente?->nombre }}</dd></div>
        <div><dt class="text-sm font-medium text-[#5b6470]">Profesional</dt><dd class="break-words text-[#172538]">{{ $turno->profesional?->name }}</dd></div>
        <div><dt class="text-sm font-medium text-[#5b6470]">Proceso</dt><dd class="break-words text-[#172538]">{{ $turno->proceso?->nombre ?? '—' }}</dd></div>
        <div><dt class="text-sm font-medium text-[#5b6470]">Tipo</dt><dd class="text-[#172538]">{{ $turno->tipo === 'seguimiento' ? 'Seguimiento' : ($turno->es_externo ? 'Compromiso externo' : 'Consulta inicial') }}</dd></div>
        @if ($turno->es_externo)<div class="sm:col-span-2"><dt class="text-sm font-medium text-[#5b6470]">Detalle</dt><dd class="break-words text-[#172538]">{{ $turno->detalle_externo }}</dd></div>@endif
        <div><dt class="text-sm font-medium text-[#5b6470]">Fecha y hora</dt><dd class="text-[#172538]">{{ $turno->fecha_hora?->format('d/m/Y H:i') }}</dd></div>
        <div><dt class="text-sm font-medium text-[#5b6470]">Estado</dt><dd class="mt-1">
            @if ($turno->estado === 'cancelado')
                <span class="inline-flex rounded-full bg-[#edf1f5] px-2.5 py-1 text-sm font-semibold text-[#5b6470]">Cancelado (histórico)</span>
            @else
                <span class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-sm font-semibold text-green-800">Programado</span>
            @endif
        </dd></div>
    </dl>
    <div class="flex flex-col gap-3 border-t border-[#dde2e8] pt-5 sm:flex-row sm:flex-wrap">
        <a href="{{ route('turnos.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-control border border-[#c7ced7] bg-white px-4 py-2 text-sm font-semibold text-[#172538] hover:bg-[#f5f6f8] focus:outline-none focus:ring-2 focus:ring-[#8c6936]">Volver</a>
        @can('update', $turno)<a href="{{ $turno->es_externo ? route('turnos.externos.edit', $turno) : route('turnos.edit', $turno) }}" class="inline-flex min-h-11 items-center justify-center rounded-control border border-[#c7ced7] bg-white px-4 py-2 text-sm font-semibold text-[#172538] hover:bg-[#f5f6f8] focus:outline-none focus:ring-2 focus:ring-[#8c6936]">Reprogramar</a>@endcan
        @can('delete', $turno)
            @if ($confirmingCancellation)
                <div role="alertdialog" aria-modal="true" aria-labelledby="confirm-cancel-title" class="w-full rounded-card border border-red-300 bg-red-50 p-4 text-red-900">
                    <h3 id="confirm-cancel-title" class="font-semibold">¿Cancelar este turno?</h3>
                    <p class="mt-2 text-sm">Se conservará como histórico y se liberará el horario del profesional.</p>
                    <div class="mt-4 flex flex-col gap-3 sm:flex-row"><button wire:click="cancelTurno" class="inline-flex min-h-11 items-center justify-center rounded-control bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-700 focus:ring-offset-2">Confirmar cancelación</button><button wire:click="$set('confirmingCancellation', false)" class="inline-flex min-h-11 items-center justify-center rounded-control border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-900 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-700">Volver</button></div>
                </div>
            @else
                <button wire:click="requestCancellation" class="inline-flex min-h-11 items-center justify-center rounded-control bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-700 focus:ring-offset-2">Cancelar turno</button>
            @endif
        @endcan
    </div>
</div>
