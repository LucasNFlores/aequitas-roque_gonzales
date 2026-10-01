<form action="{{ $action }}" method="POST" class="space-y-4">
    @csrf
    @if(isset($turno))
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-forms.input-label for="cliente_id" :value="__('Cliente')" />
            <select id="cliente_id" name="cliente_id" required class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                <option value="">Seleccione un cliente</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}" @selected(old('cliente_id', $turno->cliente_id ?? '') == $cliente->id)>
                        {{ $cliente->apellido }}, {{ $cliente->nombre }} (DNI {{ $cliente->dni }})
                    </option>
                @endforeach
            </select>
            <x-forms.input-error :messages="$errors->get('cliente_id')" class="mt-2" />
        </div>

        <div>
            <x-forms.input-label for="profesional_id" :value="__('Profesional')" />
            <select id="profesional_id" name="profesional_id" required class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                <option value="">Seleccione un profesional</option>
                @foreach($profesionales as $profesional)
                    <option value="{{ $profesional->id }}" @selected(old('profesional_id', $turno->profesional_id ?? '') == $profesional->id)>
                        {{ $profesional->name }}
                    </option>
                @endforeach
            </select>
            <x-forms.input-error :messages="$errors->get('profesional_id')" class="mt-2" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-forms.input-label for="proceso_id" :value="__('Proceso (requerido para seguimiento)')" />
            <select id="proceso_id" name="proceso_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                <option value="">Sin proceso (solo consulta inicial)</option>
                @foreach($procesos as $proceso)
                    <option value="{{ $proceso->id }}" data-cliente="{{ $proceso->cliente_id }}" @selected(old('proceso_id', $turno->proceso_id ?? '') == $proceso->id)>
                        #{{ $proceso->id }} — {{ $proceso->nombre }} ({{ $proceso->estado }})
                    </option>
                @endforeach
            </select>
            <x-forms.input-error :messages="$errors->get('proceso_id')" class="mt-2" />
        </div>

        <div>
            <x-forms.input-label for="tipo" :value="__('Tipo de turno')" />
            <select id="tipo" name="tipo" required class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                @foreach($tipos as $valor => $etiqueta)
                    <option value="{{ $valor }}" @selected(old('tipo', $turno->tipo ?? 'consulta_inicial') === $valor)>{{ $etiqueta }}</option>
                @endforeach
            </select>
            <x-forms.input-error :messages="$errors->get('tipo')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-forms.input-label for="fecha_hora" :value="__('Fecha y hora (duración 60 min)')" />
        <x-forms.text-input id="fecha_hora" class="block mt-1 w-full" type="datetime-local" name="fecha_hora" :value="old('fecha_hora', isset($turno) ? $turno->fecha_hora?->format('Y-m-d\TH:i') : '')" required />
        <x-forms.input-error :messages="$errors->get('fecha_hora')" class="mt-2" />
    </div>

    <div class="border-t border-gray-200 pt-4 flex space-x-3">
        <x-buttons.primary-button>{{ $submitLabel }}</x-buttons.primary-button>
        <a href="{{ route('turnos.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">Cancelar</a>
    </div>
</form>
