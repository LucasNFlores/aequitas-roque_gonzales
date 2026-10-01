<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-800">Gestión de turnos</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8"><div class="flex items-center justify-between"><p>Turnos internos y de seguimiento. Las cancelaciones se conservan en el historial.</p><div class="flex gap-3"><a class="text-indigo-700 underline" href="{{ route('turnos.agenda') }}">Agenda</a>@can('create', App\Models\Turno::class)<a class="text-indigo-700 underline" href="{{ route('turnos.create') }}">Nuevo turno</a>@endcan</div></div><section class="rounded bg-white p-6 shadow"><livewire:turnos.index /></section></div></div>
</x-app-layout>
