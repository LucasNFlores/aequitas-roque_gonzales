<div x-data="{ selectedFile: '' }" x-on:comprobante-guardado.window="selectedFile = ''">
    <div class="flex flex-col gap-2 border-b border-gray-200 pb-6">
        <h3 class="text-lg font-semibold text-gray-900">Registrar comprobante de pago</h3>
        <p class="text-sm text-gray-500">Busca primero al cliente por DNI y adjunta el PDF descargado desde ARCA.</p>
    </div>

    @if($successMessage !== '')
        <div class="mt-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="alert">
            {{ $successMessage }}
        </div>
    @endif

    @can('create', \App\Models\ComprobantePago::class)
        <form wire:submit="saveComprobante" class="mt-6 grid gap-6 lg:grid-cols-5">
            <section class="rounded-xl border border-gray-200 bg-gray-50 p-5 lg:col-span-3 sm:p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h4 class="font-semibold text-gray-900">Datos del comprobante</h4>
                        <p class="mt-1 text-sm text-gray-500">Los campos marcados son obligatorios.</p>
                    </div>
                    <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">PDF hasta 20 MB</span>
                </div>

                <div class="mt-6">
                    <label for="comprobante-dni" class="block text-sm font-medium text-gray-700">DNI del cliente <span class="text-red-600">*</span></label>
                    <input id="comprobante-dni" type="search" inputmode="numeric" wire:model.live.debounce.300ms="dni" placeholder="Ingresá al menos 3 dígitos" autocomplete="off" class="mt-2 block w-full rounded-md border-gray-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />

                    @if($clienteId === null && $dni !== '' && mb_strlen(trim($dni)) < 3)
                        <p class="mt-2 text-sm text-gray-500">Ingresá al menos 3 dígitos para buscar.</p>
                    @endif

                    @if($clienteId === null && $clientes->isNotEmpty())
                        <div class="mt-2 overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
                            @foreach($clientes as $cliente)
                                <button type="button" wire:click="selectCliente({{ $cliente->id }})" class="flex w-full items-center justify-between gap-4 border-b border-gray-100 px-4 py-3 text-left last:border-b-0 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                                    <span class="font-medium text-gray-900">{{ $cliente->apellido }}, {{ $cliente->nombre }}</span>
                                    <span class="text-sm text-gray-500">DNI {{ $cliente->dni }}</span>
                                </button>
                            @endforeach
                        </div>
                    @elseif($clienteId === null && mb_strlen(trim($dni)) >= 3)
                        <p class="mt-2 text-sm text-gray-500">No se encontraron clientes con ese DNI.</p>
                    @endif

                    @error('clienteId')
                        <span class="mt-2 block text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                @if($selectedCliente)
                    <div class="mt-4 flex items-center justify-between gap-4 rounded-lg border border-indigo-200 bg-indigo-50 p-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Cliente seleccionado</p>
                            <p class="mt-1 font-medium text-gray-900">{{ $selectedCliente->apellido }}, {{ $selectedCliente->nombre }}</p>
                            <p class="text-sm text-gray-600">DNI {{ $selectedCliente->dni }}</p>
                        </div>
                        <button type="button" wire:click="clearCliente" class="rounded-md px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">Cambiar</button>
                    </div>
                @endif

                <div class="mt-6 grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="comprobante-fecha" class="block text-sm font-medium text-gray-700">Fecha de carga <span class="text-red-600">*</span></label>
                        <input id="comprobante-fecha" type="date" wire:model.defer="fechaSubida" max="{{ now()->toDateString() }}" class="mt-2 block w-full rounded-md border-gray-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        @error('fechaSubida')
                            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="comprobante-archivo" class="block text-sm font-medium text-gray-700">Archivo PDF <span class="text-red-600">*</span></label>
                        <input id="comprobante-archivo" type="file" accept="application/pdf,.pdf" wire:model="archivo" @change="selectedFile = $event.target.files[0]?.name ?? ''" class="mt-2 block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100 focus:border-indigo-500 focus:ring-indigo-500" />
                        <p x-show="selectedFile" x-text="selectedFile" class="mt-2 truncate text-sm text-gray-500"></p>
                        <p wire:loading wire:target="archivo" class="mt-2 text-sm text-indigo-600">Preparando archivo...</p>
                        @error('archivo')
                            <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <label for="comprobante-descripcion" class="block text-sm font-medium text-gray-700">Descripción <span class="font-normal text-gray-500">(opcional)</span></label>
                    <textarea id="comprobante-descripcion" wire:model.defer="descripcion" rows="4" maxlength="5000" placeholder="Por ejemplo: detalle del pago o período al que corresponde." class="mt-2 block w-full rounded-md border-gray-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    @error('descripcion')
                        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mt-6 flex justify-end border-t border-gray-200 pt-5">
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveComprobante,archivo" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveComprobante">Guardar comprobante</span>
                        <span wire:loading wire:target="saveComprobante">Guardando...</span>
                    </button>
                </div>
            </section>

            <aside class="rounded-xl border border-indigo-100 bg-indigo-50 p-5 lg:col-span-2 sm:p-6">
                <h4 class="font-semibold text-indigo-950">Archivo protegido</h4>
                <p class="mt-2 text-sm leading-6 text-indigo-900">El comprobante se guarda de forma privada y sólo podrá accederse a él mediante permisos autorizados.</p>
                <p class="mt-4 rounded-md bg-white/70 p-3 text-sm text-indigo-900">Verificá que el archivo sea un PDF legible y que no supere los 20 MB.</p>
            </aside>
        </form>
    @else
        <div class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Tenés permiso para consultar comprobantes, pero no para registrar uno nuevo.
        </div>
    @endcan

    <section class="mt-10 border-t border-gray-200 pt-8">
        <h4 class="text-lg font-semibold text-gray-900">Comprobantes registrados</h4>
        <div class="mt-4 overflow-x-auto rounded-lg border border-gray-200">
            <table class="w-full min-w-[700px] text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-700">
                    <tr>
                        <th scope="col" class="px-5 py-3">Cliente</th>
                        <th scope="col" class="px-5 py-3">DNI</th>
                        <th scope="col" class="px-5 py-3">Fecha</th>
                        <th scope="col" class="px-5 py-3">Descripción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($comprobantes as $comprobante)
                        <tr wire:key="comprobante-{{ $comprobante->id }}">
                            <td class="px-5 py-4 font-medium text-gray-900">{{ $comprobante->cliente?->apellido }}, {{ $comprobante->cliente?->nombre }}</td>
                            <td class="px-5 py-4">{{ $comprobante->cliente?->dni }}</td>
                            <td class="px-5 py-4">{{ $comprobante->fecha_subida?->format('d/m/Y') }}</td>
                            <td class="max-w-xs truncate px-5 py-4">{{ $comprobante->descripcion ?: 'Sin descripción' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-gray-500">Todavía no hay comprobantes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $comprobantes->links() }}</div>
    </section>
</div>
