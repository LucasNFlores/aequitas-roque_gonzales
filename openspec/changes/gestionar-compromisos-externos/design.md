# Diseño: compromisos externos

## Persistencia

`Turno` ya contiene tipo, indicador externo, detalle y estado. La migración `2026_10_01_000007_add_estado_to_turnos_table` agrega `programado`/`cancelado`, incluye índice, aplica backfill a los registros existentes y tiene `down()` reversible. HU-13 la reutiliza sin agregar un segundo modelo o migración.

La cancelación actualiza solo `estado`; conserva fecha, profesional, detalle, relaciones e identificador. Los turnos cancelados no bloquean disponibilidad.

## Reglas y flujo

- El alta escribe `tipo=externo`, `es_externo=true` y detalle no vacío.
- El profesional debe estar activo y tener rol Profesional. Un proceso opcional debe pertenecer al cliente y, si tiene profesional asignado, coincidir con el profesional del turno.
- Un externo programado ocupa toda la fecha del profesional; tampoco permite otros turnos internos o de seguimiento ese día.
- El scheduler valida disponibilidad y persiste dentro de una transacción. En reprogramación excluye el turno actual y conserva su fecha original si la nueva fecha entra en conflicto.
- Los filtros operativos siguen mostrando externos y cancelados como historial; CU8 no incluye acciones de escritura.

## Autorización e interfaz

La policy, middleware de rutas y componente Livewire comprueban los permisos de CU5/CU6.1/CU7. La interfaz reutiliza el modelo y el scheduler actuales, añade un formulario dedicado a externos y muestra acciones solo cuando la policy las permite. El componente vuelve a autorizar y validar cada llamada directa.

Secretario y Administrador pueden escribir. Profesional, Coordinador y Directivo pueden recibir 403 o no tienen acceso a CU8 según la matriz, y nunca obtienen controles de escritura. La lectura de agenda conserva el alcance previo del Profesional.

## Notificaciones

No se envían, registran ni programan notificaciones o recordatorios en HU-13; quedan para la tarjeta futura indicada por el usuario.

## Verificación

Probar roles y acceso directo, detalle obligatorio, relaciones, bloqueo diario en ambas direcciones, reprogramación con conflicto y sin conflicto, cancelación histórica, disponibilidad liberada, agenda y persistencia en navegador real y tests PHPUnit en Docker.
