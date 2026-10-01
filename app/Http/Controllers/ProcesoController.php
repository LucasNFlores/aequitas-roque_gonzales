<?php

namespace App\Http\Controllers;

use App\Actions\Procesos\CrearProcesoAdicional;
use App\Http\Requests\StoreProcesoRequest;
use App\Http\Requests\UpdateProcesoRequest;
use App\Models\Cliente;
use App\Models\Proceso;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProcesoController extends Controller
{
    /**
     * Formulario anidado desde la ficha de un cliente existente (HU-23).
     */
    public function createForCliente(Cliente $cliente): View
    {
        $this->authorize('view', $cliente);
        $this->authorize('create', Proceso::class);

        return view('procesos.create', array_merge(
            $this->catalogs(),
            ['clienteFijo' => $cliente, 'submissionToken' => (string) Str::uuid()]
        ));
    }

    /**
     * Alta anidada de proceso adicional; el cliente viene de la URL y no se duplica ni modifica.
     */
    public function storeForCliente(
        StoreProcesoRequest $request,
        Cliente $cliente,
        CrearProcesoAdicional $crearProcesoAdicional,
    ): RedirectResponse {
        $this->authorize('view', $cliente);
        $this->authorize('create', Proceso::class);

        $validated = $request->validated();
        $proceso = $crearProcesoAdicional->handle($cliente, $validated);

        return redirect()
            ->route('clientes.show', $cliente)
            ->with('success', "Proceso adicional #{$proceso->id} creado en estado pendiente. No se programó ningún turno.");
    }

    /**
     * Catálogos activos para el formulario (servicio y coordinador activos).
     *
     * @return array{servicios: Collection<int, Servicio>, coordinadores: Collection<int, User>, profesionales: Collection<int, User>}
     */
    private function catalogs(): array
    {
        return [
            'servicios' => Servicio::query()->orderBy('nombre')->get(),
            'coordinadores' => User::role('Coordinador')->orderBy('name')->get(),
            'profesionales' => User::role('Profesional')->orderBy('name')->get(),
        ];
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
