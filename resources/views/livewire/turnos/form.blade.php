<form wire:submit="save" class="space-y-5">
    @if ($turnoId === null)
        <label class="block">Cliente<select wire:model="clienteId" class="mt-1 block w-full rounded border-gray-300"><option value="">Seleccionar</option>@foreach ($clientes as $cliente)<option value="{{ $cliente->id }}">{{ $cliente->apellido }}, {{ $cliente->nombre }}</option>@endforeach</select></label>
        @error('clienteId')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
        <label class="block">Profesional<select wire:model="profesionalId" class="mt-1 block w-full rounded border-gray-300"><option value="">Seleccionar</option>@foreach ($profesionales as $profesional)<option value="{{ $profesional->id }}">{{ $profesional->name }}</option>@endforeach</select></label>
        @error('profesionalId')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
        <label class="block">Tipo<select wire:model="tipo" class="mt-1 block w-full rounded border-gray-300"><option value="consulta_inicial">Consulta inicial</option>@can('agendar_turnos_seguimiento')<option value="seguimiento">Seguimiento</option>@endcan</select></label>
        @error('tipo')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
        <label class="block">Proceso (obligatorio para seguimiento)<select wire:model="procesoId" class="mt-1 block w-full rounded border-gray-300"><option value="">Sin proceso</option>@foreach ($procesos as $proceso)<option value="{{ $proceso->id }}">#{{ $proceso->id }} — {{ $proceso->cliente?->apellido }}, {{ $proceso->cliente?->nombre }} — {{ $proceso->nombre }}</option>@endforeach</select></label>
        @error('procesoId')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
    @endif
    <label class="block">Fecha y hora<input type="datetime-local" wire:model="fechaHora" class="mt-1 block w-full rounded border-gray-300"></label>
    @error('fechaHora')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
    <div class="flex gap-3"><button class="rounded bg-indigo-600 px-4 py-2 text-white">{{ $turnoId === null ? 'Agendar turno' : 'Reprogramar' }}</button><a href="{{ route('turnos.index') }}" class="rounded border px-4 py-2">Cancelar</a></div>
</form>
