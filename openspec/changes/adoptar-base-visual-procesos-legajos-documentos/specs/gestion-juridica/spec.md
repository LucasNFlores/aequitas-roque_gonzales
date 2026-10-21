## ADDED Requirements

### Requirement: Aplicar la base visual Aequitas en procesos, legajos y documentos

Las pantallas activas de procesos, legajos, documentación e historial de reportes MUST aplicar los tokens visuales Aequitas ya disponibles para tipografía, superficies, texto, bordes, acciones y foco. La adaptación MUST conservar filtros, estados, acciones y visibilidad por rol existentes. MUST NOT ampliar permisos ni el alcance de los datos consultados. Las tablas con columnas densas MUST permanecer utilizables en pantallas pequeñas.

#### Scenario: Usuario consulta las pantallas del módulo

- **WHEN** un usuario autorizado consulta la bandeja, un proceso en un legajo, los documentos del proceso o un historial de reporte
- **THEN** la interfaz aplica tipografía operativa Figtree, superficies claras, texto oscuro y acciones primarias marino con foco dorado
- **AND** conserva los controles e información autorizados para ese usuario

#### Scenario: Usuario usa la bandeja o los detalles en móvil

- **WHEN** el usuario consulta una tabla densa o una tarjeta en un viewport angosto
- **THEN** la página conserva los filtros y acciones accesibles sin desbordar el viewport
- **AND** las tablas se desplazan dentro de su propio contenedor
