<?php

namespace App\Models;

use Database\Factories\CategoriaDocumentoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaDocumento extends Model
{
    /** @use HasFactory<CategoriaDocumentoFactory> */
    use HasFactory;

    protected $table = 'categorias_documento';

    protected $fillable = ['nombre', 'descripcion', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    // RN1: normalizar nombre (trim + colapsar espacios)
    public function setNombreAttribute($value): void
    {
        $this->attributes['nombre'] = preg_replace('/\s+/', ' ', trim((string) $value));
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'categoria_id');
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeOrdenadas(Builder $query): Builder
    {
        return $query->orderBy('nombre');
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
}
