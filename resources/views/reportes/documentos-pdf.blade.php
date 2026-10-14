<!DOCTYPE html>
{{-- resources/views/reportes/documentos-pdf.blade.php : plantilla solo para DomPDF --}}
{{-- Sin @extends ni assets externos: todo inline, porque DomPDF no ejecuta JS ni CSS externo. --}}
<html lang="es">
<head>
<meta charset="utf-8">
<title>Documentos por Categoría — {{ $codigo }}</title>
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; margin: 90px 30px 60px 30px; }
  header { position: fixed; top: -70px; left: 0; right: 0; border-bottom: 2px solid #333; padding-bottom: 6px; }
  footer { position: fixed; bottom: -40px; left: 0; right: 0; border-top: 1px solid #999; font-size: 9px; color: #555; }
  footer .der { float: right; }
  h1 { font-size: 18px; margin: 0 0 2px 0; }
  .meta { margin: 6px 0; }
  table { width: 100%; border-collapse: collapse; margin-top: 8px; }
  th, td { border: 1px solid #999; padding: 5px 6px; text-align: left; }
  th { background: #eee; }
  .resumen { margin-top: 10px; border: 1px solid #999; padding: 6px; background: #fafafa; }
  .pagenum:before { content: counter(page); }
</style>
</head>
<body>

{{-- 1 IDENTIDAD (se repite en cada página por ser header fijo) --}}
<header>
  <strong>AEQUITAS</strong> · Estudio jurídico — Sistema de gestión<br>
  <span style="font-size:9px">Av. del Parque 1250, Posadas, Misiones · Tel. (0376) 400-0000 · informes@aequitas.example</span>
</header>

{{-- 5 NAVEGACIÓN: pie con código y página actual/total --}}
<footer>
  AEQUITAS · Uso interno · Datos según filtros · {{ $codigo }} · Página <span class="pagenum"></span>
  <span class="der">Emitido por {{ $emitidoPor }} · {{ $fechaHora }}</span>
</footer>

{{-- 2 ALCANCE + 3 TRAZABILIDAD --}}
<h1>DOCUMENTOS POR CATEGORÍA</h1>
<div>{{ $codigo }} · Módulo Gestión Documental · Reporte de resumen + detalle</div>
<div class="meta">
  <strong>FECHA DESDE:</strong> {{ date('d/m/Y', strtotime($f['fecha_desde'])) }}
  &nbsp;&nbsp;<strong>FECHA HASTA:</strong> {{ date('d/m/Y', strtotime($f['fecha_hasta'])) }}
  &nbsp;&nbsp;<strong>EMITIDO POR:</strong> {{ $emitidoPor }}
  &nbsp;&nbsp;<strong>FECHA Y HORA:</strong> {{ $fechaHora }}<br>
  <strong>FILTROS APLICADOS:</strong>
  Estado: {{ ucfirst($f['estado']) }} ·
  Categoría: {{ empty($f['categoria_id']) ? 'Todas' : ('ID ' . $f['categoria_id']) }}
</div>
<div><strong>{{ $totales['total_docs'] }} documentos</strong> en {{ $totales['total_categorias'] }} categorías · Orden: nombre</div>

{{-- 4 RESULTADOS --}}
<table>
  <thead><tr><th>Nº</th><th>Categoría</th><th>Estado</th><th>Docs en período</th><th>Última carga</th></tr></thead>
  <tbody>
  @forelse($categorias as $i => $c)
    <tr>
      <td>{{ str_pad($i+1, 2, '0', STR_PAD_LEFT) }}</td>
      <td>{{ $c->nombre }}</td>
      <td>{{ $c->activo ? 'Activa' : 'Inactiva' }}</td>
      <td>{{ $c->docs_periodo_count }}</td>
      <td>{{ $c->ultima_carga ? date('d/m/Y', strtotime($c->ultima_carga)) : '—' }}</td>
    </tr>
  @empty
    <tr><td colspan="5">Sin resultados para el período y filtros indicados.</td></tr>
  @endforelse
  </tbody>
</table>

<div class="resumen">
  <strong>RESUMEN FINAL DEL PERÍODO</strong><br>
  Por estado: {{ $totales['activas'] }} activas y {{ $totales['inactivas'] }} inactivas.
  Total filtrado: {{ $totales['total_docs'] }} documentos en {{ $totales['total_categorias'] }} categorías.
</div>
<p style="font-size:9px">Criterio: incluye documentos con created_at entre las fechas indicadas, sin excluir categorías inactivas (se distinguen por Estado). El conteo es del período, no el histórico.</p>

</body>
</html>
