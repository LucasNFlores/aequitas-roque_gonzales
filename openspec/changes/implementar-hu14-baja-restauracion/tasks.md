# Tareas de implementación

- [x] Incorporar el CRUD base de categorías, el vínculo opcional desde documentos y su selector de cargas.
- [x] Agregar la migración reversible de `deleted_at` y habilitar `SoftDeletes`.
- [x] Conservar la relación con categorías dadas de baja en la lectura de documentos históricos.
- [x] Separar la baja lógica de activar/desactivar y añadir restauración sin cambiar el indicador `activo`.
- [x] Completar policy, autorización de acciones y controles de interfaz para baja/restauración; denegar borrado físico.
- [x] Excluir categorías inactivas o dadas de baja de cargas nuevas y rechazarlas en servidor.
- [x] Cubrir documentos asociados y documentos históricos sin categoría, preservando metadatos y archivos físicos.
- [x] Actualizar la documentación funcional existente y OpenSpec.
- [x] Ejecutar Pint, las pruebas focalizadas en Docker y `git diff --check`.
