# Implementar HU-07: gestión de procesos jurídicos

## Necesidad

La bandeja `/procesos` ya permite consultar procesos, cambiar estados generales y revisar el historial. HU-07 requiere completar la admisión y el rechazo, la asignación y reasignación de profesionales, y los filtros operativos del proceso.

## Autoridad funcional

- Tarjeta HU-07 de Trello, consultada sin modificarla.
- Matriz online Aequitas V2, pestaña `Aequitas - Matriz de Módulos, Funciones y Permisos por Rol V2`.
- `docs/transcripcion_de_docs.md` y `openspec/specs/gestion-juridica/spec.md`.

La matriz confirma: CU15/CU15.1 para Coordinador y Administrador; CU16/CU17 para Secretario, Coordinador y Administrador; CU20 para Profesional asignado, Coordinador y Administrador; CU29/CU30 para los cinco roles, con alcance del Profesional.

## Alcance

- Admitir o rechazar procesos pendientes; exigir causal no vacía para el rechazo y conservarla en el proceso y en el historial.
- Asignar o reasignar profesionales activos que tengan el rol Profesional y el servicio compatible con el proceso.
- Guardar honorarios del proceso al asignarlo o reasignarlo tomando el costo de referencia del servicio.
- Completar búsqueda y filtros por cliente, servicio, estado, coordinador, profesional y rango de fechas, con paginación.
- Mantener el historial con estado anterior/nuevo, fecha, actor y motivo.
- Proteger las acciones en Livewire/policies y conservar el alcance del Profesional en consultas, historial y acciones.

## Fuera de alcance

- Modificar la hoja de Google Drive o mover/editar la tarjeta Trello.
- Notificaciones, cambios de agenda y cambios a la administración del catálogo de estados.
- Cambiar la creación de clientes o procesos adicionales.

## Criterios de salida

- Los flujos permitidos funcionan desde el navegador contra la aplicación en Docker.
- Las pruebas PHPUnit cubren permisos por rol, transiciones, validación del rechazo, compatibilidad de asignación, honorarios, filtros y acceso a procesos ajenos.
- Pint, prueba de la funcionalidad, compilación frontend y `git diff --check` finalizan correctamente.
- Los datos creados para la validación manual se eliminan de forma selectiva; no se borran datos preexistentes.
- El cambio revisado se integra en `dev` local, sin push.

## Riesgos

- Los flujos especiales dependen de que existan estados activos con slug estable `admitido` y `rechazado`; se rechazarán operaciones si el catálogo los desactiva.
- El esquema actual no tiene honorarios por proceso. La nueva columna será nullable y no se rellenará sobre procesos preexistentes.
- Las opciones de asignación y los filtros deben excluir usuarios y servicios dados de baja, y nunca ampliar el alcance del Profesional.
