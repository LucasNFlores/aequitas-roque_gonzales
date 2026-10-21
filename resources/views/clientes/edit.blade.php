<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-[#172538]">
            {{ __('Editar cliente') }}: <span>{{ $cliente->nombre }} {{ $cliente->apellido }}</span>
        </h2>
    </x-slot>

    <div class="px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl">
            <section class="rounded-panel border border-[#dde2e8] bg-white p-4 shadow-panel sm:p-6">
                @include('clientes._form', [
                    'action' => route('clientes.update', $cliente),
                    'submitLabel' => 'Guardar Cambios',
                ])
            </section>
        </div>
    </div>
</x-app-layout>
