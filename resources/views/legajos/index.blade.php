<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-aequitas-900">Consulta CU18</p>
            <h1 class="mt-1 text-xl font-semibold leading-tight text-ink">Legajos jurídicos</h1>
        </div>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-panel border border-line bg-white p-5 shadow-panel sm:p-7">
                <h2 class="text-lg font-semibold text-ink">Buscar un legajo</h2>
                <p class="mt-1 text-sm text-muted">Buscá por DNI, nombre o apellido. Solo aparecen los clientes con procesos que podés consultar.</p>

                @livewire('legajos.index')
            </section>
        </div>
    </div>
</x-app-layout>
