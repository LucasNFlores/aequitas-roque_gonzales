<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Editar Usuario: ') }} {{ $user->name }}</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @include('users._form', ['action' => route('users.update', $user), 'submitLabel' => 'Guardar Cambios', 'user' => $user, 'method' => 'PUT'])
            </div>
        </div>
    </div>
</x-app-layout>
