<form action="{{ $action }}" method="POST" class="space-y-4">
    @csrf
    @if(isset($cliente))
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-input-label for="nombre" :value="__('Nombre')" />
            <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre', $cliente->nombre ?? '')" required autofocus />
            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="apellido" :value="__('Apellido')" />
            <x-text-input id="apellido" class="block mt-1 w-full" type="text" name="apellido" :value="old('apellido', $cliente->apellido ?? '')" required />
            <x-input-error :messages="$errors->get('apellido')" class="mt-2" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-input-label for="dni" :value="__('DNI')" />
            <x-text-input id="dni" class="block mt-1 w-full" type="text" name="dni" :value="old('dni', $cliente->dni ?? '')" required />
            <x-input-error :messages="$errors->get('dni')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="telefono" :value="__('Teléfono')" />
            <x-text-input id="telefono" class="block mt-1 w-full" type="text" name="telefono" :value="old('telefono', $cliente->telefono ?? '')" required />
            <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="correo" :value="__('Correo electrónico')" />
        <x-text-input id="correo" class="block mt-1 w-full" type="email" name="correo" :value="old('correo', $cliente->correo ?? '')" required />
        <x-input-error :messages="$errors->get('correo')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="domicilio" :value="__('Domicilio')" />
        <x-text-input id="domicilio" class="block mt-1 w-full" type="text" name="domicilio" :value="old('domicilio', $cliente->domicilio ?? '')" required />
        <x-input-error :messages="$errors->get('domicilio')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="fecha_nacimiento" :value="__('Fecha de nacimiento')" />
        <x-text-input id="fecha_nacimiento" class="block mt-1 w-full" type="date" name="fecha_nacimiento" :value="old('fecha_nacimiento', isset($cliente) ? $cliente->fecha_nacimiento?->format('Y-m-d') : '')" required />
        <x-input-error :messages="$errors->get('fecha_nacimiento')" class="mt-2" />
    </div>

    <div class="border-t border-gray-200 pt-4 flex space-x-3">
        <x-primary-button>
            {{ $submitLabel }}
        </x-primary-button>
        <a href="{{ route('clientes.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            Cancelar
        </a>
    </div>
</form>
