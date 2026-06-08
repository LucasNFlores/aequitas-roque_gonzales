<?php

namespace App\Models;

use Database\Factories\DocumentoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documento extends Model
{
    /** @use HasFactory<DocumentoFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'proceso_id',
        'archivo_path',
        'tipo_documento',
        'nombre',
    ];

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }
}
