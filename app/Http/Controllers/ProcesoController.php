<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProcesoRequest;
use App\Http\Requests\UpdateProcesoRequest;
use App\Models\Cliente;
use App\Models\EstadoProceso;
use App\Models\Proceso;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProcesoController extends Controller
{
    /**
     * Formulario global de proceso adicional (HU-23) con selector de cliente.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Proceso::class);

        $selectedClienteId = $request->integer('cliente_id') ?: null;
        $clienteFijo = $selectedClienteId !== null
            ? Cliente::query()->whereKey($selectedClienteId)->first()
            : null;

        return view('procesos.create', array_merge(
            $this->catalogs(),
            ['clienteFijo' => $clienteFijo, 'selectedClienteId' => $selectedClienteId]
        ));
    }

    /**
     * Formulario anidado desde la ficha de un cliente existente (HU-23).
     */
    public function createForCliente(Cliente $cliente): View
    {
        $this->authorize('view', $cliente);
        $this->authorize('create', Proceso::class);

        $cliente->loadMissing(['procesos.servicio']);

        return view('procesos.create', array_merge(
            $this->catalogs(),
            ['clienteFijo' => $cliente, 'selectedClienteId' => $cliente->id]
        ));
    }

    /**
     * Alta global de proceso adicional para un cliente existente (HU-23).
     */
    public function store(StoreProcesoRequest $request): RedirectResponse
    {
        $this->authorize('create', Proceso::class);

        $validated = $request->validated();
        $cliente = Cliente::query()->whereKey($validated['cliente_id'])->firstOrFail();

        $proceso = $this->persistProcesoAdicional($cliente, $validated);

        return redirect()
            ->route('clientes.show', $cliente)
            ->with('success', "Proceso adicional #{$proceso->id} creado en estado pendiente.");
    }

    /**
     * Alta anidada de proceso adicional; el cliente viene de la URL y no se duplica ni modifica.
     */
    public function storeForCliente(StoreProcesoRequest $request, Cliente $cliente): RedirectResponse
    {
        $this->authorize('view', $cliente);
        $this->authorize('create', Proceso::class);

        $validated = $request->validated();
        $validated['cliente_id'] = $cliente->id;

        $proceso = $this->persistProcesoAdicional($cliente, $validated);

        return redirect()
            ->route('clientes.show', $cliente)
            ->with('success', "Proceso adicional #{$proceso->id} creado en estado pendiente.");
    }

    /**
     * Catálogos activos para el formulario (servicio y coordinador activos).
     *
     * @return array{clientes: Collection<int, Cliente>, servicios: Collection<int, Servicio>, coordinadores: Collection<int, User>, profesionales: Collection<int, User>}
     */
    private function catalogs(): array
    {
        return [
            'clientes' => Cliente::query()->orderBy('apellido')->orderBy('nombre')->limit(200)->get(),
            'servicios' => Servicio::query()->orderBy('nombre')->get(),
            'coordinadores' => User::role('Coordinador')->orderBy('name')->get(),
            'profesionales' => User::role('Profesional')->orderBy('name')->get(),
        ];
    }

    /**
     * Persiste el proceso adicional en estado pendiente sin efectos sobre cliente o turnos.
     *
     * @param  array<string, mixed>  $validated
     */
    private function persistProcesoAdicional(Cliente $cliente, array $validated): Proceso
    {
        $pendienteActivo = EstadoProceso::query()
            ->where('slug', 'pendiente')
            ->where('activo', true)
            ->exists();

        if (! $pendienteActivo) {
            throw ValidationException::withMessages([
                'estado' => 'El estado inicial pendiente no se encuentra activo.',
            ]);
        }

        return Proceso::create([
            'cliente_id' => $cliente->id,
            'profesional_id' => $validated['profesional_id'] ?? null,
            'servicio_id' => $validated['servicio_id'],
            'coordinador_id' => $validated['coordinador_id'],
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'tipo' => $validated['tipo'],
            'estado' => 'pendiente',
            'motivo_rechazo' => null,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Proceso $proceso)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Proceso $proceso)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProcesoRequest $request, Proceso $proceso)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Proceso $proceso)
    {
        //
    }
}
