# CU1: rechazar conflictos en el turno inicial

## Objetivo

Evitar que el alta inicial reserve al Coordinador en un horario ya ocupado por otro turno o bloqueado por un compromiso externo.

## Alcance

- Comprobar disponibilidad dentro de la transacción de alta y antes de crear cliente, proceso, turno o registro interno.
- Informar el conflicto junto al campo de fecha y hora inicial.
- Serializar el alta frente al scheduler de turnos mediante el bloqueo existente de la fila del Coordinador.
- Permitir horarios liberados por turnos cancelados.
- Mantener el alta completa en horarios disponibles y el alcance de notificaciones existente.

## Criterios de salida

- Una coincidencia exacta o bloqueo externo en la fecha solicitada rechaza el alta sin persistencia parcial.
- La validación usa la misma normalización al minuto que el scheduler.
- Un turno cancelado no impide el alta.
- Tests PHPUnit focalizados y recorrido de interfaz real verifican conflicto, cancelación y alta válida.

## Riesgos

- La exclusión mutua depende de que todos los flujos de reserva del Coordinador bloqueen la misma fila `users`; `TurnoScheduler` ya sigue ese patrón.
- SQLite de tests no reproduce el bloqueo de fila de MySQL, por lo que la concurrencia se verifica en el diseño compartido del lock y con pruebas de conflicto secuencial.

## Fuera de alcance

- Envíos externos o cambios al módulo final de notificaciones.
- Cambios de roles CU1, agenda, turnos de seguimiento u otras tarjetas.
