<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaDocumento extends Model
{
    use HasFactory;

    protected $table = 'categorias_documento';

    protected $fillable = ['nombre', 'descripcion', 'activo', 'created_by', 'updated_by'];

    protected $casts = ['activo' => 'boolean'];

    // RN1: normalizar nombre (trim + colapsar espacios)
    public function setNombreAttribute($value): void
    {
        $this->attributes['nombre'] = preg_replace('/\s+/', ' ', trim((string) $value));
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class, 'categoria_id');
    }

    public function scopeActivas(Builder $q): Builder
    {
        return $q->where('activo', true);
    }

    public function scopeOrdenadas(Builder $q): Builder
    {
        return $q->orderBy('nombre');
    }

    /** Para dropdown de cargas nuevas (CU9/CU13): solo activas. */
    public static function paraCargaNueva()
    {
        return static::activas()->ordenadas()->get();
    }

    public function enUso(): bool
    {
        return $this->documentos()->exists();
    }

    public function cantidadDocumentos(): int
    {
        return $this->documentos()->count();
    }
}
