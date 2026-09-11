<?php

namespace App\Models;

use Database\Factories\ComprobantePagoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComprobantePago extends Model
{
    /** @use HasFactory<ComprobantePagoFactory> */
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'proceso_id',
        'archivo_path',
        'fecha_subida',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_subida' => 'date',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * El vínculo con el proceso es opcional. Se deja preparado para poder
     * asociar un comprobante a una causa cuando ese flujo esté disponible.
     */
    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }
}
