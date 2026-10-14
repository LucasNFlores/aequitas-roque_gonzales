{{-- resources/views/categorias/index.blade.php --}}
@extends('layouts.app')
@section('content')
<h1>Categorías de documentos</h1>

@if(session('ok'))<div class="alert alert-success">{{ session('ok') }}</div>@endif

<div>
  <a href="{{ route('categorias.create') }}">Nueva categoría</a>
  | Filtro:
  <a href="?estado=todas">Todas</a>
  <a href="?estado=activas">Activas</a>
  <a href="?estado=inactivas">Inactivas</a>
</div>

<table>
  <thead><tr><th>Nombre</th><th>Descripción</th><th>Estado</th><th>Docs en uso</th><th>Acciones</th></tr></thead>
  <tbody>
  @forelse($categorias as $c)
    <tr>
      <td>{{ $c->nombre }}</td>
      <td>{{ $c->descripcion ?? '—' }}</td>
      <td>{{ $c->activo ? 'Activa' : 'Inactiva' }}</td>
      <td>{{ $c->documentos_count }}</td>
      <td>
        <a href="{{ route('categorias.edit', $c) }}">Editar</a>
        @if($c->activo)
          <form method="POST" action="{{ route('categorias.deactivate', $c) }}" style="display:inline"
                onsubmit="return confirm('Tiene {{ $c->documentos_count }} documentos históricos que se conservarán. Dejará de ofrecerse para nuevas cargas. ¿Confirmar?')">
            @csrf @method('PATCH')
            <button type="submit">Desactivar</button>
          </form>
        @else
          <form method="POST" action="{{ route('categorias.activate', $c) }}" style="display:inline">
            @csrf @method('PATCH')
            <button type="submit">Reactivar</button>
          </form>
        @endif
      </td>
    </tr>
  @empty
    <tr><td colspan="5">Sin categorías. Cree la primera.</td></tr>
  @endforelse
  </tbody>
</table>
{{ $categorias->links() }}
@endsection
