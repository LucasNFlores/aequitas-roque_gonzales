# Tareas de planificación para aplicar

## Preparación

- [x] Revisar la migración de `turnos`; la implementación de categorías no modifica ese esquema.
- [x] Confirmar el modelo, tabla y relación documental existentes antes de ampliar la transición desde `Documento.tipo_documento`.
- [x] Mantener migraciones reversibles para el estado de turno y el esquema de categorías, conservando el texto histórico y datos no relacionados.

## Turnos y cancelación

- [x] Añadir estado operativo `programado`/`cancelado` a los turnos y migrar los existentes a `programado`.
- [x] Completar la autorización de HU-11 (consulta inicial y seguimiento) en policy, componentes Livewire y rutas, alineada con CU4, CU4.1, CU6 y CU7.
- [x] Validar cliente, proceso activo, profesional y tipo para alta/reprogramación de turnos internos y de seguimiento; los compromisos externos se mantienen fuera de HU-11.
- [x] Implementar conflictos por fecha/hora exacta y bloqueo de jornada completa por compromisos externos activos, dentro de una transacción.
- [x] Implementar reprogramación segura y cancelación sin borrado, conservando el historial.

## Agenda

- [x] Implementar consulta por profesional y rango acotado de fecha, usando alcance previo del Profesional y relaciones precargadas.
- [x] Representar tipos de turno, bloqueo externo, estado cancelado e historial sin otorgar operaciones de escritura.
- [x] Manejar rango máximo, agenda vacía, relaciones ausentes y respuestas 403 sin exponer información.

## Categorías documentales

- [x] Crear y verificar modelo, factory, seeder, migración y relación de categoría documental con nombre único, estado activo y baja lógica.
- [x] Conservar `tipo_documento`; asociar solo coincidencias exactas e identificar las históricas sin vínculo.
- [x] Restringir nuevas cargas a categorías activas/no eliminadas y mantener lectura y edición histórica de categorías inactivas o sin vínculo.
- [x] Implementar alta, edición, activación, desactivación y baja lógica con autorización de CU37.
- [x] Proteger documentos y archivos al desactivar o dar de baja categorías asociadas.

## Calidad y cierre

- [x] Crear pruebas PHPUnit de permisos, URLs directas, alcance del Profesional, validaciones, conflictos y cancelación.
- [x] Crear y ejecutar pruebas PHPUnit del ciclo de categorías, validación de nombre, backfill histórico y preservación de documentos/archivos.
- [x] Ejecutar Pint, las pruebas afectadas y `git diff --check` al aplicar código.
- [x] Actualizar la documentación operativa afectada después de la implementación.
