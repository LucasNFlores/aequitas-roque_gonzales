{{-- resources/views/categorias/form.blade.php : crear + editar --}}
@extends('layouts.app')
@section('content')
<h1>{{ $categoria->exists ? 'Editar categoría' : 'Nueva categoría' }}</h1>

@if($categoria->exists && $categoria->documentos_count > 0)
  <div class="alert alert-warning">Esta categoría tiene {{ $categoria->documentos_count }} documentos asociados. La edición no afectará el historial.</div>
@endif

<form method="POST" action="{{ $categoria->exists ? route('categorias.update', $categoria) : route('categorias.store') }}">
  @csrf
  @if($categoria->exists) @method('PUT') @endif

  <label>Nombre *</label>
  <input name="nombre" value="{{ old('nombre', $categoria->nombre) }}" maxlength="100" required>
  @error('nombre')<div class="error">{{ $message }}</div>@enderror

  <label>Descripción</label>
  <textarea name="descripcion" maxlength="255">{{ old('descripcion', $categoria->descripcion) }}</textarea>
  @error('descripcion')<div class="error">{{ $message }}</div>@enderror

  <button type="submit">{{ $categoria->exists ? 'Guardar cambios' : 'Crear categoría' }}</button>
  <a href="{{ route('categorias.index') }}">Cancelar</a>
</form>
@endsection
