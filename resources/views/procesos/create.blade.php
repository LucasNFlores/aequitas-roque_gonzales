<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nuevo proceso adicional') }}
            @if (! empty($clienteFijo))
                <span class="text-indigo-600">: {{ $clienteFijo->nombre }} {{ $clienteFijo->apellido }}</span>
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @include('procesos._form', [
                    'action' => route('clientes.procesos.store', $clienteFijo),
                    'cancelUrl' => route('clientes.show', $clienteFijo),
                ])
            </div>
        </div>
    </div>
</x-app-layout>
