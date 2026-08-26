<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Servicio') }}: <span class="text-indigo-600">{{ $servicio->nombre }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @include('servicios._form', [
                    'action' => route('servicios.update', $servicio),
                    'submitLabel' => 'Guardar Cambios',
                ])
            </div>
        </div>
    </div>
</x-app-layout>
