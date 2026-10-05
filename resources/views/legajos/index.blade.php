<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-indigo-600">Consulta CU18</p>
            <h1 class="mt-1 text-xl font-semibold leading-tight text-gray-900">Legajos jurídicos</h1>
        </div>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7">
                <h2 class="text-lg font-semibold text-gray-900">Buscar un legajo</h2>
                <p class="mt-1 text-sm text-gray-600">Buscá por DNI, nombre o apellido. Solo aparecen los clientes con procesos que podés consultar.</p>

                @livewire('legajos.index')
            </section>
        </div>
    </div>
</x-app-layout>
