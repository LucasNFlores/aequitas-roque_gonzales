<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReporteRequest;
use App\Http\Requests\UpdateReporteRequest;
use App\Models\Proceso;
use App\Models\Reporte;
use Illuminate\Http\RedirectResponse;

class ReporteController extends Controller
{
    public function store(StoreReporteRequest $request, Proceso $proceso): RedirectResponse
    {
        $validated = $request->validated();
        abort_unless((int) $validated['proceso_id'] === $proceso->id, 404);

        $proceso->reportes()->create([
            'profesional_id' => $request->user()->id,
            'contenido' => $validated['contenido'],
            'fecha' => $validated['fecha'],
        ]);

        return back()->with('success', 'Reporte registrado correctamente.');
    }

    public function update(UpdateReporteRequest $request, Proceso $proceso, Reporte $reporte): RedirectResponse
    {
        abort_unless($reporte->proceso_id === $proceso->id, 404);

        $reporte->update($request->validated());

        return back()->with('success', 'Reporte actualizado correctamente.');
    }

    public function destroy(Proceso $proceso, Reporte $reporte): RedirectResponse
    {
        abort_unless($reporte->proceso_id === $proceso->id, 404);
        $this->authorize('delete', $reporte);
        $reporte->delete();

        return back()->with('success', 'Reporte dado de baja correctamente.');
    }
}
