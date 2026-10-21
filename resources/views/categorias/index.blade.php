<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-ink">
            {{ __('Categorías de documentos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-panel border border-line bg-white p-5 shadow-panel sm:p-6">
                @livewire('categorias.index')
            </div>
        </div>
    </div>
</x-app-layout>
