<div x-data="{ selectedFile: '' }" x-on:comprobante-guardado.window="selectedFile = ''" class="min-w-0">
    <div class="flex flex-col gap-2 border-b border-[#dde2e8] pb-5">
        <h3 class="text-lg font-semibold text-[#172538]">Registrar comprobante de pago</h3>
        <p class="text-sm text-[#5b6470]">Busca primero al cliente por DNI y adjunta el PDF descargado desde ARCA.</p>
    </div>

    @if($successMessage !== '')
        <div class="mt-5 rounded-control border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
            {{ $successMessage }}
        </div>
    @endif

    @can('create', \App\Models\ComprobantePago::class)
        <form wire:submit="saveComprobante" class="mt-5 grid min-w-0 gap-5 lg:grid-cols-5">
            <section class="min-w-0 rounded-card border border-[#dde2e8] bg-[#f5f6f8] p-4 lg:col-span-3 sm:p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h4 class="font-semibold text-[#172538]">Datos del comprobante</h4>
                        <p class="mt-1 text-sm text-[#5b6470]">Los campos marcados son obligatorios.</p>
                    </div>
                    <span class="inline-flex w-fit rounded-full bg-[#edf1f5] px-3 py-1 text-xs font-semibold text-[#172538]">PDF hasta 20 MB</span>
                </div>

                <div class="mt-5 min-w-0">
                    <label for="comprobante-dni" class="block text-sm font-medium text-[#172538]">DNI del cliente <span class="text-red-700">*</span></label>
                    <input id="comprobante-dni" type="search" inputmode="numeric" wire:model.live.debounce.300ms="dni" placeholder="Ingresá al menos 3 dígitos" autocomplete="off" class="mt-2 block min-h-11 w-full rounded-control border-[#c7ced7] bg-white px-3 py-2.5 text-[#172538] shadow-sm placeholder:text-[#697483] focus:border-[#8c6936] focus:ring-[#8c6936]">

                    @if($clienteId === null && $dni !== '' && mb_strlen(trim($dni)) < 3)
                        <p class="mt-2 text-sm text-[#5b6470]">Ingresá al menos 3 dígitos para buscar.</p>
                    @endif

                    @if($clienteId === null && $clientes->isNotEmpty())
                        <div class="mt-2 overflow-hidden rounded-control border border-[#dde2e8] bg-white shadow-sm">
                            @foreach($clientes as $cliente)
                                <button type="button" wire:click="selectCliente({{ $cliente->id }})" class="flex min-h-12 w-full flex-col items-start justify-center gap-1 border-b border-[#dde2e8] px-4 py-3 text-left last:border-b-0 hover:bg-[#f5f6f8] focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#8c6936] sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                                    <span class="font-medium text-[#172538]">{{ $cliente->apellido }}, {{ $cliente->nombre }}</span>
                                    <span class="text-sm text-[#5b6470]">DNI {{ $cliente->dni }}</span>
                                </button>
                            @endforeach
                        </div>
                    @elseif($clienteId === null && mb_strlen(trim($dni)) >= 3)
                        <p class="mt-2 text-sm text-[#5b6470]">No se encontraron clientes con ese DNI.</p>
                    @endif

                    @error('clienteId')
                        <span role="alert" class="mt-2 block text-sm text-red-700">{{ $message }}</span>
                    @enderror
                </div>

                @if($selectedCliente)
                    <div class="mt-4 flex min-w-0 flex-col gap-3 rounded-card border border-[#c7ced7] bg-white p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wide text-[#5b6470]">Cliente seleccionado</p>
                            <p class="mt-1 break-words font-medium text-[#172538]">{{ $selectedCliente->apellido }}, {{ $selectedCliente->nombre }}</p>
                            <p class="text-sm text-[#5b6470]">DNI {{ $selectedCliente->dni }}</p>
                        </div>
                        <button type="button" wire:click="clearCliente" class="inline-flex min-h-11 w-fit items-center rounded-control px-3 py-2 text-sm font-semibold text-[#172538] underline decoration-[#8c6936] underline-offset-4 hover:bg-[#edf1f5] focus:outline-none focus:ring-2 focus:ring-[#8c6936]">Cambiar</button>
                    </div>
                @endif

                <div class="mt-5 grid min-w-0 gap-5 sm:grid-cols-2">
                    <div class="min-w-0">
                        <label for="comprobante-fecha" class="block text-sm font-medium text-[#172538]">Fecha de carga <span class="text-red-700">*</span></label>
                        <input id="comprobante-fecha" type="date" wire:model.defer="fechaSubida" max="{{ now()->toDateString() }}" class="mt-2 block min-h-11 w-full rounded-control border-[#c7ced7] bg-white px-3 py-2.5 text-[#172538] shadow-sm focus:border-[#8c6936] focus:ring-[#8c6936]">
                        @error('fechaSubida')
                            <span role="alert" class="mt-1 block text-sm text-red-700">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="min-w-0">
                        <label for="comprobante-archivo" class="block text-sm font-medium text-[#172538]">Archivo PDF <span class="text-red-700">*</span></label>
                        <input id="comprobante-archivo" type="file" accept="application/pdf,.pdf" wire:model="archivo" @change="selectedFile = $event.target.files[0]?.name ?? ''" class="mt-2 block min-h-11 w-full max-w-full cursor-pointer rounded-control border border-[#c7ced7] bg-white px-2 py-2 text-sm text-[#172538] shadow-sm file:mr-2 file:rounded-control file:border-0 file:bg-[#edf1f5] file:px-2 file:py-1.5 file:text-xs file:font-semibold file:text-[#172538] hover:file:bg-[#dde2e8] focus:border-[#8c6936] focus:ring-[#8c6936] sm:px-3 sm:file:mr-3 sm:file:px-3 sm:file:text-sm">
                        <p x-show="selectedFile" x-text="selectedFile" class="mt-2 break-all text-sm text-[#5b6470]"></p>
                        <p wire:loading wire:target="archivo" class="mt-2 text-sm text-[#5b6470]">Preparando archivo...</p>
                        @error('archivo')
                            <span role="alert" class="mt-1 block text-sm text-red-700">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mt-5">
                    <label for="comprobante-descripcion" class="block text-sm font-medium text-[#172538]">Descripción <span class="font-normal text-[#5b6470]">(opcional)</span></label>
                    <textarea id="comprobante-descripcion" wire:model.defer="descripcion" rows="4" maxlength="5000" placeholder="Por ejemplo: detalle del pago o período al que corresponde." class="mt-2 block w-full rounded-control border-[#c7ced7] bg-white px-3 py-2.5 text-[#172538] shadow-sm placeholder:text-[#697483] focus:border-[#8c6936] focus:ring-[#8c6936]"></textarea>
                    @error('descripcion')
                        <span role="alert" class="mt-1 block text-sm text-red-700">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mt-5 flex justify-stretch border-t border-[#dde2e8] pt-4 sm:justify-end">
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveComprobante,archivo" class="inline-flex min-h-11 w-full items-center justify-center rounded-control bg-[#172538] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#243850] focus:outline-none focus:ring-2 focus:ring-[#8c6936] focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                        <span wire:loading.remove wire:target="saveComprobante">Guardar comprobante</span>
                        <span wire:loading wire:target="saveComprobante">Guardando...</span>
                    </button>
                </div>
            </section>

            <aside class="rounded-card border border-[#dde2e8] bg-white p-4 lg:col-span-2 sm:p-6">
                <h4 class="font-semibold text-[#172538]">Archivo protegido</h4>
                <p class="mt-2 text-sm leading-6 text-[#5b6470]">El comprobante se guarda de forma privada y sólo podrá accederse a él mediante permisos autorizados.</p>
                <p class="mt-4 rounded-control bg-[#f5f6f8] p-3 text-sm text-[#172538]">Verificá que el archivo sea un PDF legible y que no supere los 20 MB.</p>
            </aside>
        </form>
    @else
        <div class="mt-5 rounded-control border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Tenés permiso para consultar comprobantes, pero no para registrar uno nuevo.
        </div>
    @endcan

    <section class="mt-8 border-t border-[#dde2e8] pt-6">
        <h4 class="text-lg font-semibold text-[#172538]">Comprobantes registrados</h4>

        <div class="mt-4 hidden overflow-x-auto rounded-card border border-[#dde2e8] lg:block">
            <table class="w-full min-w-[700px] text-left text-sm text-[#5b6470]">
                <thead class="bg-[#f5f6f8] text-xs uppercase tracking-wide text-[#172538]">
                    <tr>
                        <th scope="col" class="px-5 py-3">Cliente</th>
                        <th scope="col" class="px-5 py-3">DNI</th>
                        <th scope="col" class="px-5 py-3">Fecha</th>
                        <th scope="col" class="px-5 py-3">Descripción</th>
                        <th scope="col" class="px-5 py-3 text-right">Archivo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#dde2e8] bg-white">
                    @forelse($comprobantes as $comprobante)
                        <tr wire:key="comprobante-table-{{ $comprobante->id }}" class="hover:bg-[#f5f6f8]">
                            <td class="px-5 py-4 font-medium text-[#172538]">{{ $comprobante->cliente?->apellido }}, {{ $comprobante->cliente?->nombre }}</td>
                            <td class="px-5 py-4">{{ $comprobante->cliente?->dni }}</td>
                            <td class="whitespace-nowrap px-5 py-4">{{ $comprobante->fecha_subida?->format('d/m/Y') }}</td>
                            <td class="max-w-xs break-words px-5 py-4">{{ $comprobante->descripcion ?: 'Sin descripción' }}</td>
                            <td class="px-5 py-4 text-right">
                                @can('view', $comprobante)
                                    <a href="{{ route('comprobantes.show', $comprobante) }}" target="_blank" rel="noopener" class="inline-flex min-h-10 items-center rounded-control bg-[#edf1f5] px-3 py-2 text-sm font-semibold text-[#172538] hover:bg-[#dde2e8] focus:outline-none focus:ring-2 focus:ring-[#8c6936]">Ver comprobante</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-[#5b6470]">Todavía no hay comprobantes registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 space-y-3 lg:hidden">
            @forelse($comprobantes as $comprobante)
                <article wire:key="comprobante-card-{{ $comprobante->id }}" class="min-w-0 rounded-card border border-[#dde2e8] bg-white p-4 shadow-sm">
                    <h5 class="break-words font-semibold text-[#172538]">{{ $comprobante->cliente?->apellido }}, {{ $comprobante->cliente?->nombre }}</h5>
                    <dl class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                        <div><dt class="font-medium text-[#5b6470]">DNI</dt><dd class="text-[#172538]">{{ $comprobante->cliente?->dni }}</dd></div>
                        <div><dt class="font-medium text-[#5b6470]">Fecha</dt><dd class="text-[#172538]">{{ $comprobante->fecha_subida?->format('d/m/Y') }}</dd></div>
                        <div class="min-w-0 sm:col-span-2"><dt class="font-medium text-[#5b6470]">Descripción</dt><dd class="break-words text-[#172538]">{{ $comprobante->descripcion ?: 'Sin descripción' }}</dd></div>
                    </dl>
                    @can('view', $comprobante)
                        <div class="mt-3 border-t border-[#dde2e8] pt-3">
                            <a href="{{ route('comprobantes.show', $comprobante) }}" target="_blank" rel="noopener" class="inline-flex min-h-11 items-center rounded-control bg-[#edf1f5] px-3 py-2 text-sm font-semibold text-[#172538] hover:bg-[#dde2e8] focus:outline-none focus:ring-2 focus:ring-[#8c6936]">Ver comprobante</a>
                        </div>
                    @endcan
                </article>
            @empty
                <p role="status" class="rounded-card border border-[#dde2e8] bg-[#f5f6f8] px-5 py-8 text-center text-sm text-[#5b6470]">Todavía no hay comprobantes registrados.</p>
            @endforelse
        </div>

        <div class="mt-5">{{ $comprobantes->links('livewire.comprobantes.pagination') }}</div>
    </section>
</div>
