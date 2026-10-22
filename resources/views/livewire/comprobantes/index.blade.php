<div x-data="{ selectedFile: '' }" x-on:comprobante-guardado.window="selectedFile = ''" class="min-w-0">
    <div class="flex flex-col gap-2 border-b border-line pb-5">
        <h3 class="text-lg font-semibold text-ink">Registrar comprobante de pago</h3>
        <p class="text-sm text-muted">Busca primero al cliente por DNI y adjunta el PDF descargado desde ARCA.</p>
    </div>

    @if($successMessage !== '')
        <div class="mt-5 rounded-control border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
            {{ $successMessage }}
        </div>
    @endif

    @can('create', \App\Models\ComprobantePago::class)
        <form wire:submit="saveComprobante" class="mt-5 grid min-w-0 gap-5 lg:grid-cols-5">
            <section class="min-w-0 rounded-card border border-line bg-canvas p-4 lg:col-span-3 sm:p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h4 class="font-semibold text-ink">Datos del comprobante</h4>
                        <p class="mt-1 text-sm text-muted">Los campos marcados son obligatorios.</p>
                    </div>
                    <span class="inline-flex w-fit rounded-full bg-aequitas-50 px-3 py-1 text-xs font-semibold text-ink">PDF hasta 20 MB</span>
                </div>

                <div class="mt-5 min-w-0">
                    <label for="comprobante-dni" class="block text-sm font-medium text-ink">DNI del cliente <span class="text-red-700">*</span></label>
                    <input id="comprobante-dni" type="search" inputmode="numeric" wire:model.live.debounce.300ms="dni" placeholder="Ingresá al menos 3 dígitos" autocomplete="off" class="mt-2 block min-h-11 w-full rounded-control border-line bg-white px-3 py-2.5 text-ink shadow-sm placeholder:text-muted focus:border-gold-600 focus:ring-gold-600">

                    @if($clienteId === null && $dni !== '' && mb_strlen(trim($dni)) < 3)
                        <p class="mt-2 text-sm text-muted">Ingresá al menos 3 dígitos para buscar.</p>
                    @endif

                    @if($clienteId === null && $clientes->isNotEmpty())
                        <div class="mt-2 overflow-hidden rounded-control border border-line bg-white shadow-sm">
                            @foreach($clientes as $cliente)
                                <button type="button" wire:click="selectCliente({{ $cliente->id }})" class="flex min-h-12 w-full flex-col items-start justify-center gap-1 border-b border-line px-4 py-3 text-left last:border-b-0 hover:bg-canvas focus:outline-none focus:ring-2 focus:ring-inset focus:ring-gold-600 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                                    <span class="font-medium text-ink">{{ $cliente->apellido }}, {{ $cliente->nombre }}</span>
                                    <span class="text-sm text-muted">DNI {{ $cliente->dni }}</span>
                                </button>
                            @endforeach
                        </div>
                    @elseif($clienteId === null && mb_strlen(trim($dni)) >= 3)
                        <p class="mt-2 text-sm text-muted">No se encontraron clientes con ese DNI.</p>
                    @endif

                    @error('clienteId')
                        <span role="alert" class="mt-2 block text-sm text-red-700">{{ $message }}</span>
                    @enderror
                </div>

                @if($selectedCliente)
                    <div class="mt-4 flex min-w-0 flex-col gap-3 rounded-card border border-line bg-white p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Cliente seleccionado</p>
                            <p class="mt-1 break-words font-medium text-ink">{{ $selectedCliente->apellido }}, {{ $selectedCliente->nombre }}</p>
                            <p class="text-sm text-muted">DNI {{ $selectedCliente->dni }}</p>
                        </div>
                        <button type="button" wire:click="clearCliente" class="inline-flex min-h-11 w-fit items-center rounded-control px-3 py-2 text-sm font-semibold text-ink underline decoration-gold-600 underline-offset-4 hover:bg-aequitas-50 focus:outline-none focus:ring-2 focus:ring-gold-600">Cambiar</button>
                    </div>
                @endif

                <div class="mt-5 grid min-w-0 gap-5 sm:grid-cols-2">
                    <div class="min-w-0">
                        <label for="comprobante-fecha" class="block text-sm font-medium text-ink">Fecha de carga <span class="text-red-700">*</span></label>
                        <input id="comprobante-fecha" type="date" wire:model.defer="fechaSubida" max="{{ now()->toDateString() }}" class="mt-2 block min-h-11 w-full rounded-control border-line bg-white px-3 py-2.5 text-ink shadow-sm focus:border-gold-600 focus:ring-gold-600">
                        @error('fechaSubida')
                            <span role="alert" class="mt-1 block text-sm text-red-700">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="min-w-0">
                        <label for="comprobante-archivo" class="block text-sm font-medium text-ink">Archivo PDF <span class="text-red-700">*</span></label>
                        <input id="comprobante-archivo" type="file" accept="application/pdf,.pdf" wire:model="archivo" @change="selectedFile = $event.target.files[0]?.name ?? ''" class="mt-2 block min-h-11 w-full max-w-full cursor-pointer rounded-control border border-line bg-white px-2 py-2 text-sm text-ink shadow-sm file:mr-2 file:rounded-control file:border-0 file:bg-aequitas-50 file:px-2 file:py-1.5 file:text-xs file:font-semibold file:text-ink hover:file:bg-aequitas-100 focus:border-gold-600 focus:ring-gold-600 sm:px-3 sm:file:mr-3 sm:file:px-3 sm:file:text-sm">
                        <p x-show="selectedFile" x-text="selectedFile" class="mt-2 break-all text-sm text-muted"></p>
                        <p wire:loading wire:target="archivo" class="mt-2 text-sm text-muted">Preparando archivo...</p>
                        @error('archivo')
                            <span role="alert" class="mt-1 block text-sm text-red-700">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mt-5">
                    <label for="comprobante-descripcion" class="block text-sm font-medium text-ink">Descripción <span class="font-normal text-muted">(opcional)</span></label>
                    <textarea id="comprobante-descripcion" wire:model.defer="descripcion" rows="4" maxlength="5000" placeholder="Por ejemplo: detalle del pago o período al que corresponde." class="mt-2 block w-full rounded-control border-line bg-white px-3 py-2.5 text-ink shadow-sm placeholder:text-muted focus:border-gold-600 focus:ring-gold-600"></textarea>
                    @error('descripcion')
                        <span role="alert" class="mt-1 block text-sm text-red-700">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mt-5 flex justify-stretch border-t border-line pt-4 sm:justify-end">
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveComprobante,archivo" class="inline-flex min-h-11 w-full items-center justify-center rounded-control bg-aequitas-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-aequitas-800 focus:outline-none focus:ring-2 focus:ring-gold-600 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                        <span wire:loading.remove wire:target="saveComprobante">Guardar comprobante</span>
                        <span wire:loading wire:target="saveComprobante">Guardando...</span>
                    </button>
                </div>
            </section>

            <aside class="rounded-card border border-line bg-white p-4 lg:col-span-2 sm:p-6">
                <h4 class="font-semibold text-ink">Archivo protegido</h4>
                <p class="mt-2 text-sm leading-6 text-muted">El comprobante se guarda de forma privada y sólo podrá accederse a él mediante permisos autorizados.</p>
                <p class="mt-4 rounded-control bg-canvas p-3 text-sm text-ink">Verificá que el archivo sea un PDF legible y que no supere los 20 MB.</p>
            </aside>
        </form>
    @else
        <div class="mt-5 rounded-control border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Tenés permiso para consultar comprobantes, pero no para registrar uno nuevo.
        </div>
    @endcan

    <section class="mt-8 border-t border-line pt-6">
        <h4 class="text-lg font-semibold text-ink">Comprobantes registrados</h4>

        <div class="mt-4 hidden overflow-x-auto rounded-card border border-line lg:block">
            <table class="w-full min-w-[700px] text-left text-sm text-muted">
                <thead class="bg-canvas text-xs uppercase tracking-wide text-ink">
                    <tr>
                        <th scope="col" class="px-5 py-3">Cliente</th>
                        <th scope="col" class="px-5 py-3">DNI</th>
                        <th scope="col" class="px-5 py-3">Fecha</th>
                        <th scope="col" class="px-5 py-3">Descripción</th>
                        <th scope="col" class="px-5 py-3 text-right">Archivo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line bg-white">
                    @forelse($comprobantes as $comprobante)
                        <tr wire:key="comprobante-table-{{ $comprobante->id }}" class="hover:bg-canvas">
                            <td class="px-5 py-4 font-medium text-ink">{{ $comprobante->cliente?->apellido }}, {{ $comprobante->cliente?->nombre }}</td>
                            <td class="px-5 py-4">{{ $comprobante->cliente?->dni }}</td>
                            <td class="whitespace-nowrap px-5 py-4">{{ $comprobante->fecha_subida?->format('d/m/Y') }}</td>
                            <td class="max-w-xs break-words px-5 py-4">{{ $comprobante->descripcion ?: 'Sin descripción' }}</td>
                            <td class="px-5 py-4 text-right">
                                @can('view', $comprobante)
                                    <a href="{{ route('comprobantes.show', $comprobante) }}" target="_blank" rel="noopener" class="inline-flex min-h-10 items-center rounded-control bg-aequitas-50 px-3 py-2 text-sm font-semibold text-ink hover:bg-aequitas-100 focus:outline-none focus:ring-2 focus:ring-gold-600">Ver comprobante</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-muted">Todavía no hay comprobantes registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 space-y-3 lg:hidden">
            @forelse($comprobantes as $comprobante)
                <article wire:key="comprobante-card-{{ $comprobante->id }}" class="min-w-0 rounded-card border border-line bg-white p-4 shadow-sm">
                    <h5 class="break-words font-semibold text-ink">{{ $comprobante->cliente?->apellido }}, {{ $comprobante->cliente?->nombre }}</h5>
                    <dl class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                        <div><dt class="font-medium text-muted">DNI</dt><dd class="text-ink">{{ $comprobante->cliente?->dni }}</dd></div>
                        <div><dt class="font-medium text-muted">Fecha</dt><dd class="text-ink">{{ $comprobante->fecha_subida?->format('d/m/Y') }}</dd></div>
                        <div class="min-w-0 sm:col-span-2"><dt class="font-medium text-muted">Descripción</dt><dd class="break-words text-ink">{{ $comprobante->descripcion ?: 'Sin descripción' }}</dd></div>
                    </dl>
                    @can('view', $comprobante)
                        <div class="mt-3 border-t border-line pt-3">
                            <a href="{{ route('comprobantes.show', $comprobante) }}" target="_blank" rel="noopener" class="inline-flex min-h-11 items-center rounded-control bg-aequitas-50 px-3 py-2 text-sm font-semibold text-ink hover:bg-aequitas-100 focus:outline-none focus:ring-2 focus:ring-gold-600">Ver comprobante</a>
                        </div>
                    @endcan
                </article>
            @empty
                <p role="status" class="rounded-card border border-line bg-canvas px-5 py-8 text-center text-sm text-muted">Todavía no hay comprobantes registrados.</p>
            @endforelse
        </div>

        <div class="mt-5">{{ $comprobantes->links('livewire.comprobantes.pagination') }}</div>
    </section>
</div>
