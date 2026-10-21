<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Informe de documentos por categoría</title>
    <style>
        @page { margin: 32px; }
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { margin: 0 0 5px; font-size: 20px; }
        .subtitulo { color: #6b7280; margin-bottom: 20px; }
        .datos, .resultados { border-collapse: collapse; width: 100%; }
        .datos { margin-bottom: 18px; }
        .datos td { border: 1px solid #d1d5db; padding: 8px; width: 50%; }
        .etiqueta { color: #6b7280; display: block; font-size: 9px; margin-bottom: 4px; text-transform: uppercase; }
        .resultados th, .resultados td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
        .resultados th { background: #f3f4f6; }
        .resumen { background: #f9fafb; border: 1px solid #d1d5db; margin-bottom: 18px; padding: 10px; }
        .vacio { color: #6b7280; text-align: center !important; }
        .pie { border-top: 1px solid #d1d5db; color: #6b7280; font-size: 9px; margin-top: 20px; padding-top: 8px; }
    </style>
</head>
<body>
    <h1>Informe de documentos por categoría</h1>
    <div class="subtitulo">Resumen de documentos cargados en el período indicado.</div>

    <table class="datos">
        <tr>
            <td><span class="etiqueta">Período</span>{{ \Illuminate\Support\Carbon::parse($filtros['fecha_desde'])->format('d/m/Y') }} al {{ \Illuminate\Support\Carbon::parse($filtros['fecha_hasta'])->format('d/m/Y') }}</td>
            <td><span class="etiqueta">Estado de categoría</span>{{ ucfirst($filtros['estado']) }}</td>
        </tr>
        <tr>
            <td><span class="etiqueta">Categoría</span>{{ $categorias->firstWhere('id', $filtros['categoria_id'])?->nombre ?? 'Todas' }}</td>
            <td><span class="etiqueta">Emitido por / fecha</span>{{ $emitidoPor }} · {{ $fechaHora }}</td>
        </tr>
    </table>

    <div class="resumen"><strong>{{ $totales['total_documentos'] }}</strong> documentos en <strong>{{ $totales['total_categorias'] }}</strong> categorías ({{ $totales['activas'] }} activas y {{ $totales['inactivas'] }} inactivas).</div>

    <table class="resultados">
        <thead>
            <tr>
                <th>Categoría</th>
                <th>Estado</th>
                <th>Documentos en el período</th>
                <th>Última carga</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categorias as $categoria)
                <tr>
                    <td>{{ $categoria->nombre }}</td>
                    <td>{{ $categoria->activo ? 'Activa' : 'Inactiva' }}</td>
                    <td>{{ $categoria->documentos_en_periodo_count }}</td>
                    <td>{{ $categoria->ultima_carga ? \Illuminate\Support\Carbon::parse($categoria->ultima_carga)->format('d/m/Y') : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="vacio">No hay categorías para los filtros seleccionados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pie">El conteo incluye únicamente documentos creados dentro del período. No incluye ni adjunta archivos originales.</div>
</body>
</html>
