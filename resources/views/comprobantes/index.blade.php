<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-ink">
            {{ __('Comprobantes de pago') }}
        </h2>
    </x-slot>

    <div class="px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <section class="min-w-0 rounded-panel border border-line bg-white p-4 shadow-panel sm:p-6">
                @livewire('comprobantes.index')
            </section>
        </div>
    </div>
</x-app-layout>
