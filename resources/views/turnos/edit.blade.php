<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Reprogramar Turno') }} #{{ $turno->id }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-500 mb-4">Fecha actual: <strong>{{ $turno->fecha_hora?->format('d/m/Y H:i') }}</strong>. Al guardar se libera la disponibilidad anterior.</p>
                @include('turnos._form', ['action' => route('turnos.update', $turno), 'submitLabel' => 'Guardar cambios', 'turno' => $turno])
            </div>
        </div>
    </div>
</x-app-layout>
