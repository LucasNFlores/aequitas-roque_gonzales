# Diseño de gestión de estados e historial

## Persistencia

Se conservará `procesos.estado` como clave textual estable para no romper las consultas y factories existentes, pero se quitará la restricción `enum` mediante una migración. El valor almacenado será el `slug` de un estado del catálogo.

La tabla `estados_proceso` tendrá:

- `nombre` visible para el usuario.
- `slug` estable para ser almacenado en `procesos.estado`.
- `activo` para impedir que los estados inactivos se usen en nuevas transiciones.
- `posicion` para ordenar la lista de estados.
- timestamps y baja lógica para conservar referencias históricas.

La tabla `historial_estados_proceso` tendrá el proceso, estado anterior, estado nuevo, fecha de cambio, usuario interviniente y motivo. El registro inicial se creará con estado anterior nulo.

## Reglas de autorización

| Capacidad | Roles |
|---|---|
| Listar y filtrar procesos | Secretario, Profesional, Coordinador, Directivo, Administrador |
| Consultar historial | Secretario, Profesional asignado, Coordinador, Directivo, Administrador |
| Actualizar estado | Profesional asignado, Coordinador, Administrador |
| Gestionar catálogo de estados | Coordinador, Administrador |

La consulta de procesos usará `Proceso::visibleTo()` y cargará relaciones necesarias antes de renderizar. El Profesional nunca recibirá procesos ajenos ni podrá abrir su historial o cambiar su estado.

## Transiciones

`Proceso::transitionTo()` validará que el estado destino sea activo y guardará el motivo temporalmente para que el evento de actualización cree el historial con el usuario autenticado. La creación y cualquier cambio del atributo `estado` quedarán registrados, incluso si la actualización proviene de otro punto del sistema.

La desactivación de un estado se rechazará cuando existan procesos no eliminados que lo utilicen. Los estados inactivos seguirán disponibles para mostrar el historial y el estado actual de procesos antiguos.

## Interfaz

- `/procesos`: página Blade que monta `Procesos\Index`, con búsqueda, filtro por estado, acciones autorizadas y modal de historial/transición.
- `/estados-proceso`: página Blade que monta `EstadosProceso\Index`, con alta, edición, ordenamiento, activación y desactivación.
- Alpine.js manejará apertura, cierre, foco visual y escape de modales.
- Livewire ejecutará validación, autorización, consultas y persistencia en servidor.
- Tailwind CSS seguirá las convenciones visuales existentes y contemplará diseño responsive.

## Verificación

Las pruebas cubrirán migraciones, seeders, historial inicial y de transición, filtros y alcance del Profesional, autorización por rol, CRUD/orden/activación del catálogo y restricciones de desactivación.
