<?php

namespace App\Models;

use Database\Factories\HistorialEstadoProcesoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialEstadoProceso extends Model
{
    /** @use HasFactory<HistorialEstadoProcesoFactory> */
    use HasFactory;

    protected $table = 'historial_estados_proceso';

    protected $fillable = [
        'proceso_id',
        'usuario_id',
        'estado_anterior',
        'estado_nuevo',
        'motivo',
        'fecha_cambio',
    ];

    protected function casts(): array
    {
        return [
            'fecha_cambio' => 'datetime',
        ];
    }

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id')->withTrashed();
    }
}
