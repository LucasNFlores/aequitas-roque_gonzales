# Tareas de planificación para aplicar

## Preparación

- [ ] Revisar el estado de la migración `turnos` y de datos existentes antes de modificar el esquema.
- [ ] Confirmar que no existe una categoría documental o relación equivalente fuera de `Documento.tipo_documento`.
- [ ] Definir migraciones reversibles para estado de turno y categorías, sin alterar datos no relacionados.

## Turnos y cancelación

- [ ] Añadir estado operativo `programado`/`cancelado` a los turnos y migrar los existentes a `programado`.
- [ ] Completar la autorización por subtipo en policy, Form Requests, rutas y controlador, alineada con CU4–CU7.
- [ ] Completar las reglas de cliente, proceso, profesional, tipo, flag externo y detalle externo en creación y actualización.
- [ ] Implementar conflictos por fecha/hora exacta y bloqueo de jornada completa para externos, dentro de una transacción.
- [ ] Implementar reprogramación segura y cancelación sin borrado, conservando el historial.
- [ ] Emitir y persistir la notificación auditable de alta, reprogramación y cancelación.

## Agenda

- [ ] Implementar consulta por profesional y rango de fecha, usando alcance previo del Profesional y relaciones precargadas.
- [ ] Representar tipos de turno, bloqueo externo, estado cancelado e historial sin otorgar operaciones de escritura.
- [ ] Manejar rangos inválidos, agenda vacía, datos relacionados ausentes y respuestas 403 sin exponer información.

## Categorías documentales

- [ ] Crear modelo, factory, seeder, migración y relación de categoría documental con nombre único, activo y baja lógica.
- [ ] Conservar `tipo_documento` y migrar solo asociaciones inequívocas; identificar las históricas sin categoría vinculada.
- [ ] Restringir las nuevas cargas a categorías activas/no eliminadas y mantener lectura de categorías históricas o inactivas.
- [ ] Implementar alta, edición, activación, desactivación y baja lógica con autorización de CU37.
- [ ] Proteger documentos y archivos al desactivar o dar de baja categorías asociadas.

## Calidad y cierre

- [ ] Crear pruebas PHPUnit de permisos, URLs directas, alcance del Profesional, validaciones, conflictos, cancelación y notificaciones.
- [ ] Crear pruebas PHPUnit del ciclo de categorías, validación de nombre, datos históricos y preservación de documentos/archivos.
- [ ] Ejecutar Pint, las pruebas afectadas y `git diff --check` al aplicar código.
- [ ] Actualizar la documentación operativa afectada después de la implementación y antes de archivar el cambio.
