<?php

namespace App\Models;

use Database\Factories\ClienteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    /** @use HasFactory<ClienteFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'apellido',
        'dni',
        'telefono',
        'correo',
        'domicilio',
        'fecha_nacimiento',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
        ];
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole('Profesional')) {
            return $user->can('consultar_legajos')
                ? $query->whereHas('procesos', fn (Builder $processQuery): Builder => $processQuery->where('profesional_id', $user->id))
                : $query->whereKey(0);
        }

        return $user->can('listar_filtrar_clientes')
            ? $query
            : $query->whereKey(0);
    }

    public function procesos(): HasMany
    {
        return $this->hasMany(Proceso::class);
    }

    public function comprobantesPago(): HasMany
    {
        return $this->hasMany(ComprobantePago::class);
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class);
    }

    public function turnos(): HasMany
    {
        return $this->hasMany(Turno::class);
    }
}
