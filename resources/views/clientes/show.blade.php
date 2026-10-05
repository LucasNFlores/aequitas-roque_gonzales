<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle de Cliente') }}: <span class="text-indigo-600">{{ $cliente->nombre }} {{ $cliente->apellido }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif
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

                <div class="border-t border-gray-200 pt-6 mt-6 flex flex-wrap gap-3">
                    @can('consultar_legajos')
                        <a href="{{ route('legajos.show', $cliente) }}" class="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-indigo-700 transition hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Consultar legajo
                        </a>
                    @endcan
                    <a href="{{ route('clientes.edit', $cliente) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Editar
                    </a>
                    @can('create', App\Models\Proceso::class)
                        <a href="{{ route('clientes.procesos.create', $cliente) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Nuevo proceso
                        </a>
                    @endcan
                    <a href="{{ route('clientes.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Volver al listado
                    </a>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mt-6">
                <h3 class="text-lg font-medium text-gray-900">Procesos del cliente</h3>
                <p class="text-sm text-gray-500 mt-1">Historial de necesidades jurídicas registradas para este cliente.</p>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-2">Proceso</th>
                                <th scope="col" class="px-4 py-2">Servicio</th>
                                <th scope="col" class="px-4 py-2">Estado</th>
                                <th scope="col" class="px-4 py-2">Inicio</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cliente->procesos as $proceso)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-4 py-2 font-medium text-gray-900">{{ $proceso->nombre }}</td>
                                    <td class="px-4 py-2">{{ $proceso->servicio?->nombre ?? '—' }}</td>
                                    <td class="px-4 py-2">{{ $proceso->estado }}</td>
                                    <td class="px-4 py-2">{{ $proceso->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr class="bg-white">
                                    <td colspan="4" class="px-4 py-3 text-center text-gray-500 italic">
                                        Este cliente aún no tiene procesos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
