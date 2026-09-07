<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle de Cliente') }}: <span class="text-indigo-600">{{ $cliente->nombre }} {{ $cliente->apellido }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Nombre</p>
                            <p class="text-base text-gray-900">{{ $cliente->nombre }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Apellido</p>
                            <p class="text-base text-gray-900">{{ $cliente->apellido }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">DNI</p>
                            <p class="text-base text-gray-900">{{ $cliente->dni }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Teléfono</p>
                            <p class="text-base text-gray-900">{{ $cliente->telefono }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-500">Correo</p>
                        <p class="text-base text-gray-900">{{ $cliente->correo }}</p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-500">Domicilio</p>
                        <p class="text-base text-gray-900">{{ $cliente->domicilio }}</p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-500">Fecha de nacimiento</p>
                        <p class="text-base text-gray-900">{{ $cliente->fecha_nacimiento?->format('d/m/Y') }}</p>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6 mt-6 flex space-x-3">
                    <a href="{{ route('clientes.edit', $cliente) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Editar
                    </a>
                    <a href="{{ route('clientes.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Volver al listado
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
