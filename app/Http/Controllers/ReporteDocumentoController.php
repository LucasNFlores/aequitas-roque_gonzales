<?php

namespace App\Http\Controllers;

use App\Models\CategoriaDocumento;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ReporteDocumentoController extends Controller
{
    private const MAXIMO_DIAS_PERIODO = 366;

    /**
     * @return array{fecha_desde: string, fecha_hasta: string, estado: string, categoria_id: int|null}
     */
    private function filtros(Request $request): array
    {
        $validator = Validator::make($request->query(), [
            'fecha_desde' => ['nullable', 'required_with:fecha_hasta', 'date_format:Y-m-d', 'before_or_equal:today'],
            'fecha_hasta' => ['nullable', 'required_with:fecha_desde', 'date_format:Y-m-d', 'before_or_equal:today', 'after_or_equal:fecha_desde'],
            'estado' => ['nullable', Rule::in(['todas', 'activas', 'inactivas'])],
            'categoria_id' => ['nullable', 'integer', Rule::exists('categorias_documento', 'id')->whereNull('deleted_at')],
        ], [
            'fecha_desde.required_with' => 'Indique también la fecha hasta para definir el período.',
            'fecha_desde.date_format' => 'La fecha desde debe ser una fecha válida.',
            'fecha_desde.before_or_equal' => 'La fecha desde no puede ser futura.',
            'fecha_hasta.required_with' => 'Indique también la fecha desde para definir el período.',
            'fecha_hasta.date_format' => 'La fecha hasta debe ser una fecha válida.',
            'fecha_hasta.before_or_equal' => 'La fecha hasta no puede ser futura.',
            'fecha_hasta.after_or_equal' => 'La fecha hasta debe ser igual o posterior a la fecha desde.',
            'estado.in' => 'Seleccione un estado de categoría válido.',
            'categoria_id.exists' => 'La categoría seleccionada no existe o ya no está disponible.',
        ]);

        if ($validator->fails()) {
            throw (new ValidationException($validator))->redirectTo(route('reportes.documentos.index'));
        }

        $filtros = $validator->validated();

        if (isset($filtros['fecha_desde'], $filtros['fecha_hasta'])) {
            $desde = CarbonImmutable::createFromFormat('!Y-m-d', $filtros['fecha_desde']);
            $hasta = CarbonImmutable::createFromFormat('!Y-m-d', $filtros['fecha_hasta']);

            if ($desde->diffInDays($hasta) + 1 > self::MAXIMO_DIAS_PERIODO) {
                throw ValidationException::withMessages([
                    'fecha_hasta' => 'El período no puede superar los 366 días.',
                ])->redirectTo(route('reportes.documentos.index'));
            }
        } else {
            $hasta = CarbonImmutable::today();
            $desde = $hasta->subDays(29);
        }

        return [
            'fecha_desde' => $desde->toDateString(),
            'fecha_hasta' => $hasta->toDateString(),
            'estado' => $filtros['estado'] ?? 'todas',
            'categoria_id' => isset($filtros['categoria_id']) ? (int) $filtros['categoria_id'] : null,
        ];
    }

    /** @param array{fecha_desde: string, fecha_hasta: string, estado: string, categoria_id: int|null} $filtros */
    private function queryReporte(array $filtros): Builder
    {
        $desde = CarbonImmutable::createFromFormat('!Y-m-d', $filtros['fecha_desde']);
        $hastaExclusivo = CarbonImmutable::createFromFormat('!Y-m-d', $filtros['fecha_hasta'])->addDay();

        $query = CategoriaDocumento::query()
            ->withCount(['documentos as documentos_en_periodo_count' => function (Builder $documentos) use ($desde, $hastaExclusivo): void {
                $documentos->where('documentos.created_at', '>=', $desde)
                    ->where('documentos.created_at', '<', $hastaExclusivo);
            }])
            ->withMax(['documentos as ultima_carga' => function (Builder $documentos) use ($desde, $hastaExclusivo): void {
                $documentos->where('documentos.created_at', '>=', $desde)
                    ->where('documentos.created_at', '<', $hastaExclusivo);
            }], 'created_at')
            ->orderBy('nombre');

        if ($filtros['estado'] === 'activas') {
            $query->where('activo', true);
        } elseif ($filtros['estado'] === 'inactivas') {
            $query->where('activo', false);
        }

        if ($filtros['categoria_id'] !== null) {
            $query->whereKey($filtros['categoria_id']);
        }

        return $query;
    }

    /**
     * @param  Collection<int, CategoriaDocumento>  $categorias
     * @return array{total_categorias: int, total_documentos: int, activas: int, inactivas: int}
     */
    private function totales(Collection $categorias): array
    {
        return [
            'total_categorias' => $categorias->count(),
            'total_documentos' => (int) $categorias->sum('documentos_en_periodo_count'),
            'activas' => $categorias->where('activo', true)->count(),
            'inactivas' => $categorias->where('activo', false)->count(),
        ];
    }

    public function index(Request $request): View
    {
        $this->authorize('viewReport', CategoriaDocumento::class);

        $filtros = $this->filtros($request);
        $categorias = $this->queryReporte($filtros)->paginate(10)->withQueryString();
        $totales = $this->totales($this->queryReporte($filtros)->get());
        $todasCategorias = CategoriaDocumento::query()->ordenadas()->get(['id', 'nombre']);

        return view('reportes.documentos-index', [
            'categorias' => $categorias,
            'todasCategorias' => $todasCategorias,
            'filtros' => $filtros,
            'totales' => $totales,
        ]);
    }

    public function pdf(Request $request): Response
    {
        $this->authorize('viewReport', CategoriaDocumento::class);

        $filtros = $this->filtros($request);
        $categorias = $this->queryReporte($filtros)->get();
        $totales = $this->totales($categorias);

        return Pdf::loadView('reportes.documentos-pdf', [
            'categorias' => $categorias,
            'filtros' => $filtros,
            'totales' => $totales,
            'emitidoPor' => $request->user()->name,
            'fechaHora' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait')
            ->download('informe-documentos-'.$filtros['fecha_desde'].'_'.$filtros['fecha_hasta'].'.pdf');
    }
}
