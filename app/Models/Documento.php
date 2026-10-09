<?php

namespace App\Models;

use Database\Factories\DocumentoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Documento extends Model implements Auditable
{
    /** @use HasFactory<DocumentoFactory> */
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->requiresProfessionalAssignmentScope()) {
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
        'categoria_id',
        'archivo_path',
        'tipo_documento',
        'nombre',
    ];

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaDocumento::class, 'categoria_id')->withTrashed();
    }

    public function versiones(): HasMany
    {
        return $this->hasMany(DocumentoVersion::class)
            ->orderByDesc('fecha_reemplazo')
            ->orderByDesc('id');
    }
}
