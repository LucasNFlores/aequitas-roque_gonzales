<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-ink">
            {{ __('Nuevo Cliente') }}
        </h2>
    </x-slot>

    <div class="px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl">
            <section class="rounded-panel border border-line bg-white p-4 shadow-panel sm:p-6">
                @include('clientes._form', [
                    'action' => route('clientes.store'),
                    'submitLabel' => 'Crear Cliente',
                ])
            </section>
        </div>
    </div>
</x-app-layout>
