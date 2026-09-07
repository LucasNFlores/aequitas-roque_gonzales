<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nuevo Cliente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @include('clientes._form', [
                    'action' => route('clientes.store'),
                    'submitLabel' => 'Crear Cliente',
                ])
            </div>
        </div>
    </div>
</x-app-layout>
