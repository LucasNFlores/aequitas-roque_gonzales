<?php

namespace App\Models;

use Database\Factories\EstadoProcesoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstadoProceso extends Model
{
    /** @use HasFactory<EstadoProcesoFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'estados_proceso';

    protected $fillable = [
        'nombre',
        'slug',
        'activo',
        'posicion',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'posicion' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('posicion')->orderBy('id');
    }

    public function procesos(): HasMany
    {
        return $this->hasMany(Proceso::class, 'estado', 'slug');
    }
}
