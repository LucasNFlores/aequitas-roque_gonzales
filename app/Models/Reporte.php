<?php

namespace App\Models;

use Database\Factories\ReporteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reporte extends Model
{
    /** @use HasFactory<ReporteFactory> */
    use HasFactory, SoftDeletes;

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole('Profesional')) {
            return $user->can('consultar_reportes')
                ? $query->where('profesional_id', $user->id)
                : $query->whereKey(0);
        }

        return $user->can('consultar_reportes')
            ? $query
            : $query->whereKey(0);
    }

    protected $fillable = [
        'proceso_id',
        'profesional_id',
        'contenido',
        'fecha',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profesional_id');
    }
}
