# Implementar la baja lógica y restauración de categorías documentales

## Objetivo

Completar HU-14/CU37 para que dar de baja una categoría sea distinto de desactivarla, permita restaurarla y conserve los documentos históricos y sus archivos.

## Alcance

- Incorporar `SoftDeletes` y `deleted_at` a categorías documentales mediante una migración reversible.
- Mantener operaciones separadas: activar/desactivar cambia `activo`; dar de baja establece `deleted_at`; restaurar limpia `deleted_at` sin cambiar `activo`.
- Autorizar baja y restauración por policy, proteger las acciones Livewire y ofrecerlas en la interfaz de gestión.
- Excluir categorías inactivas o dadas de baja de nuevas cargas y rechazar intentos directos con esos identificadores.
- Conservar las asociaciones y archivos de documentos al dar de baja una categoría; permitir leer el nombre de la categoría histórica.
- Conservar y mostrar `tipo_documento` y el archivo físico de documentos históricos sin categoría.
- Añadir cobertura PHPUnit focalizada y actualizar la documentación funcional afectada.

## Fuera de alcance

- Cambios en los flujos de turnos, agenda o permisos generales de otros módulos.
- Eliminación física de categorías, documentos o archivos.
- Asignar retroactivamente categorías cuando la equivalencia con `tipo_documento` no sea inequívoca.

## Criterios de aceptación

- Desactivar no establece `deleted_at`; la baja lógica sí, y ambas operaciones conservan las reglas existentes para documentos asociados.
- Restaurar conserva el valor previo de `activo`; solo una categoría activa y restaurada se ofrece en cargas nuevas.
- Ninguna ruta o acción de servidor permite `forceDelete` ni seleccionar categorías inactivas/dadas de baja en una carga nueva.
- Los documentos asociados conservan `categoria_id`, `tipo_documento` y archivo físico; su consulta histórica puede resolver una categoría con baja lógica.
- Un documento sin `categoria_id` sigue exponiendo su `tipo_documento` histórico y su archivo.

## Riesgos

- Una relación normal a una categoría con baja lógica aparentaría estar vacía; el vínculo histórico debe consultar `withTrashed()`.
- La restauración no debe activar implícitamente una categoría que se había desactivado antes de la baja.
- El esquema ya impide nombres duplicados; la validación de la interfaz debe seguir considerando también las categorías dadas de baja.
