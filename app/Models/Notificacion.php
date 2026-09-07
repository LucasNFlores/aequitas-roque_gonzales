<?php

namespace App\Models;

use Database\Factories\NotificacionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notificacion extends Model
{
    public const CANALES = ['email', 'whatsapp'];

    public const ESTADOS = ['enviado', 'fallido'];

    /** @use HasFactory<NotificacionFactory> */
    use HasFactory, SoftDeletes;

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->can('consultar_notificaciones')
            ? $query
            : $query->whereKey(0);
    }

    protected $table = 'notificaciones';

    protected $fillable = [
        'user_id',
        'cliente_id',
        'canal',
        'mensaje',
        'fecha_envio',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_envio' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
