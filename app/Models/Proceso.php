<?php

namespace App\Models;

use Database\Factories\ProcesoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proceso extends Model
{
    public const TIPOS = ['Civil', 'Comercial', 'Familia'];

    public const ESTADOS = [
        'pendiente',
        'admitido',
        'iniciado',
        'en_proceso',
        'finalizado',
        'en_espera',
        'rechazado',
    ];

    /** @use HasFactory<ProcesoFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cliente_id',
        'profesional_id',
        'servicio_id',
        'coordinador_id',
        'nombre',
        'descripcion',
        'fecha_inicio',
        'tipo',
        'estado',
        'motivo_rechazo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
        ];
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole('Profesional')) {
            return $user->can('listar_filtrar_procesos')
                ? $query->where('profesional_id', $user->id)
                : $query->whereKey(0);
        }

        return $user->can('listar_filtrar_procesos') || $user->can('consultar_legajos')
            ? $query
            : $query->whereKey(0);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profesional_id');
    }

    public function coordinador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinador_id');
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function turnos(): HasMany
    {
        return $this->hasMany(Turno::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    public function reportes(): HasMany
    {
        return $this->hasMany(Reporte::class);
    }
}
