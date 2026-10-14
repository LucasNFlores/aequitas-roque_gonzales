{{-- resources/views/documentos/_select_categoria.blade.php : usar en CU9/CU13 --}}
{{-- Solo ofrece categorías activas para nuevas cargas. --}}
@php($categorias = \App\Models\CategoriaDocumento::paraCargaNueva())
<label>Categoría *</label>
<select name="categoria_id" required>
  <option value="">Seleccione…</option>
  @foreach($categorias as $c)
    <option value="{{ $c->id }}" @selected(old('categoria_id', $seleccionada ?? null) == $c->id)>{{ $c->nombre }}</option>
  @endforeach
</select>
@error('categoria_id')<div class="error">{{ $message }}</div>@enderror

{{-- Si el documento ya tiene una categoría hoy inactiva, mostrarla readonly como historial --}}
@if(isset($documento) && $documento->categoria && !$documento->categoria->activo)
  <p>Categoría histórica: <span class="badge">{{ $documento->categoria->nombre }} (inactiva)</span></p>
@endif
