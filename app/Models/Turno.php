<?php

namespace App\Models;

use Database\Factories\TurnoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Turno extends Model
{
    public const TIPOS = ['consulta_inicial', 'seguimiento', 'externo'];

    /** @use HasFactory<TurnoFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cliente_id',
        'profesional_id',
        'proceso_id',
        'fecha_hora',
        'es_externo',
        'detalle_externo',
        'tipo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
            'es_externo' => 'boolean',
        ];
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole('Profesional')) {
            return $user->can('ver_agenda_profesional')
                ? $query->where('profesional_id', $user->id)
                : $query->whereKey(0);
        }

        return $user->can('ver_agenda_profesional')
            ? $query
            : $query->whereKey(0);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }
}
