# Diseño: conflictos en el alta inicial CU1

Dentro de la transacción de `RegistrarClienteInicial`, bloquear el Coordinador que ya se reserva para el turno. Parsear la fecha solicitada y normalizar segundos a cero, igual que `TurnoScheduler`; consultar `Turno::existeConflicto` para ese Coordinador y esa fecha antes de las primeras escrituras.

Si existe conflicto, lanzar `ValidationException` asociada a `fecha_hora_inicial`. Laravel devuelve el formulario con sus datos y el mensaje visible junto al campo. La excepción revierte la transacción; al estar antes de las escrituras no se crean cliente, proceso, turno ni notificación interna.

El lock sobre la fila del Coordinador es la misma exclusión mutua usada por `TurnoScheduler`, cubriendo solicitudes concurrentes aunque aún no exista una fila de turno coincidente. La consulta de disponibilidad ignora los turnos cancelados y trata un turno externo programado como bloqueo de toda la fecha.

El registro interno mínimo existente se conserva sin cambios y no se agregan avisos externos.
