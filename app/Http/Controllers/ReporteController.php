<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReporteRequest;
use App\Http\Requests\UpdateReporteRequest;
use App\Models\Proceso;
use App\Models\Reporte;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReporteController extends Controller
{
    public function store(StoreReporteRequest $request, Proceso $proceso): RedirectResponse
    {
        $validated = $request->validated();
        abort_unless((int) $validated['proceso_id'] === $proceso->id, 404);
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);

        $proceso->reportes()->create([
            'profesional_id' => $proceso->profesional_id,
            'registrado_por_id' => $actor->id,
            'contenido' => $validated['contenido'],
            'fecha' => $validated['fecha'],
        ]);

        return back()->with('success', 'Reporte registrado correctamente.');
    }

    public function update(UpdateReporteRequest $request, Proceso $proceso, Reporte $reporte): RedirectResponse
    {
        abort_unless($reporte->proceso_id === $proceso->id, 404);

        $validated = $request->validated();
        $contenido = $validated['contenido'] ?? $reporte->contenido;
        $fecha = $validated['fecha'] ?? $reporte->fecha?->toDateString();

        DB::transaction(function () use ($request, $proceso, $reporte, $contenido, $fecha): void {
            $reporte = $proceso->reportes()->lockForUpdate()->findOrFail($reporte->id);

            if ($reporte->contenido !== $contenido || $reporte->fecha?->toDateString() !== $fecha) {
                $actor = $request->user();
                abort_unless($actor instanceof User, 403);

                $reporte->versiones()->create([
                    'contenido_anterior' => $reporte->contenido,
                    'fecha_anterior' => $reporte->fecha,
                    'usuario_id' => $actor->id,
                    'usuario_nombre' => $actor->nombre_completo,
                    'fecha_cambio' => now(),
                ]);

                $reporte->update([
                    'contenido' => $contenido,
                    'fecha' => $fecha,
                ]);
            }
        });

        return back()->with('success', 'Reporte actualizado correctamente.');
    }

    public function history(Proceso $proceso, Reporte $reporte): View
    {
        abort_unless($reporte->proceso_id === $proceso->id, 404);
        $this->authorize('view', $reporte);

        $reporte->load('versiones');

        return view('reportes.historial', compact('proceso', 'reporte'));
    }

    public function destroy(Proceso $proceso, Reporte $reporte): RedirectResponse
    {
        abort_unless($reporte->proceso_id === $proceso->id, 404);
        $this->authorize('delete', $reporte);
        $reporte->delete();

        return back()->with('success', 'Reporte dado de baja correctamente.');
    }
}
