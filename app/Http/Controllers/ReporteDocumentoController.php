<?php

namespace App\Http\Controllers;

use App\Models\CategoriaDocumento;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * REP-DOC-2026-001 — Reporte de Documentos por Categoría.
 *
 * Pregunta que responde: ¿Qué documentación se cargó por categoría
 * en el período, y qué categorías están en desuso?
 *
 * Reutiliza CU37: solo coordinador/administrador (misma policy viewAny).
 * Filtros: período (fecha_desde/hasta sobre documentos.created_at)
 *         + estado (todas/activas/inactivas)
 *         + categoria_id (todas o una puntual).
 */
class ReporteDocumentoController extends Controller
{
    use AuthorizesRequests;

    public const CODIGO = 'REP-DOC-2026-001';

    /** Normaliza y valida filtros. Defaults = septiembre 2026 (igual que ejemplos FARO). */
    private function filtros(Request $request): array
    {
        $v = $request->validate([
            'fecha_desde'  => 'nullable|date',
            'fecha_hasta'  => 'nullable|date|after_or_equal:fecha_desde',
            'estado'       => 'nullable|in:todas,activas,inactivas',
            'categoria_id' => 'nullable|in:todas,all,__all__|exists:categorias_documento,id',
        ]);

        // Aceptar "todas" como sin filtro puntual
        $cat = $v['categoria_id'] ?? null;
        if (in_array($cat, ['todas', 'all', '__all__'], true)) {
            $cat = null;
        }

        return [
            'fecha_desde'  => $v['fecha_desde'] ?? '2026-09-01',
            'fecha_hasta'  => $v['fecha_hasta'] ?? '2026-09-30',
            'estado'       => $v['estado'] ?? 'todas',
            'categoria_id' => $cat,
        ];
    }

    /** Query base: categorías con conteo de documentos dentro del período. */
    private function queryReporte(array $f): Builder
    {
        $desde = $f['fecha_desde'] . ' 00:00:00';
        $hasta = $f['fecha_hasta'] . ' 23:59:59';

        $q = CategoriaDocumento::query()
            ->withCount(['documentos as docs_periodo_count' => function ($dq) use ($desde, $hasta) {
                $dq->whereBetween('documentos.created_at', [$desde, $hasta]);
            }])
            // Última carga del período (para la columna de trazabilidad)
            ->withMax(['documentos as ultima_carga' => function ($dq) use ($desde, $hasta) {
                $dq->whereBetween('documentos.created_at', [$desde, $hasta]);
            }], 'created_at')
            ->orderBy('nombre');

        if ($f['estado'] === 'activas') {
            $q->where('activo', true);
        } elseif ($f['estado'] === 'inactivas') {
            $q->where('activo', false);
        }

        if (! empty($f['categoria_id'])) {
            $q->where('id', $f['categoria_id']);
        }

        return $q;
    }

    private function totales($categorias): array
    {
        // $categorias puede ser Paginator o Collection
        $items = $categorias instanceof \Illuminate\Contracts\Pagination\Paginator
            ? collect($categorias->items())
            : collect($categorias);

        // Totales del FILTRADO completo (no solo la página) cuando se llama desde pdf/csv.
        // En index() se recalculan sobre el total sin paginar para que el encabezado no mienta.
        return [
            'total_categorias' => $items->count(),
            'total_docs'       => (int) $items->sum('docs_periodo_count'),
            'activas'          => $items->where('activo', true)->count(),
            'inactivas'        => $items->where('activo', false)->count(),
        ];
    }

    private function totalesFiltrado(array $f): array
    {
        $todas = $this->queryReporte($f)->get();
        return $this->totales($todas);
    }

    /** GET /reportes/documentos — vista HTML con filtros, tabla, total y paginación. */
    public function index(Request $request)
    {
        $this->authorize('viewAny', CategoriaDocumento::class);

        $f = $this->filtros($request);

        $categorias = $this->queryReporte($f)->paginate(10)->withQueryString();
        $totales = $this->totalesFiltrado($f);

        // Para el select "categoría puntual"
        $todasCategorias = CategoriaDocumento::ordenadas()->get(['id', 'nombre']);

        return view('reportes.documentos-index', [
            'categorias'        => $categorias,
            'todasCategorias'   => $todasCategorias,
            'f'                 => $f,
            'totales'           => $totales,
            'codigo'            => self::CODIGO,
            'emitidoPor'        => $request->user()->name ?? $request->user()->email,
            'fechaHora'         => now()->format('d/m/Y H:i'),
        ]);
    }

    /** GET /reportes/documentos/pdf — descarga PDF con misma consulta (sin paginar). */
    public function pdf(Request $request)
    {
        $this->authorize('viewAny', CategoriaDocumento::class);

        $f = $this->filtros($request);
        $categorias = $this->queryReporte($f)->get();
        $totales = $this->totales($categorias);

        // enable_php: permite el <script type="text/php"> de la vista que escribe "Página X de Y".
        $pdf = Pdf::loadView('reportes.documentos-pdf', [
            'categorias'  => $categorias,
            'f'           => $f,
            'totales'     => $totales,
            'codigo'      => self::CODIGO,
            'emitidoPor'  => $request->user()->name ?? $request->user()->email,
            'fechaHora'   => now()->format('d/m/Y H:i'),
        ])->setOption('enable_php', true)->setPaper('a4', 'portrait');

        $nombre = 'aequitas-' . strtolower(self::CODIGO) . '-' . $f['fecha_desde'] . '_' . $f['fecha_hasta'] . '.pdf';

        return $pdf->download($nombre);
    }

    /** GET /reportes/documentos/csv — descarga CSV (mismo juego de datos). */
    public function csv(Request $request)
    {
        $this->authorize('viewAny', CategoriaDocumento::class);

        $f = $this->filtros($request);
        $categorias = $this->queryReporte($f)->get();

        $nombre = 'aequitas-' . strtolower(self::CODIGO) . '.csv';

        return response()->streamDownload(function () use ($categorias) {
            $out = fopen('php://output', 'w');
            // BOM para que Excel abra UTF-8 con tildes
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Categoria', 'Descripcion', 'Estado', 'Docs_periodo', 'Ultima_carga'], ';');
            foreach ($categorias as $c) {
                fputcsv($out, [
                    $c->nombre,
                    $c->descripcion ?? '',
                    $c->activo ? 'Activa' : 'Inactiva',
                    $c->docs_periodo_count,
                    $c->ultima_carga ? date('d/m/Y', strtotime($c->ultima_carga)) : '—',
                ], ';');
            }
            fclose($out);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
