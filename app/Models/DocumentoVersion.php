<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoVersion extends Model
{
    protected $table = 'documento_versiones';

    protected $fillable = [
        'documento_id',
        'usuario_id',
        'archivo_path',
        'categoria_id',
        'categoria_nombre',
        'tipo_documento',
        'nombre',
        'fecha_reemplazo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_reemplazo' => 'datetime',
        ];
    }

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id')->withTrashed();
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaDocumento::class)->withTrashed();
    }
}
