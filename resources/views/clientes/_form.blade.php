<form action="{{ $action }}" method="POST" class="space-y-4">
    @csrf
    @if(isset($cliente))
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-forms.input-label for="nombre" :value="__('Nombre')" />
            <x-forms.text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre', $cliente->nombre ?? '')" required autofocus />
            <x-forms.input-error :messages="$errors->get('nombre')" class="mt-2" />
        </div>

        <div>
            <x-forms.input-label for="apellido" :value="__('Apellido')" />
            <x-forms.text-input id="apellido" class="block mt-1 w-full" type="text" name="apellido" :value="old('apellido', $cliente->apellido ?? '')" required />
            <x-forms.input-error :messages="$errors->get('apellido')" class="mt-2" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-forms.input-label for="dni" :value="__('DNI')" />
            <x-forms.text-input id="dni" class="block mt-1 w-full" type="text" name="dni" :value="old('dni', $cliente->dni ?? '')" required />
            <x-forms.input-error :messages="$errors->get('dni')" class="mt-2" />
        </div>

        <div>
            <x-forms.input-label for="telefono" :value="__('Teléfono')" />
            <x-forms.text-input id="telefono" class="block mt-1 w-full" type="text" name="telefono" :value="old('telefono', $cliente->telefono ?? '')" required />
            <x-forms.input-error :messages="$errors->get('telefono')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-forms.input-label for="correo" :value="__('Correo electrónico')" />
        <x-forms.text-input id="correo" class="block mt-1 w-full" type="email" name="correo" :value="old('correo', $cliente->correo ?? '')" required />
        <x-forms.input-error :messages="$errors->get('correo')" class="mt-2" />
    </div>

    <div>
        <x-forms.input-label for="domicilio" :value="__('Domicilio')" />
        <x-forms.text-input id="domicilio" class="block mt-1 w-full" type="text" name="domicilio" :value="old('domicilio', $cliente->domicilio ?? '')" required />
        <x-forms.input-error :messages="$errors->get('domicilio')" class="mt-2" />
    </div>

    <div>
        <x-forms.input-label for="fecha_nacimiento" :value="__('Fecha de nacimiento')" />
        <x-forms.text-input id="fecha_nacimiento" class="block mt-1 w-full" type="date" name="fecha_nacimiento" :value="old('fecha_nacimiento', isset($cliente) ? $cliente->fecha_nacimiento?->format('Y-m-d') : '')" required />
        <x-forms.input-error :messages="$errors->get('fecha_nacimiento')" class="mt-2" />
    </div>

    <div class="border-t border-gray-200 pt-4 flex space-x-3">
        <x-buttons.primary-button>
            {{ $submitLabel }}
        </x-buttons.primary-button>
        <a href="{{ route('clientes.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            Cancelar
        </a>
    </div>
</form>
