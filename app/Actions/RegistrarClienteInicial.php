<?php

namespace App\Actions;

use App\Models\Cliente;
use App\Models\Notificacion;
use App\Models\Proceso;
use App\Models\Servicio;
use App\Models\Turno;
use App\Models\User;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RegistrarClienteInicial
{
    private const SERVICIO_INICIAL = 'Consulta legal inicial';

    /**
     * @param  array{nombre: string, apellido: string, dni: string, telefono: string, correo: string, domicilio: string, fecha_nacimiento: string, fecha_hora_inicial: string}  $attributes
     */
    public function handle(array $attributes): Cliente
    {
        return DB::transaction(function () use ($attributes): Cliente {
            $coordinador = User::query()
                ->role('Coordinador')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (! $coordinador instanceof User) {
                throw new DomainException('No hay un Coordinador activo para asignar el turno inicial.');
            }

            $servicio = Servicio::query()
                ->where('nombre', self::SERVICIO_INICIAL)
                ->first();

            if (! $servicio instanceof Servicio) {
                throw new DomainException('No está configurado el servicio inicial "Consulta legal inicial".');
            }

            $fechaHoraInicial = CarbonImmutable::parse($attributes['fecha_hora_inicial']);
            $cliente = Cliente::query()->create(Arr::except($attributes, 'fecha_hora_inicial'));
            $proceso = Proceso::query()->create([
                'cliente_id' => $cliente->id,
                'profesional_id' => null,
                'servicio_id' => $servicio->id,
                'coordinador_id' => $coordinador->id,
                'nombre' => $servicio->nombre.' - '.$cliente->nombre.' '.$cliente->apellido,
                'descripcion' => 'Proceso inicial creado automáticamente al registrar al cliente.',
                'fecha_inicio' => $fechaHoraInicial->toDateString(),
                'tipo' => Proceso::TIPOS[0],
                'estado' => 'pendiente',
                'motivo_rechazo' => null,
            ]);

            $turno = Turno::query()->create([
                'cliente_id' => $cliente->id,
                'profesional_id' => $coordinador->id,
                'coordinador_id' => $coordinador->id,
                'proceso_id' => $proceso->id,
                'fecha_hora' => $fechaHoraInicial,
                'es_externo' => false,
                'detalle_externo' => null,
                'tipo' => 'consulta_inicial',
            ]);

            Notificacion::query()->create([
                'user_id' => $coordinador->id,
                'cliente_id' => $cliente->id,
                'canal' => 'interno',
                'mensaje' => 'Se registró un turno inicial para '.$cliente->nombre.' '.$cliente->apellido.' el '.$turno->fecha_hora->format('d/m/Y H:i').'.',
                'fecha_envio' => now(),
                'estado' => 'enviado',
            ]);

            return $cliente;
        });
    }
}
