<?php

namespace App\Models;

use Database\Factories\ServicioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Servicio extends Model
{
    /** @use HasFactory<ServicioFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'costo_servicio',
    ];

    protected function casts(): array
    {
        return [
            'costo_servicio' => 'decimal:2',
        ];
    }

    public function procesos(): HasMany
    {
        return $this->hasMany(Proceso::class);
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_servicios');
    }
}
