<!DOCTYPE html>
{{-- resources/views/reportes/documentos-pdf.blade.php : plantilla solo para DomPDF --}}
{{-- Espejo del ejemplo FARO: encabezado con logo + pie con Página X de Y en todas las páginas. --}}
{{-- Sin @extends ni assets externos: todo inline, porque DomPDF no ejecuta JS ni CSS externo. --}}
<html lang="es">
<head>
<meta charset="utf-8">
<title>Documentos por Categoría — {{ $codigo }}</title>
<style>
  @page { margin: 105px 30px 60px 30px; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; margin: 0; }
  /* ENCABEZADO: fijo, se repite en cada página. Tabla (sin floats) para que DomPDF no lo recorte. */
  header { position: fixed; top: -90px; left: 0; right: 0; border-bottom: 2px solid #1f2a44; padding-bottom: 5px; }
  table.cab { width: 100%; border-collapse: collapse; }
  table.cab td { vertical-align: middle; padding: 0; }
  td.logo-cell { width: 52px; }
  .logo { width: 40px; height: 40px; background: #1f2a44; color: #fff;
          font-size: 24px; font-weight: bold; text-align: center; line-height: 40px; }
  .empresa { font-size: 15px; font-weight: bold; }
  .empresa small { font-size: 10px; font-weight: normal; }
  .datos-empresa { font-size: 9px; color: #444; }
  /* PIE: fijo, se repite en cada página. El "Página X de Y" lo escribe el script de abajo. */
  footer { position: fixed; bottom: -40px; left: 0; right: 0; border-top: 1px solid #999;
           font-size: 9px; color: #555; padding-top: 4px; }
  h1 { font-size: 17px; margin: 0 0 2px 0; }
  .codigo { font-size: 11px; color: #444; }
  /* Bloques de trazabilidad estilo FARO */
  .bloques { width: 100%; border-collapse: collapse; margin: 8px 0; }
  .bloques td { border: 1px solid #999; padding: 4px 8px; width: 25%; vertical-align: top; }
  .bloques .et { font-size: 8px; color: #666; font-weight: bold; }
  .bloques .val { font-size: 11px; }
  .filtros { background: #eef1f6; border: 1px solid #999; padding: 5px 8px; margin: 6px 0; }
  table.detalle { width: 100%; border-collapse: collapse; margin-top: 8px; }
  table.detalle th, table.detalle td { border: 1px solid #999; padding: 5px 6px; text-align: left; }
  table.detalle th { background: #eef1f6; }
  .alcance { margin-top: 8px; border: 1px solid #999; padding: 6px 8px; font-size: 10px; }
  .resumen { margin-top: 8px; border: 1px solid #999; padding: 6px 8px; background: #fafafa; }
  .et-seccion { font-size: 10px; font-weight: bold; margin-bottom: 3px; }
</style>
</head>
<body>

{{-- 1 IDENTIDAD --}}
<header>
  <table class="cab"><tr>
    <td class="logo-cell"><div class="logo">A</div></td>
    <td>
      <div class="empresa">AEQUITAS GESTIÓN<br><small>Estudio jurídico — Sistemas de gestión</small></div>
      <div class="datos-empresa">Av. del Parque 1250, Posadas, Misiones · Tel. (0376) 400-0000 · informes@aequitas.example</div>
    </td>
  </tr></table>
</header>

{{-- 5 NAVEGACIÓN --}}
<footer>AEQUITAS Gestión · Uso interno · Datos ficticios para clase {{ $codigo }}</footer>
<script type="text/php">
if (isset($pdf)) {
  $font = $fontMetrics->get_font("DejaVu Sans", "normal");
  $pdf->page_text(455, 812, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, 9, array(0.33, 0.33, 0.33));
}
</script>

{{-- TÍTULO + TRAZABILIDAD --}}
<h1>DOCUMENTOS POR CATEGORÍA</h1>
<div class="codigo">{{ $codigo }} · Módulo Gestión Documental · Reporte de resumen + detalle</div>

<table class="bloques">
  <tr>
    <td><div class="et">FECHA DESDE</div><div class="val">{{ date('d/m/Y', strtotime($f['fecha_desde'])) }}</div></td>
    <td><div class="et">FECHA HASTA</div><div class="val">{{ date('d/m/Y', strtotime($f['fecha_hasta'])) }}</div></td>
    <td><div class="et">EMITIDO POR</div><div class="val">{{ $emitidoPor }}</div></td>
    <td><div class="et">FECHA Y HORA</div><div class="val">{{ $fechaHora }}</div></td>
  </tr>
</table>

{{-- 2 ALCANCE --}}
<div class="filtros"><strong>FILTROS APLICADOS</strong><br>
  Estado: {{ ucfirst($f['estado']) }} · Categoría: {{ empty($f['categoria_id']) ? 'Todas' : ('ID ' . $f['categoria_id']) }}
</div>
<div><strong>{{ $totales['total_docs'] }} documentos encontrados.</strong> Orden: nombre de categoría.</div>

{{-- 4 RESULTADOS --}}
<table class="detalle">
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

<div class="alcance">
  <div class="et-seccion">ALCANCE DE LOS DATOS</div>
  Incluye documentos con fecha de carga dentro del período indicado, sin excluir categorías inactivas (se distinguen por Estado).
  El conteo es del período, no el histórico total.
</div>

<div class="resumen">
  <div class="et-seccion">RESUMEN FINAL DEL PERÍODO</div>
  Por estado: {{ $totales['activas'] }} activas y {{ $totales['inactivas'] }} inactivas.
  Total filtrado: {{ $totales['total_docs'] }} documentos en {{ $totales['total_categorias'] }} categorías.
</div>

</body>
</html>
