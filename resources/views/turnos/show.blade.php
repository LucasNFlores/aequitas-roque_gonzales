<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-800">Detalle del turno</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8"><section class="rounded bg-white p-6 shadow"><livewire:turnos.show :turno="$turno" /></section></div></div>
</x-app-layout>
