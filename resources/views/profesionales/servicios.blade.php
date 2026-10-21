<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">
            {{ __('Servicios por profesional') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-panel border border-line bg-white p-5 shadow-panel sm:p-6">
                @livewire('profesionales.servicios')
            </div>
        </div>
    </div>
</x-app-layout>
