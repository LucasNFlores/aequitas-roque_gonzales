# Implementar HU-09: alta de cliente con proceso y turno inicial

## Objetivo

Completar el alta de cliente para que un Secretario o Administrador cree, en una sola operación, el cliente, su proceso inicial y un turno inicial asignado al Coordinador, con registro de notificación interna.

## Alcance

- Solicitar la fecha y hora del turno inicial al registrar un cliente.
- Crear el cliente, el proceso inicial en estado `pendiente`, el turno de tipo `consulta_inicial` y la notificación interna en una transacción.
- Asociar el proceso y el turno a un Coordinador activo.
- Reutilizar el servicio inicial existente `Consulta legal inicial`.
- Evitar registros derivados ante DNI o correo duplicados, configuración incompleta o errores durante la operación.
- Cubrir autorización, éxito, duplicados, configuración ausente y reversión mediante pruebas PHPUnit.

## Fuera de alcance

- Agenda, detección de conflictos, reprogramación y cancelación de turnos.
- Creación de procesos adicionales para clientes existentes.
- Integración de entrega por Brevo; esta HU deja el registro interno auditable.

## Criterio de salida

Un alta válida deja exactamente un cliente, un proceso inicial, un turno inicial y una notificación para el Coordinador. Cualquier fallo previo o intermedio no deja registros parciales.

## Riesgos

- No contar con Coordinador o servicio inicial configurados. Se cancela toda la operación y se informa el motivo.
- La columna histórica `profesional_id` de `turnos` es obligatoria. Se conserva por compatibilidad y se agrega `coordinador_id` explícito para el turno inicial.
- Una segunda solicitud concurrente puede superar la validación previa. La restricción única de cliente y la transacción impiden derivados duplicados.
