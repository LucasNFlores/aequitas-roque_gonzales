<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">
            {{ __('Gestión de Usuarios del Sistema') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-panel border border-line bg-white p-5 shadow-panel sm:p-6">
                @if(session('success'))
                    <div class="mb-6 rounded border border-green-400 bg-green-100 px-4 py-3 text-positive" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                @livewire('usuarios.index')
            </div>
        </div>
    </div>
</x-app-layout>
