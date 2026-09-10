<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $user->name }}</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-4">
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>DNI:</strong> {{ $user->dni ?? '—' }}</p>
                <p><strong>Teléfono:</strong> {{ $user->telefono ?? '—' }}</p>
                <p><strong>Roles:</strong> {{ $user->roles->pluck('name')->implode(', ') ?: 'Sin rol' }}</p>
                <p><strong>Servicios:</strong> {{ $user->servicios->pluck('nombre')->implode(', ') ?: '—' }}</p>
                <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">Volver</a>
            </div>
        </div>
    </div>
</x-app-layout>
