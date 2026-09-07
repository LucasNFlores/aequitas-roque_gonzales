<form action="{{ $action }}" method="POST" class="space-y-4">
    @csrf
    @if(isset($servicio))
        @method('PUT')
    @endif

    <div>
        <label for="nombre" class="block text-sm font-medium text-gray-700">{{ __('Nombre del servicio') }}</label>
        <input id="nombre" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" name="nombre" value="{{ old('nombre', $servicio->nombre ?? '') }}" required autofocus />
        @error('nombre')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="costo_servicio" class="block text-sm font-medium text-gray-700">{{ __('Costo del servicio') }}</label>
        <input id="costo_servicio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="number" step="0.01" min="0" name="costo_servicio" value="{{ old('costo_servicio', $servicio->costo_servicio ?? '') }}" required />
        @error('costo_servicio')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="border-t border-gray-200 pt-4 flex space-x-3">
        <button type="submit" class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('servicios-viejo.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            Cancelar
        </a>
    </div>
</form>
