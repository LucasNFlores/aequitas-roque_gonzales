<div class="space-y-4">
    @if (session('success'))<p role="status" class="rounded bg-green-100 p-3 text-green-800">{{ session('success') }}</p>@endif
    <dl class="grid gap-3 sm:grid-cols-2">
        <div><dt class="font-semibold">Cliente</dt><dd>{{ $turno->cliente?->apellido }}, {{ $turno->cliente?->nombre }}</dd></div>
        <div><dt class="font-semibold">Profesional</dt><dd>{{ $turno->profesional?->name }}</dd></div>
        <div><dt class="font-semibold">Proceso</dt><dd>{{ $turno->proceso?->nombre ?? '—' }}</dd></div>
        <div><dt class="font-semibold">Tipo</dt><dd>{{ $turno->tipo === 'seguimiento' ? 'Seguimiento' : ($turno->es_externo ? 'Compromiso externo' : 'Consulta inicial') }}</dd></div>
        @if ($turno->es_externo)<div><dt class="font-semibold">Detalle</dt><dd>{{ $turno->detalle_externo }}</dd></div>@endif
        <div><dt class="font-semibold">Fecha y hora</dt><dd>{{ $turno->fecha_hora?->format('d/m/Y H:i') }}</dd></div>
        <div><dt class="font-semibold">Estado</dt><dd>{{ $turno->estado === 'cancelado' ? 'Cancelado (histórico)' : 'Programado' }}</dd></div>
    </dl>
    <div class="flex flex-wrap gap-3"><a href="{{ route('turnos.index') }}" class="rounded border px-4 py-2">Volver</a>
        @can('update', $turno)<a href="{{ route('turnos.edit', $turno) }}" class="rounded border px-4 py-2">Reprogramar</a>@endcan
        @can('delete', $turno)
            @if ($confirmingCancellation)
                <div role="alertdialog" aria-modal="true" aria-labelledby="confirm-cancel-title" class="w-full rounded border border-red-300 bg-red-50 p-4 text-red-900">
                    <h3 id="confirm-cancel-title" class="font-semibold">¿Cancelar este turno?</h3>
                    <p class="mt-2">Se conservará como histórico y se liberará el horario del profesional.</p>
                    <div class="mt-4 flex gap-3"><button wire:click="cancelTurno" class="rounded bg-red-700 px-4 py-2 text-white">Confirmar cancelación</button><button wire:click="$set('confirmingCancellation', false)" class="rounded border px-4 py-2">Volver</button></div>
                </div>
            @else
                <button wire:click="requestCancellation" class="rounded bg-red-700 px-4 py-2 text-white">Cancelar turno</button>
            @endif
        @endcan
    </div>
</div>
