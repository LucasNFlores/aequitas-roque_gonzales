<form wire:submit="save" class="space-y-5">
    @if ($turnoId === null)
        <label class="grid gap-1 text-sm font-medium text-ink">Cliente
            <select wire:model="clienteId" class="min-h-11 w-full rounded-control border-line bg-white px-3 py-2 text-ink shadow-sm focus:border-gold-600 focus:ring-gold-600"><option value="">Seleccionar</option>@foreach ($clientes as $cliente)<option value="{{ $cliente->id }}">{{ $cliente->apellido }}, {{ $cliente->nombre }}</option>@endforeach</select>
        </label>
        @error('clienteId')<p role="alert" class="text-sm text-red-700">{{ $message }}</p>@enderror
        <label class="grid gap-1 text-sm font-medium text-ink">Profesional
            <select wire:model="profesionalId" class="min-h-11 w-full rounded-control border-line bg-white px-3 py-2 text-ink shadow-sm focus:border-gold-600 focus:ring-gold-600"><option value="">Seleccionar</option>@foreach ($profesionales as $profesional)<option value="{{ $profesional->id }}">{{ $profesional->name }}</option>@endforeach</select>
        </label>
        @error('profesionalId')<p role="alert" class="text-sm text-red-700">{{ $message }}</p>@enderror
        <label class="grid gap-1 text-sm font-medium text-ink">Tipo
            <select wire:model="tipo" class="min-h-11 w-full rounded-control border-line bg-white px-3 py-2 text-ink shadow-sm focus:border-gold-600 focus:ring-gold-600"><option value="consulta_inicial">Consulta inicial</option>@can('agendar_turnos_seguimiento')<option value="seguimiento">Seguimiento</option>@endcan</select>
        </label>
        @error('tipo')<p role="alert" class="text-sm text-red-700">{{ $message }}</p>@enderror
        <label class="grid gap-1 text-sm font-medium text-ink">Proceso (obligatorio para seguimiento)
            <select wire:model="procesoId" class="min-h-11 w-full rounded-control border-line bg-white px-3 py-2 text-ink shadow-sm focus:border-gold-600 focus:ring-gold-600"><option value="">Sin proceso</option>@foreach ($procesos as $proceso)<option value="{{ $proceso->id }}">#{{ $proceso->id }} — {{ $proceso->cliente?->apellido }}, {{ $proceso->cliente?->nombre }} — {{ $proceso->nombre }}</option>@endforeach</select>
        </label>
        @error('procesoId')<p role="alert" class="text-sm text-red-700">{{ $message }}</p>@enderror
    @endif
    <label class="grid gap-1 text-sm font-medium text-ink">Fecha y hora
        <input type="datetime-local" wire:model="fechaHora" class="min-h-11 w-full rounded-control border-line bg-white px-3 py-2 text-ink shadow-sm focus:border-gold-600 focus:ring-gold-600">
    </label>
    @error('fechaHora')<p role="alert" class="text-sm text-red-700">{{ $message }}</p>@enderror
    <div class="flex flex-col gap-3 border-t border-line pt-5 sm:flex-row">
        <button class="inline-flex min-h-11 items-center justify-center rounded-control bg-aequitas-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-aequitas-800 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">{{ $turnoId === null ? 'Agendar turno' : 'Reprogramar' }}</button>
        <a href="{{ route('turnos.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-control border border-line bg-white px-4 py-2 text-sm font-semibold text-ink hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-gold-600">Cancelar</a>
    </div>
</form>
