<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteVersion extends Model
{
    protected $table = 'reporte_versiones';

    protected $fillable = [
        'reporte_id',
        'contenido_anterior',
        'fecha_anterior',
        'usuario_id',
        'usuario_nombre',
        'fecha_cambio',
    ];

    protected function casts(): array
    {
        return [
            'fecha_anterior' => 'date',
            'fecha_cambio' => 'datetime',
        ];
    }

    public function reporte(): BelongsTo
    {
        return $this->belongsTo(Reporte::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id')->withTrashed();
    }
}
