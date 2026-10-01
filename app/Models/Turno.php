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

    public const ESTADOS = ['programado', 'cancelado'];

    /** Duración por defecto para detección de solapamiento por rango (HU-11). */
    public const DURACION_MINUTOS = 60;

    /** Estados de proceso que impiden agendar seguimiento. */
    public const PROCESO_ESTADOS_INACTIVOS = ['finalizado', 'rechazado'];

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
        'estado',
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

    /**
     * CU7: cancelación lógica. Nunca delete()/forceDelete().
     * Conserva historial y libera disponibilidad.
     */
    public function cancelar(): bool
    {
        if ($this->isCancelado()) {
            return true;
        }

        return $this->update(['estado' => 'cancelado']);
    }

    /**
     * Detecta conflicto por rango horario + bloqueo por externo (HU-11).
     * Rango: [fecha_hora, fecha_hora + DURACION_MINUTOS).
     * Externo activo del mismo profesional bloquea todo el día.
     */
    public static function existeConflicto(
        int $profesionalId,
        \Carbon\CarbonInterface|\DateTimeInterface|string $fechaHora,
        ?int $excluirId = null,
        bool $esExternoNuevo = false
    ): bool {
        $inicio = $fechaHora instanceof \Carbon\CarbonInterface
            ? $fechaHora->copy()
            : \Carbon\Carbon::parse($fechaHora);
        $fin = $inicio->copy()->addMinutes(self::DURACION_MINUTOS);
        $ventanaInicio = $inicio->copy()->subMinutes(self::DURACION_MINUTOS);

        $query = self::query()
            ->activos()
            ->where('profesional_id', $profesionalId)
            ->whereBetween('fecha_hora', [$ventanaInicio, $fin])
            ->when($excluirId !== null, fn (Builder $q) => $q->where('id', '!=', $excluirId));

        $candidatos = $query->get(['id', 'fecha_hora', 'es_externo']);

        // Bloqueo por externo: cualquier externo activo ese día bloquea, y un
        // nuevo externo choca con cualquier turno activo ese día.
        $fechaDia = $inicio->toDateString();
        foreach ($candidatos as $candidato) {
            $candidatoFecha = $candidato->fecha_hora instanceof \Carbon\CarbonInterface
                ? $candidato->fecha_hora->toDateString()
                : \Carbon\Carbon::parse($candidato->fecha_hora)->toDateString();

            if ($candidatoFecha !== $fechaDia) {
                continue;
            }

            if ($esExternoNuevo || (bool) $candidato->es_externo) {
                return true;
            }
        }

        // Solapamiento por rango para turnos internos.
        foreach ($candidatos as $candidato) {
            $candInicio = $candidato->fecha_hora instanceof \Carbon\CarbonInterface
                ? $candidato->fecha_hora
                : \Carbon\Carbon::parse($candidato->fecha_hora);
            $candFin = $candInicio->copy()->addMinutes(self::DURACION_MINUTOS);

            if ($candInicio->lt($fin) && $inicio->lt($candFin)) {
                return true;
            }
        }

        // Externo fuera de la ventana horaria pero mismo día: buscar por fecha.
        $externoMismoDia = self::query()
            ->activos()
            ->where('profesional_id', $profesionalId)
            ->whereDate('fecha_hora', $fechaDia)
            ->where('es_externo', true)
            ->when($excluirId !== null, fn (Builder $q) => $q->where('id', '!=', $excluirId))
            ->exists();

        if ($externoMismoDia) {
            return true;
        }

        if ($esExternoNuevo) {
            return self::query()
                ->activos()
                ->where('profesional_id', $profesionalId)
                ->whereDate('fecha_hora', $fechaDia)
                ->when($excluirId !== null, fn (Builder $q) => $q->where('id', '!=', $excluirId))
                ->exists();
        }

        return false;
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
