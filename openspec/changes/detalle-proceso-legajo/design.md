# Diseño

## Rutas y autorización

- Mantener `/clientes/{cliente}/legajo` como ficha con los datos del cliente y resúmenes de procesos.
- Agregar `/clientes/{cliente}/legajo/procesos/{proceso}` con binding acotado al cliente.
- Autorizar la consulta por CU18 y obtener el proceso desde `Cliente::procesos()` con `visibleTo($user)`; un proceso ajeno o no asignado se responde como no encontrado.
- Cargar relaciones de turno, servicio, usuarios, documentos, versiones, reportes, comprobantes e historial mediante eager loading.
- La edición de datos del cliente, estado del proceso, reportes, documentos, turnos y comprobantes sigue controlada por sus policies y permisos existentes.

## Interfaz

- En el legajo, usar tarjetas compactas que muestren nombre, servicio, tipo, estado, inicio y responsables, con un enlace accesible al detalle.
- En el detalle, presentar encabezado del caso, datos del cliente, acciones autorizadas, turnos e historial lado a lado en pantallas amplias, y secciones apiladas en móvil.
- Mantener el resto de relaciones en secciones con estados vacíos claros.
- Reutilizar colores, radios, tipografía y foco visibles de los tokens Aequitas.
- No incluir rutas de archivos en los datos del detalle; las acciones de archivo siguen pasando por las rutas documentales existentes.

## Verificación

- PHPUnit cubre el acceso permitido, el alcance del Profesional, el aislamiento entre clientes, los controles de edición por policy y la denegación de accesos directos.
- Ejecutar Pint, pruebas afectadas y `git diff --check`.
- Levantar Laravel local sin Docker y recorrer con navegador la ficha, apertura del detalle y variantes de permisos.
