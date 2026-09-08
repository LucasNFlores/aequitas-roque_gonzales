<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Estados de proceso') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-lg border-b border-gray-200 bg-white p-6 shadow-sm">
                @livewire('estados-proceso.index')
            </div>
        </div>
    </div>
</x-app-layout>
