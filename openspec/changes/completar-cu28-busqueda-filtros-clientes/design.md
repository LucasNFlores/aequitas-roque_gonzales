# Diseño

## Consulta

Autorizar el listado con la policy `ClientePolicy::viewAny`, mantener clientes no eliminados, y aplicar búsqueda textual sobre nombre, apellido o DNI. El filtro de estado consulta la relación de procesos y se combina mediante AND con la búsqueda. Cada cliente aparece una sola vez incluso si varios procesos coinciden. El orden debe ser determinista (`latest()` más id descendente) y la paginación debe conservar únicamente los filtros funcionales.

## Cliente con procesos de estados distintos

Decisión funcional confirmada: incluir al cliente si al menos uno de sus procesos coincide con el estado seleccionado (semántica `whereHas`). Así el filtro representa existencia de procesos en ese estado y un mismo cliente puede aparecer en más de un filtro; dentro de cada resultado aparece una sola vez.

## Interfaz

Un formulario GET comparte los parámetros con la paginación. Incluye búsqueda, estado, acción de aplicar y enlace de limpiar. Si hay filtros sin resultados, muestra un mensaje específico y conserva los controles para corregir o limpiar; el listado sin filtros y sin clientes conserva su propio mensaje.

## Permisos

Usar el permiso `listar_filtrar_clientes` a través de policy para el index y no conceder la capacidad a Profesional. Las rutas de escritura existentes mantienen sus policies propias; el listado no concede acceso a detalles fuera del alcance del usuario.

## Validación

Pruebas feature con perfiles Secretario, Coordinador, Directivo, Administrador y Profesional; búsqueda por nombre/apellido y DNI, filtro por estado, combinación, páginas con parámetros conservados, estado vacío y acceso directo denegado. En navegador, recorrer esos flujos con sesiones de los roles pertinentes.
