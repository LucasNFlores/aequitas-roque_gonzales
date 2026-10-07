# Implementar HU-09: alta de cliente con proceso y turno inicial

> **Aclaración de alcance:** esta HU conserva el registro interno mínimo que ya crea el alta. No entrega CU-35 ni HU-18 a HU-20: la bandeja, las reglas y los canales se implementan en el módulo independiente de última fase, que revisará este registro al integrar los eventos. No se confirma Brevo sin revisar costos vigentes.

## Objetivo

Completar el alta de cliente para que un Secretario o Administrador cree, en una sola operación, el cliente, su proceso inicial y un turno inicial asignado al Coordinador, con registro de notificación interna.

## Alcance

- Solicitar la fecha y hora del turno inicial al registrar un cliente.
- Crear el cliente, el proceso inicial en estado `pendiente`, el turno de tipo `consulta_inicial` y la notificación interna en una transacción.
- Asociar el proceso y el turno a un Coordinador activo.
- Usar el servicio inicial `Consultoría`, tal como lo establecen la matriz funcional y la especificación base.
- Comprobar que el estado `pendiente` exista, esté activo y no haya sido dado de baja antes de crear el proceso.
- Respaldar la unicidad de DNI y correo con restricciones de base de datos, además de la validación del formulario.
- Evitar registros derivados ante DNI o correo duplicados, configuración incompleta o errores durante la operación.
- Cubrir autorización, éxito, duplicados, configuración ausente y reversión mediante pruebas PHPUnit.

## Fuera de alcance

- Agenda, detección de conflictos, reprogramación y cancelación de turnos.
- Creación de procesos adicionales para clientes existentes.
- Integración de entrega por proveedor externo; esta HU deja solo el registro interno auditable y no decide proveedor. La revisión de Brevo se hará al comenzar el módulo de última fase.

## Criterio de salida

Un alta válida deja exactamente un cliente, un proceso inicial, un turno inicial y una notificación para el Coordinador. Cualquier fallo previo o intermedio no deja registros parciales.

## Riesgos

- No contar con Coordinador o servicio inicial configurados. Se cancela toda la operación y se informa el motivo.
- Un estado `pendiente` inactivo o dado de baja no puede iniciar un proceso.
- Si existen correos duplicados históricos, la migración del índice único se detiene para permitir su resolución antes del despliegue.
- Si ya existe tanto el nombre legado como `Consultoría`, la migración de normalización se detiene para evitar ambigüedad en el catálogo.
- La columna histórica `profesional_id` de `turnos` es obligatoria. Se conserva por compatibilidad y se agrega `coordinador_id` explícito para el turno inicial.
- Una segunda solicitud concurrente puede superar la validación previa. La restricción única de cliente y la transacción impiden derivados duplicados.
