<?php

namespace App\Models;

use Database\Factories\DocumentoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documento extends Model
{
    /** @use HasFactory<DocumentoFactory> */
    use HasFactory, SoftDeletes;

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole('Profesional')) {
            return $user->can('visualizar_documentacion')
                ? $query->whereHas('proceso', fn (Builder $processQuery): Builder => $processQuery->where('profesional_id', $user->id))
                : $query->whereKey(0);
        }

        return $user->can('visualizar_documentacion')
            ? $query
            : $query->whereKey(0);
    }

    protected $fillable = [
        'proceso_id',
        'archivo_path',
        'tipo_documento',
        'nombre',
    ];

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }
}
