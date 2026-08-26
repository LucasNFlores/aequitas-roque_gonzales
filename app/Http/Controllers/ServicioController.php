<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServicioRequest;
use App\Http\Requests\UpdateServicioRequest;
use App\Models\Servicio;

class ServicioController extends Controller
{
    public function index()
    {
        $servicios = Servicio::latest()->paginate(10);

        return view('servicios.index', compact('servicios'));
    }

    public function create()
    {
        return view('servicios.create');
    }

    public function store(StoreServicioRequest $request)
    {
        Servicio::create($request->validated());

        return redirect()->route('servicios.index')->with('success', 'Servicio creado correctamente.');
    }

    public function edit(Servicio $servicio)
    {
        return view('servicios.edit', compact('servicio'));
    }

    public function update(UpdateServicioRequest $request, Servicio $servicio)
    {
        $servicio->update($request->validated());

        return redirect()->route('servicios.index')->with('success', 'Servicio actualizado correctamente.');
    }

    public function destroy(Servicio $servicio)
    {
        $servicio->delete();

        return redirect()->route('servicios.index')->with('success', 'Servicio eliminado correctamente.');
    }
}
