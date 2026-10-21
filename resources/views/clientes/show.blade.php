<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-[#172538]">
            {{ __('Detalle de cliente') }}: <span>{{ $cliente->nombre }} {{ $cliente->apellido }}</span>
        </h2>
    </x-slot>

    <div class="px-4 py-8 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <section class="rounded-panel border border-[#dde2e8] bg-white p-4 shadow-panel sm:p-6">
                @if (session('success'))
                    <div class="mb-5 rounded-control border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                        {{ session('success') }}
                    </div>
                @endif
                <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <p class="text-sm font-medium text-[#5b6470]">Nombre</p>
                            <p class="break-words text-base text-[#172538]">{{ $cliente->nombre }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-[#5b6470]">Apellido</p>
                            <p class="break-words text-base text-[#172538]">{{ $cliente->apellido }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-[#5b6470]">DNI</p>
                            <p class="text-base text-[#172538]">{{ $cliente->dni }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-[#5b6470]">Teléfono</p>
                            <p class="text-base text-[#172538]">{{ $cliente->telefono }}</p>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-[#5b6470]">Correo</p>
                            <p class="break-all text-base text-[#172538]">{{ $cliente->correo }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-[#5b6470]">Domicilio</p>
                            <p class="break-words text-base text-[#172538]">{{ $cliente->domicilio }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-[#5b6470]">Fecha de nacimiento</p>
                            <p class="text-base text-[#172538]">{{ $cliente->fecha_nacimiento?->format('d/m/Y') }}</p>
                        </div>
                </div>

                <div class="mt-6 flex flex-col gap-3 border-t border-[#dde2e8] pt-5 sm:flex-row sm:flex-wrap">
                    @can('consultar_legajos')
                        <a href="{{ route('legajos.show', $cliente) }}" class="inline-flex min-h-11 items-center justify-center rounded-control border border-[#c7ced7] bg-white px-4 py-2 text-sm font-semibold text-[#172538] transition hover:bg-[#f5f6f8] focus:outline-none focus:ring-2 focus:ring-[#8c6936] focus:ring-offset-2">
                            Consultar legajo
                        </a>
                    @endcan
                    <a href="{{ route('clientes.edit', $cliente) }}" class="inline-flex min-h-11 items-center justify-center rounded-control bg-[#172538] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#243850] focus:outline-none focus:ring-2 focus:ring-[#8c6936] focus:ring-offset-2">
                        Editar
                    </a>
                    @can('create', App\Models\Proceso::class)
                        <a href="{{ route('clientes.procesos.create', $cliente) }}" class="inline-flex min-h-11 items-center justify-center rounded-control bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                            Nuevo proceso
                        </a>
                    @endcan
                    <a href="{{ route('clientes.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-control border border-[#c7ced7] bg-white px-4 py-2 text-sm font-semibold text-[#172538] shadow-sm transition hover:bg-[#f5f6f8] focus:outline-none focus:ring-2 focus:ring-[#8c6936] focus:ring-offset-2">
                        Volver al listado
                    </a>
                </div>
            </section>

            <section class="mt-6 rounded-panel border border-[#dde2e8] bg-white p-4 shadow-panel sm:p-6">
                <h3 class="text-lg font-semibold text-[#172538]">Procesos del cliente</h3>
                <p class="mt-1 text-sm text-[#5b6470]">Historial de necesidades jurídicas registradas para este cliente.</p>

                <div class="mt-4 hidden overflow-x-auto rounded-card border border-[#dde2e8] sm:block">
                    <table class="w-full text-left text-sm text-[#5b6470]">
                        <thead class="bg-[#f5f6f8] text-xs uppercase tracking-wide text-[#172538]">
                            <tr>
                                <th scope="col" class="px-4 py-2">Proceso</th>
                                <th scope="col" class="px-4 py-2">Servicio</th>
                                <th scope="col" class="px-4 py-2">Estado</th>
                                <th scope="col" class="px-4 py-2">Inicio</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cliente->procesos as $proceso)
                                <tr class="border-t border-[#dde2e8] hover:bg-[#f5f6f8]">
                                    <td class="px-4 py-3 font-medium text-[#172538]">{{ $proceso->nombre }}</td>
                                    <td class="px-4 py-2">{{ $proceso->servicio?->nombre ?? '—' }}</td>
                                    <td class="px-4 py-2">{{ $proceso->estado }}</td>
                                    <td class="px-4 py-2">{{ $proceso->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr class="bg-white">
                                    <td colspan="4" class="px-4 py-6 text-center text-[#5b6470]">
                                        Este cliente aún no tiene procesos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 space-y-3 sm:hidden">
                    @forelse ($cliente->procesos as $proceso)
                        <article class="rounded-card border border-[#dde2e8] bg-white p-4">
                            <h4 class="break-words font-semibold text-[#172538]">{{ $proceso->nombre }}</h4>
                            <dl class="mt-3 grid gap-2 text-sm">
                                <div><dt class="font-medium text-[#5b6470]">Servicio</dt><dd class="break-words text-[#172538]">{{ $proceso->servicio?->nombre ?? '—' }}</dd></div>
                                <div><dt class="font-medium text-[#5b6470]">Estado</dt><dd class="text-[#172538]">{{ $proceso->estado }}</dd></div>
                                <div><dt class="font-medium text-[#5b6470]">Inicio</dt><dd class="text-[#172538]">{{ $proceso->fecha_inicio?->format('d/m/Y') ?? '—' }}</dd></div>
                            </dl>
                        </article>
                    @empty
                        <p role="status" class="rounded-card border border-[#dde2e8] bg-[#f5f6f8] px-4 py-6 text-center text-sm text-[#5b6470]">Este cliente aún no tiene procesos registrados.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
