<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Clientes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-b border-gray-200">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Listado de Clientes</h3>
                        <p class="text-sm text-gray-500 mt-1">Administra los clientes del estudio.</p>
                    </div>
                    @can('create', App\Models\Cliente::class)
                        <a href="{{ route('clientes.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Nuevo Cliente
                        </a>
                    @endcan
                </div>

                <div class="overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">Nombre</th>
                                <th scope="col" class="px-6 py-3">DNI</th>
                                <th scope="col" class="px-6 py-3">Correo</th>
                                <th scope="col" class="px-6 py-3">Teléfono</th>
                                <th scope="col" class="px-6 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clientes as $cliente)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                        <a href="{{ route('clientes.show', $cliente) }}" class="hover:text-indigo-600 hover:underline">
                                            {{ $cliente->nombre }} {{ $cliente->apellido }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $cliente->dni }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $cliente->correo }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $cliente->telefono }}
                                    </td>
                                    <td class="px-6 py-4 text-center space-x-2">
                                        @can('view', $cliente)
                                            <a href="{{ route('clientes.show', $cliente) }}" class="font-medium text-gray-600 hover:text-gray-900 bg-gray-50 px-3 py-2 rounded-md transition">
                                                Ver
                                            </a>
                                        @endcan
                                        @can('update', $cliente)
                                            <a href="{{ route('clientes.edit', $cliente) }}" class="font-medium text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-2 rounded-md transition">
                                                Editar
                                            </a>
                                        @endcan
                                        @can('delete', $cliente)
                                            <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Seguro que deseas dar de baja este cliente?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-medium text-red-600 hover:text-red-900 bg-red-50 px-3 py-2 rounded-md transition">
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr class="bg-white">
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500 italic">
                                        No hay clientes cargados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $clientes->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
