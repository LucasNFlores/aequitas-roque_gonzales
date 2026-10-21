<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">{{ __('Nuevo Usuario Interno') }}</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-panel border border-line bg-white p-5 shadow-panel sm:p-6">
                @include('users._form', ['action' => route('users.store'), 'submitLabel' => 'Crear Usuario', 'user' => null])
            </div>
        </div>
    </div>
</x-app-layout>
