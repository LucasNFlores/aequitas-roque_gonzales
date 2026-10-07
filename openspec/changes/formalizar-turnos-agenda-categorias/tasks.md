# Tareas de planificación para aplicar

## Preparación

- [ ] Revisar el estado de la migración `turnos` y de datos existentes antes de modificar el esquema.
- [ ] Confirmar que no existe una categoría documental o relación equivalente fuera de `Documento.tipo_documento`.
- [ ] Definir migraciones reversibles para estado de turno y categorías, sin alterar datos no relacionados.

## Turnos y cancelación

- [x] Añadir estado operativo `programado`/`cancelado` a los turnos y migrar los existentes a `programado`.
- [x] Completar la autorización de HU-11 (consulta inicial y seguimiento) en policy, componentes Livewire y rutas, alineada con CU4, CU4.1, CU6 y CU7.
- [x] Validar cliente, proceso activo, profesional y tipo para alta/reprogramación de turnos internos y de seguimiento; los compromisos externos se mantienen fuera de HU-11.
- [x] Implementar conflictos por fecha/hora exacta y bloqueo de jornada completa por compromisos externos activos, dentro de una transacción.
- [x] Implementar reprogramación segura y cancelación sin borrado, conservando el historial.
- [x] Notificaciones de alta, reprogramación y cancelación: fuera de esta entrega; integrar los eventos en el [módulo independiente de fase final](https://trello.com/c/voW5TBHi/75-m%C3%B3dulo-de-notificaciones-integraci%C3%B3n-final), después de validar dicho módulo por separado.

## Agenda

- [x] Implementar consulta por profesional y rango acotado de fecha, usando alcance previo del Profesional y relaciones precargadas.
- [x] Representar tipos de turno, bloqueo externo, estado cancelado e historial sin otorgar operaciones de escritura.
- [x] Manejar rango máximo, agenda vacía, relaciones ausentes y respuestas 403 sin exponer información.

## Categorías documentales

- [ ] Crear modelo, factory, seeder, migración y relación de categoría documental con nombre único, activo y baja lógica.
- [ ] Conservar `tipo_documento` y migrar solo asociaciones inequívocas; identificar las históricas sin categoría vinculada.
- [ ] Restringir las nuevas cargas a categorías activas/no eliminadas y mantener lectura de categorías históricas o inactivas.
- [ ] Implementar alta, edición, activación, desactivación y baja lógica con autorización de CU37.
- [ ] Proteger documentos y archivos al desactivar o dar de baja categorías asociadas.

## Calidad y cierre

- [x] Crear pruebas PHPUnit de permisos, URLs directas, alcance del Profesional, validaciones, conflictos y cancelación; verificar explícitamente que HU-11 no genere notificaciones.
- [ ] Crear pruebas PHPUnit del ciclo de categorías, validación de nombre, datos históricos y preservación de documentos/archivos.
- [ ] Ejecutar Pint, las pruebas afectadas y `git diff --check` al aplicar código.
- [ ] Actualizar la documentación operativa afectada después de la implementación y antes de archivar el cambio.
