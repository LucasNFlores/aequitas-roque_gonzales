<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Database\Factories\TurnoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Turno extends Model
{
    public const TIPOS = ['consulta_inicial', 'seguimiento', 'externo'];

    public const ESTADOS = ['programado', 'cancelado'];

    /** Estados de proceso que impiden agendar seguimiento. */
    public const PROCESO_ESTADOS_INACTIVOS = ['finalizado', 'rechazado'];

    /** @use HasFactory<TurnoFactory> */
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'profesional_id',
        'coordinador_id',
        'proceso_id',
        'fecha_hora',
        'es_externo',
        'detalle_externo',
        'tipo',
        'estado',
    ];

    protected $attributes = [
        'estado' => 'programado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
            'es_externo' => 'boolean',
        ];
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', 'programado');
    }

    public function scopeCancelados(Builder $query): Builder
    {
        return $query->where('estado', 'cancelado');
    }

    public function isCancelado(): bool
    {
        return $this->estado === 'cancelado';
    }

    public function isProgramado(): bool
    {
        return $this->estado === 'programado';
    }

    public function cancelar(): bool
    {
        if ($this->isCancelado()) {
            return true;
        }

        return $this->update(['estado' => 'cancelado']);
    }

    public static function existeConflicto(
        int $profesionalId,
        CarbonInterface|\DateTimeInterface|string $fechaHora,
        ?int $excluirId = null,
        bool $esExternoNuevo = false,
    ): bool {
        $fecha = $fechaHora instanceof CarbonInterface
            ? $fechaHora->copy()
            : Carbon::parse($fechaHora);

        $turnosDelDia = static::query()
            ->activos()
            ->where('profesional_id', $profesionalId)
            ->whereDate('fecha_hora', $fecha->toDateString())
            ->when($excluirId !== null, fn (Builder $query): Builder => $query->whereKeyNot($excluirId));

        if ($esExternoNuevo) {
            return $turnosDelDia->exists();
        }

        $fechaExacta = $fecha->copy()->second(0);

        return $turnosDelDia
            ->where(function (Builder $query) use ($fechaExacta): void {
                $query->where('es_externo', true)
                    ->orWhere('fecha_hora', $fechaExacta);
            })
            ->exists();
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

    public function coordinador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinador_id');
    }

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }
}
