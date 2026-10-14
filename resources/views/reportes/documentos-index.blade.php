{{-- resources/views/reportes/documentos-index.blade.php : vista HTML del reporte --}}
{{-- Pregunta: ¿Qué documentación se cargó por categoría en el período? --}}
@extends('layouts.app')
@section('content')
{{-- IDENTIDAD --}}
<div style="border-bottom:2px solid #333;padding-bottom:8px;margin-bottom:12px">
  <strong>AEQUITAS</strong> · Estudio jurídico — Sistema de gestión<br>
  <small>Av. del Parque 1250, Posadas, Misiones · Tel. (0376) 400-0000 · informes@aequitas.example</small>
</div>

<h1>Documentos por Categoría <small style="font-weight:normal">({{ $codigo }})</small></h1>
<p><small>Módulo Gestión Documental · Reporte de resumen + detalle</small></p>

{{-- CONTEXTO + TRAZABILIDAD --}}
<p>
  <strong>Período:</strong> {{ date('d/m/Y', strtotime($f['fecha_desde'])) }} al {{ date('d/m/Y', strtotime($f['fecha_hasta'])) }}
  &nbsp;·&nbsp; <strong>Emitido por:</strong> {{ $emitidoPor }}
  &nbsp;·&nbsp; <strong>Fecha y hora:</strong> {{ $fechaHora }}
</p>
<p><strong>{{ $totales['total_docs'] }} documentos</strong> en {{ $totales['total_categorias'] }} categorías
  ({{ $totales['activas'] }} activas, {{ $totales['inactivas'] }} inactivas)</p>

{{-- CONSULTA: período + 2 filtros --}}
<form method="GET" action="{{ route('reportes.documentos.index') }}" style="margin:12px 0">
  <label>Fecha desde <input type="date" name="fecha_desde" value="{{ $f['fecha_desde'] }}"></label>
  <label>Fecha hasta <input type="date" name="fecha_hasta" value="{{ $f['fecha_hasta'] }}"></label>
  <label>Filtro 1 · Estado
    <select name="estado">
      <option value="todas" @selected($f['estado']==='todas')>Todas</option>
      <option value="activas" @selected($f['estado']==='activas')>Activas</option>
      <option value="inactivas" @selected($f['estado']==='inactivas')>Inactivas</option>
    </select>
  </label>
  <label>Filtro 2 · Categoría
    <select name="categoria_id">
      <option value="todas">Todas</option>
      @foreach($todasCategorias as $tc)
        <option value="{{ $tc->id }}" @selected(($f['categoria_id'] ?? null)==$tc->id)>{{ $tc->nombre }}</option>
      @endforeach
    </select>
  </label>
  <button type="submit">Aplicar</button>
  <a href="{{ route('reportes.documentos.index') }}">Limpiar</a>
  @error('fecha_hasta')<div class="error">{{ $message }}</div>@enderror
</form>

{{-- RESULTADOS --}}
<table>
  <thead><tr><th>Categoría</th><th>Descripción</th><th>Estado</th><th>Docs en período</th><th>Última carga</th></tr></thead>
  <tbody>
  @forelse($categorias as $c)
    <tr>
      <td>{{ $c->nombre }}</td>
      <td>{{ $c->descripcion ?? '—' }}</td>
      <td>{{ $c->activo ? 'Activa' : 'Inactiva' }}</td>
      <td>{{ $c->docs_periodo_count }}</td>
      <td>{{ $c->ultima_carga ? date('d/m/Y', strtotime($c->ultima_carga)) : '—' }}</td>
    </tr>
  @empty
    <tr><td colspan="5">Sin resultados para el período y filtros indicados.</td></tr>
  @endforelse
  </tbody>
</table>

{{-- USO: paginación + exportación conservando filtros --}}
<div style="margin-top:10px">
  {{ $categorias->links() }}
  <p><small>Mostrando {{ $categorias->firstItem() ?? 0 }}–{{ $categorias->lastItem() ?? 0 }} de {{ $categorias->total() }} categorías.</small></p>
  <a href="{{ route('reportes.documentos.pdf', request()->query()) }}" class="btn">Exportar PDF</a>
  <a href="{{ route('reportes.documentos.csv', request()->query()) }}" class="btn">Descargar CSV</a>
</div>

<p><small>Criterio: solo documentos con created_at dentro del período. El conteo es por categoría (docs_periodo_count), no el total histórico.</small></p>
@endsection
