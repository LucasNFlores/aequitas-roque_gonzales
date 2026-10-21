<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-ink">
            {{ __('Detalle de cliente') }}: <span>{{ $cliente->nombre }} {{ $cliente->apellido }}</span>
        </h2>
    </x-slot>

    <div class="px-4 py-8 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <section class="rounded-panel border border-line bg-white p-4 shadow-panel sm:p-6">
                @if (session('success'))
                    <div class="mb-5 rounded-control border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                        {{ session('success') }}
                    </div>
                @endif
                <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <p class="text-sm font-medium text-muted">Nombre</p>
                            <p class="break-words text-base text-ink">{{ $cliente->nombre }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted">Apellido</p>
                            <p class="break-words text-base text-ink">{{ $cliente->apellido }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted">DNI</p>
                            <p class="text-base text-ink">{{ $cliente->dni }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted">Teléfono</p>
                            <p class="text-base text-ink">{{ $cliente->telefono }}</p>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-muted">Correo</p>
                            <p class="break-all text-base text-ink">{{ $cliente->correo }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted">Domicilio</p>
                            <p class="break-words text-base text-ink">{{ $cliente->domicilio }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted">Fecha de nacimiento</p>
                            <p class="text-base text-ink">{{ $cliente->fecha_nacimiento?->format('d/m/Y') }}</p>
                        </div>
                </div>

                <div class="mt-6 flex flex-col gap-3 border-t border-line pt-5 sm:flex-row sm:flex-wrap">
                    @can('consultar_legajos')
                        <a href="{{ route('legajos.show', $cliente) }}" class="inline-flex min-h-11 items-center justify-center rounded-control border border-line bg-white px-4 py-2 text-sm font-semibold text-ink transition hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">
                            Consultar legajo
                        </a>
                    @endcan
                    <a href="{{ route('clientes.edit', $cliente) }}" class="inline-flex min-h-11 items-center justify-center rounded-control bg-aequitas-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-aequitas-800 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">
                        Editar
                    </a>
                    @can('create', App\Models\Proceso::class)
                        <a href="{{ route('clientes.procesos.create', $cliente) }}" class="inline-flex min-h-11 items-center justify-center rounded-control bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                            Nuevo proceso
                        </a>
                    @endcan
                    <a href="{{ route('clientes.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-control border border-line bg-white px-4 py-2 text-sm font-semibold text-ink shadow-sm transition hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2">
                        Volver al listado
                    </a>
                </div>
            </section>

            <section class="mt-6 rounded-panel border border-line bg-white p-4 shadow-panel sm:p-6">
                <h3 class="text-lg font-semibold text-ink">Procesos del cliente</h3>
                <p class="mt-1 text-sm text-muted">Historial de necesidades jurídicas registradas para este cliente.</p>

                <div class="mt-4 hidden overflow-x-auto rounded-card border border-line sm:block">
                    <table class="w-full text-left text-sm text-muted">
                        <thead class="bg-canvas text-xs uppercase tracking-wide text-ink">
                            <tr>
                                <th scope="col" class="px-4 py-2">Proceso</th>
                                <th scope="col" class="px-4 py-2">Servicio</th>
                                <th scope="col" class="px-4 py-2">Estado</th>
                                <th scope="col" class="px-4 py-2">Inicio</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cliente->procesos as $proceso)
                                <tr class="border-t border-line hover:bg-canvas">
                                    <td class="px-4 py-3 font-medium text-ink">{{ $proceso->nombre }}</td>
                                    <td class="px-4 py-2">{{ $proceso->servicio?->nombre ?? '—' }}</td>
                                    <td class="px-4 py-2">{{ $proceso->estado }}</td>
                                    <td class="px-4 py-2">{{ $proceso->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr class="bg-white">
                                    <td colspan="4" class="px-4 py-6 text-center text-muted">
                                        Este cliente aún no tiene procesos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 space-y-3 sm:hidden">
                    @forelse ($cliente->procesos as $proceso)
                        <article class="rounded-card border border-line bg-white p-4">
                            <h4 class="break-words font-semibold text-ink">{{ $proceso->nombre }}</h4>
                            <dl class="mt-3 grid gap-2 text-sm">
                                <div><dt class="font-medium text-muted">Servicio</dt><dd class="break-words text-ink">{{ $proceso->servicio?->nombre ?? '—' }}</dd></div>
                                <div><dt class="font-medium text-muted">Estado</dt><dd class="text-ink">{{ $proceso->estado }}</dd></div>
                                <div><dt class="font-medium text-muted">Inicio</dt><dd class="text-ink">{{ $proceso->fecha_inicio?->format('d/m/Y') ?? '—' }}</dd></div>
                            </dl>
                        </article>
                    @empty
                        <p role="status" class="rounded-card border border-line bg-canvas px-4 py-6 text-center text-sm text-muted">Este cliente aún no tiene procesos registrados.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
