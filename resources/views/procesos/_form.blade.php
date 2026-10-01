<form id="proceso-adicional-form" action="{{ $action }}" method="POST" class="space-y-4" onsubmit="const btn = document.getElementById('proceso-adicional-submit'); if (btn) { btn.disabled = true; btn.textContent = 'Guardando...'; }">
    @csrf
    <input type="hidden" name="submission_token" value="{{ old('submission_token', $submissionToken) }}" />

    @if ($clienteFijo)
        <div>
            <x-forms.input-label for="cliente_nombre" :value="__('Cliente')" />
            <x-forms.text-input id="cliente_nombre" class="block mt-1 w-full bg-gray-50" type="text" :value="$clienteFijo->nombre.' '.$clienteFijo->apellido.' (DNI '.$clienteFijo->dni.')'" disabled />
            <p class="mt-1 text-xs text-gray-500">El proceso se vinculará a este cliente existente. No se duplican ni modifican sus datos.</p>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-forms.input-label for="servicio_id" :value="__('Servicio')" />
            <select id="servicio_id" name="servicio_id" required class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Seleccionar servicio</option>
                @foreach ($servicios as $servicio)
                    <option value="{{ $servicio->id }}" @selected((string) old('servicio_id') === (string) $servicio->id)>{{ $servicio->nombre }}</option>
                @endforeach
            </select>
            <x-forms.input-error :messages="$errors->get('servicio_id')" class="mt-2" />
        </div>

        <div>
            <x-forms.input-label for="tipo" :value="__('Tipo')" />
            <select id="tipo" name="tipo" required class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Seleccionar tipo</option>
                @foreach (\App\Models\Proceso::TIPOS as $tipo)
                    <option value="{{ $tipo }}" @selected(old('tipo') === $tipo)>{{ $tipo }}</option>
                @endforeach
            </select>
            <x-forms.input-error :messages="$errors->get('tipo')" class="mt-2" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-forms.input-label for="coordinador_id" :value="__('Coordinador')" />
            <select id="coordinador_id" name="coordinador_id" required class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Seleccionar coordinador</option>
                @foreach ($coordinadores as $coordinador)
                    <option value="{{ $coordinador->id }}" @selected((string) old('coordinador_id') === (string) $coordinador->id)>{{ $coordinador->name }}</option>
                @endforeach
            </select>
            <x-forms.input-error :messages="$errors->get('coordinador_id')" class="mt-2" />
        </div>

        <div>
            <x-forms.input-label for="profesional_id" :value="__('Profesional (opcional)')" />
            <select id="profesional_id" name="profesional_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Sin asignar</option>
                @foreach ($profesionales as $profesional)
                    <option value="{{ $profesional->id }}" @selected((string) old('profesional_id') === (string) $profesional->id)>{{ $profesional->name }}</option>
                @endforeach
            </select>
            <x-forms.input-error :messages="$errors->get('profesional_id')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-forms.input-label for="nombre" :value="__('Nombre del proceso')" />
        <x-forms.text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre')" required autofocus maxlength="255" />
        <x-forms.input-error :messages="$errors->get('nombre')" class="mt-2" />
    </div>

    <div>
        <x-forms.input-label for="descripcion" :value="__('Descripción')" />
        <textarea id="descripcion" name="descripcion" rows="4" required maxlength="10000" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('descripcion') }}</textarea>
        <x-forms.input-error :messages="$errors->get('descripcion')" class="mt-2" />
    </div>

    <div>
        <x-forms.input-label for="fecha_inicio" :value="__('Fecha de inicio')" />
        <x-forms.text-input id="fecha_inicio" class="block mt-1 w-full" type="date" name="fecha_inicio" :value="old('fecha_inicio')" required />
        <x-forms.input-error :messages="$errors->get('fecha_inicio')" class="mt-2" />
    </div>

    <p class="text-xs text-gray-500">El proceso se crea en estado pendiente y sigue el flujo de admisión o rechazo. No se crea ningún turno automáticamente.</p>

    @if ($errors->has('estado'))
        <p class="text-sm text-red-600">{{ $errors->first('estado') }}</p>
    @endif

    <div class="border-t border-gray-200 pt-4 flex space-x-3">
        <button id="proceso-adicional-submit" type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150">
            Crear proceso
        </button>
        <a href="{{ $cancelUrl }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            Cancelar
        </a>
    </div>
</form>
