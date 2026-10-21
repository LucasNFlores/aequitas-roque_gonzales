<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">
            {{ __('Nuevo proceso adicional') }}
            @if (! empty($clienteFijo))
                <span class="text-aequitas-900">: {{ $clienteFijo->nombre }} {{ $clienteFijo->apellido }}</span>
            @endif
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-panel border border-line bg-white p-5 shadow-panel sm:p-8">
                @include('procesos._form', [
                    'action' => route('clientes.procesos.store', $clienteFijo),
                    'cancelUrl' => route('clientes.show', $clienteFijo),
                ])
            </div>
        </div>
    </div>
</x-app-layout>
