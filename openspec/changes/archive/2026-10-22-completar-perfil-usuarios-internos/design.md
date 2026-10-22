# Diseño: perfil completo de usuarios internos

## Modelo y compatibilidad

Agregar `users.apellido` como `string nullable` mediante una migración nueva. Mantener `users.name` para los nombres. Añadir ambos campos a `$fillable` y exponer `nombre_completo` como valor derivado que concatena los campos disponibles. No migrar datos existentes mediante separación heurística.

La creación y edición administrativa, junto con la edición del perfil propio, validarán nombre y apellido. La migración permite que las filas existentes sigan siendo válidas hasta que se actualicen manualmente.

## Rol único y jerarquía

La cuenta puede tener como máximo un rol. Los formularios HTTP y Livewire reciben un valor singular y reemplazan la asignación mediante `syncRoles`. Una migración de datos elimina asignaciones múltiples antes de crear un índice único sobre el tipo y el identificador del modelo. La migración conserva el rol oficial de mayor jerarquía, en este orden: Administrador, Directivo, Coordinador, Profesional y Secretario. Roles no incluidos quedan debajo de los oficiales; en empates desconocidos se conserva el nombre alfabéticamente primero y luego el menor `role_id` para que el resultado sea determinista.

El seeder documenta y crea los roles en el mismo orden jerárquico; los seeders de usuarios asignan un único rol.

La migración de roles elimina las asignaciones adicionales de forma irreversible: su `down()` puede retirar la restricción de unicidad, pero no puede reconstruir los roles eliminados. Esto refleja la decisión funcional de conservar solamente el rol de mayor jerarquía.

## Fecha de ingreso

Inicializar el campo a `today()` al abrir el formulario de alta Livewire y al renderizar el alta HTTP. El controlador y Livewire también aplicarán ese valor como respaldo si una petición de alta omite la fecha. La edición de cuentas existentes no asignará una fecha ficticia.

## Presentación y consulta

Usar `nombre_completo` en listados, saludos, selectores de profesionales/coordinadores, responsables, historial y auditoría que presenten una cuenta interna. Ampliar la búsqueda administrativa con `apellido`.

## Verificación

Probar alta HTTP y Livewire con fecha omitida y rol singular, edición de apellido, perfil propio, búsqueda por apellido, representación de cuentas antiguas con apellido nulo, normalización de roles múltiples y rechazo de una segunda asignación en la base. Actualizar la especificación vigente y la documentación de gestión de usuarios para que no describan roles combinados.
