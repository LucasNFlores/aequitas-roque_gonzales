# Diseño de turnos, agenda y categorías documentales

## Principios

- La matriz V2 define autorización; nombres técnicos de permisos Spatie no sustituyen los CUs.
- La autorización se aplica en interfaz, ruta, Form Request, policy y consulta.
- Las consultas operativas excluyen turnos cancelados y bajas lógicas; la auditoría conserva ambos.
- El Administrador mantiene acceso a todas las capacidades sin excepción.

## Turnos

### Persistencia

`turnos` ya incluye `cliente_id`, `profesional_id`, `proceso_id`, `fecha_hora`, `tipo`, `es_externo` y `detalle_externo`, pero no representa cancelación operativa. Al aplicar se añadirá un estado `programado`/`cancelado`, con valor inicial `programado` para los registros existentes y una migración reversible.

La cancelación no ejecutará `delete()` ni `softDelete()`. El turno seguirá disponible para historial, pero no participará en la disponibilidad.

### Reglas de integridad

- `consulta_inicial` y `seguimiento` tienen `es_externo=false` y no admiten `detalle_externo`.
- `externo` tiene `es_externo=true` y exige `detalle_externo`.
- El profesional debe estar activo y tener rol Profesional.
- Si se informa `proceso_id`, el proceso pertenece al cliente. Si ya tiene profesional asignado, debe coincidir con `profesional_id`.
- Un turno de seguimiento exige un proceso activo; una consulta inicial puede no tener proceso.

### Conflictos y reprogramación

- Dos turnos activos internos o de seguimiento del mismo profesional no pueden compartir `fecha_hora`.
- Un turno externo activo bloquea todo el día de su `fecha_hora`: no admite otro externo, interno ni de seguimiento del mismo profesional en esa fecha.
- Al crear o actualizar se consulta dentro de una transacción, excluyendo el propio registro durante la actualización; se valida la nueva fecha antes de liberar la anterior.
- No se infiere una duración ni solapamientos por intervalos porque la fuente funcional no los define.

### Autorización y agenda

| Operación | Secretaría | Profesional | Coordinador | Directivo | Administrador |
| --- | --- | --- | --- | --- | --- |
| Crear/modificar/cancelar turnos | Sí | No | No | No | Sí |
| Consultar agenda | Sí | Solo propia | Sí | No | Sí |

`Turno::visibleTo()` debe restringir al Profesional antes de aplicar filtros de profesional o rango. La agenda consulta un profesional y un rango acotado (día, semana o rango explícito) y carga las relaciones necesarias para evitar N+1. Muestra interno, seguimiento, externo y cancelado; este último solo como historial y nunca como bloqueo.

### Notificaciones

La creación, reprogramación y cancelación producen un registro auditable con destinatario, canal, mensaje, fecha, estado y resultado. El cambio emite la acción/evento necesario para la infraestructura de notificaciones; no crea una integración directa duplicada con Brevo.

## Categorías documentales

### Persistencia y transición

Se creará una entidad de categoría con nombre único, indicador de activo, baja lógica y relación opcional desde `Documento`. La relación debe coexistir inicialmente con `documentos.tipo_documento`.

Durante la migración, solo se vincularán documentos cuando la equivalencia con una categoría sea inequívoca. Los casos ambiguos o sin coincidencia conservan `tipo_documento`, quedan sin relación y se presentan como categoría histórica. La migración no borra ni reemplaza archivos, documentos ni sus valores históricos.

### Ciclo de vida

- Las nuevas cargas muestran únicamente categorías activas y no eliminadas.
- Una categoría inactiva o dada de baja sigue siendo legible en documentos ya asociados, pero no es seleccionable en nuevas cargas.
- Desactivar o dar de baja requiere comprobar asociaciones activas y conservar una relación o historial válido.
- La baja es lógica; no borra documentos ni archivos físicos.

### Autorización e interfaz

Solo Coordinador y Administrador pueden crear, editar, activar, desactivar o dar de baja categorías. La UI muestra listado de activas/inactivas, formulario con validación de nombre y confirmación de baja. La autorización debe repetirse en policy, ruta, request y acción de servidor; ocultar botones no es suficiente.

## Verificación

Las pruebas deben incluir:

- Matriz completa de roles para CU4–CU8 y CU37, incluso por URL o petición directa.
- Alcance del Profesional para la agenda.
- Coherencia tipo/flag/detalle, consistencia cliente-proceso-profesional y conflictos de turnos.
- Reprogramación, cancelación, conservación de historial y generación de notificaciones auditables.
- Ciclo activo/inactivo/baja de categorías, nombre vacío o duplicado, selección en nuevas cargas y preservación de documentos históricos.

## Riesgos y controles

| Riesgo | Control |
| --- | --- |
| Carrera al reservar una fecha | Validar conflictos dentro de la transacción de persistencia. |
| Cancelar mediante borrado | Estado explícito y pruebas que confirmen conservación del registro. |
| Pérdida de clasificación histórica | Relación opcional y migración solo para coincidencias inequívocas. |
| Escalamiento por URL/UI | Policy, Form Request, ruta y consulta protegidas. |
