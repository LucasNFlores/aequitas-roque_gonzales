<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ComprobantePago;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class LegajoController extends Controller
{
    public function show(Cliente $cliente): View
    {
        $this->authorize('viewLegajo', $cliente);

        $user = request()->user();
        abort_unless($user instanceof User, 403);

        $procesos = $cliente->procesos()
            ->visibleTo($user)
            ->with([
                'servicio:id,nombre',
                'coordinador:id,name',
                'profesional:id,name',
                'turnos' => fn ($query) => $query
                    ->select(['id', 'proceso_id', 'cliente_id', 'profesional_id', 'coordinador_id', 'fecha_hora', 'tipo', 'estado', 'es_externo', 'detalle_externo'])
                    ->orderByDesc('fecha_hora')
                    ->orderByDesc('id'),
                'turnos.profesional:id,name',
                'turnos.coordinador:id,name',
                'documentos' => fn ($query) => $query
                    ->select(['id', 'proceso_id', 'categoria_id', 'nombre', 'tipo_documento', 'created_at', 'updated_at'])
                    ->orderByDesc('created_at')
                    ->orderByDesc('id'),
                'documentos.proceso:id,profesional_id',
                'documentos.categoria:id,nombre',
                'documentos.versiones' => fn ($query) => $query
                    ->select(['id', 'documento_id', 'usuario_id', 'categoria_id', 'categoria_nombre', 'tipo_documento', 'nombre', 'fecha_reemplazo'])
                    ->orderByDesc('fecha_reemplazo')
                    ->orderByDesc('id'),
                'documentos.versiones.usuario:id,name',
                'documentos.versiones.categoria:id,nombre',
                'reportes' => fn ($query) => $query
                    ->visibleTo($user)
                    ->select(['id', 'proceso_id', 'profesional_id', 'contenido', 'fecha'])
                    ->orderByDesc('fecha')
                    ->orderByDesc('id'),
                'reportes.profesional:id,name',
                'historialEstados' => fn ($query) => $query
                    ->select(['id', 'proceso_id', 'usuario_id', 'estado_anterior', 'estado_nuevo', 'motivo', 'fecha_cambio'])
                    ->orderByDesc('fecha_cambio')
                    ->orderByDesc('id'),
                'historialEstados.usuario:id,name',
            ])
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id')
            ->get([
                'id',
                'cliente_id',
                'servicio_id',
                'coordinador_id',
                'profesional_id',
                'nombre',
                'tipo',
                'estado',
                'fecha_inicio',
                'descripcion',
                'honorarios',
                'motivo_rechazo',
            ]);

        $turnosSinProceso = $cliente->turnos()
            ->whereNull('proceso_id')
            ->when($user->hasRole('Profesional'), fn (Builder $query): Builder => $query->where('profesional_id', $user->id))
            ->with(['profesional:id,name', 'coordinador:id,name'])
            ->orderByDesc('fecha_hora')
            ->orderByDesc('id')
            ->get(['id', 'cliente_id', 'profesional_id', 'coordinador_id', 'fecha_hora', 'tipo', 'estado', 'es_externo', 'detalle_externo']);

        $comprobantes = collect();

        if ($user->can('ver_comprobantes_pago')) {
            $procesoIds = $procesos->modelKeys();

            $comprobantes = ComprobantePago::query()
                ->where('cliente_id', $cliente->id)
                ->where(function (Builder $query) use ($procesoIds): void {
                    $query->whereNull('proceso_id')
                        ->orWhereIn('proceso_id', $procesoIds);
                })
                ->with('proceso:id,nombre')
                ->orderByDesc('fecha_subida')
                ->orderByDesc('id')
                ->get(['id', 'cliente_id', 'proceso_id', 'fecha_subida', 'descripcion']);
        }

        return view('legajos.show', compact('cliente', 'procesos', 'turnosSinProceso', 'comprobantes', 'user'));
    }
}
