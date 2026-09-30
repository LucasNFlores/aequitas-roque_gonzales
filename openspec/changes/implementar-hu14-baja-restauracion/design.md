# Diseño de HU-14: ciclo de vida de categorías documentales

## Persistencia

- `categorias_documento.deleted_at` se añade en una migración nueva y reversible; las migraciones existentes no se modifican.
- `CategoriaDocumento` usa `SoftDeletes`. La relación `Documento::categoria()` incluye categorías con baja lógica para lectura histórica.
- La baja lógica conserva `documentos.categoria_id`, `documentos.tipo_documento` y `archivo_path`; la FK continúa impidiendo el borrado físico.
- Los documentos sin categoría permanecen sin vínculo. La interfaz conserva visible su `tipo_documento` histórico; no se realiza backfill ambiguo.

## Reglas del ciclo de vida

- Activar/desactivar solo cambia `activo`. Se conserva la regla vigente que impide desactivar si existen documentos activos asociados sin transición válida.
- Dar de baja establece `deleted_at` sin modificar `activo` ni desvincular documentos. Los documentos y sus archivos permanecen intactos.
- Restaurar limpia `deleted_at` y mantiene `activo`. Si la categoría estaba inactiva, seguirá fuera de nuevas cargas hasta reactivarse.
- La eliminación física se deniega con la policy `forceDelete` y no se expone en la interfaz.

## Autorización e interfaz

- `delete` y `restore` usan la policy `CategoriaDocumentoPolicy` y el permiso CU37 existente.
- Cada acción Livewire vuelve a autorizar en servidor; los botones solo se muestran según policy.
- El filtro administrativo distingue categorías dadas de baja de las activas/inactivas. La confirmación explica la conservación de documentos y archivos.
- Las cargas nuevas enumeran solo categorías activas y no eliminadas; Form Requests y Livewire vuelven a validar estas dos condiciones.

## Verificación

- Probar baja lógica separada de desactivación, restauración y conservación de `activo`.
- Probar acceso autorizado, denegación de borrado físico, y que categorías dadas de baja no se listen ni se acepten en nuevas cargas.
- Probar lectura de documentos asociados a categorías con baja lógica y preservación de `categoria_id`, `tipo_documento` y archivo físico.
- Probar documento histórico sin categoría, conservando `tipo_documento` y el archivo.
