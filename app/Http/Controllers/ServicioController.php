<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServicioRequest;
use App\Http\Requests\UpdateServicioRequest;
use App\Models\Servicio;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServicioController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Servicio::class);

        $servicios = Servicio::latest()->paginate(10);

        return view('servicios-viejo.index', compact('servicios'));
    }

    public function create(): View
    {
        $this->authorize('create', Servicio::class);

        return view('servicios-viejo.create');
    }

    public function store(StoreServicioRequest $request): RedirectResponse
    {
        $this->authorize('create', Servicio::class);

        Servicio::create($request->validated());

        return redirect()->route('servicios-viejo.index')->with('success', 'Servicio creado correctamente.');
    }

    public function edit(Servicio $servicio): View
    {
        $this->authorize('update', $servicio);

        return view('servicios-viejo.edit', compact('servicio'));
    }

    public function update(UpdateServicioRequest $request, Servicio $servicio): RedirectResponse
    {
        $this->authorize('update', $servicio);

        $servicio->update($request->validated());

        return redirect()->route('servicios-viejo.index')->with('success', 'Servicio actualizado correctamente.');
    }

    public function destroy(Servicio $servicio): RedirectResponse
    {
        $this->authorize('delete', $servicio);

        $servicio->delete();

        return redirect()->route('servicios-viejo.index')->with('success', 'Servicio eliminado correctamente.');
    }
}
