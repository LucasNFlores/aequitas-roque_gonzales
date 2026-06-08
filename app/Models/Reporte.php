<?php

namespace App\Models;

use Database\Factories\ReporteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reporte extends Model
{
    /** @use HasFactory<ReporteFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'proceso_id',
        'profesional_id',
        'contenido',
        'fecha',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profesional_id');
    }
}
