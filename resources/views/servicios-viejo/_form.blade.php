<form action="{{ $action }}" method="POST" class="space-y-4">
    @csrf
    @if(isset($servicio))
        @method('PUT')
    @endif

    <div>
        <x-input-label for="nombre" :value="__('Nombre del servicio')" />
        <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre', $servicio->nombre ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="costo_servicio" :value="__('Costo del servicio')" />
        <x-text-input id="costo_servicio" class="block mt-1 w-full" type="number" step="0.01" min="0" name="costo_servicio" :value="old('costo_servicio', $servicio->costo_servicio ?? '')" required />
        <x-input-error :messages="$errors->get('costo_servicio')" class="mt-2" />
    </div>

    <div class="border-t border-gray-200 pt-4 flex space-x-3">
        <x-primary-button>
            {{ $submitLabel }}
        </x-primary-button>
        <a href="{{ route('servicios-viejo.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            Cancelar
        </a>
    </div>
</form>
