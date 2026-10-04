<form wire:submit="save" class="space-y-5">
    @if ($turnoId === null)
        <label class="block">Cliente<select wire:model="clienteId" class="mt-1 block w-full rounded border-gray-300"><option value="">Seleccionar</option>@foreach ($clientes as $cliente)<option value="{{ $cliente->id }}">{{ $cliente->apellido }}, {{ $cliente->nombre }}</option>@endforeach</select></label>
        @error('clienteId')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
        <label class="block">Profesional<select wire:model="profesionalId" class="mt-1 block w-full rounded border-gray-300"><option value="">Seleccionar</option>@foreach ($profesionales as $profesional)<option value="{{ $profesional->id }}">{{ $profesional->name }}</option>@endforeach</select></label>
        @error('profesionalId')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
        <label class="block">Proceso (opcional)<select wire:model="procesoId" class="mt-1 block w-full rounded border-gray-300"><option value="">Sin proceso</option>@foreach ($procesos as $proceso)<option value="{{ $proceso->id }}">#{{ $proceso->id }} — {{ $proceso->cliente?->apellido }}, {{ $proceso->cliente?->nombre }} — {{ $proceso->nombre }}</option>@endforeach</select></label>
        @error('procesoId')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
    @else
        <dl class="grid gap-3 rounded bg-gray-50 p-4 sm:grid-cols-2">
            <div><dt class="font-semibold">Cliente</dt><dd>{{ $turno?->cliente?->apellido }}, {{ $turno?->cliente?->nombre }}</dd></div>
            <div><dt class="font-semibold">Profesional</dt><dd>{{ $turno?->profesional?->name }}</dd></div>
            <div><dt class="font-semibold">Proceso</dt><dd>{{ $turno?->proceso?->nombre ?? '—' }}</dd></div>
        </dl>
    @endif

    <label class="block">Fecha y hora<input type="datetime-local" wire:model="fechaHora" class="mt-1 block w-full rounded border-gray-300"></label>
    @error('fechaHora')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
    <label class="block">Detalle del compromiso externo<textarea wire:model="detalleExterno" rows="4" class="mt-1 block w-full rounded border-gray-300"></textarea></label>
    @error('detalleExterno')<p class="text-sm text-red-700">{{ $message }}</p>@enderror

    <div class="flex flex-wrap gap-3">
        <button class="rounded bg-indigo-600 px-4 py-2 text-white">{{ $turnoId === null ? 'Registrar compromiso' : 'Guardar reprogramación' }}</button>
        <a href="{{ route('turnos.index') }}" class="rounded border px-4 py-2">Volver a turnos</a>
    </div>
</form>
