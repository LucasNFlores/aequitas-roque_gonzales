## ADDED Requirements

### Requirement: Limitar métricas y gráficos del Profesional a su cartera actual

El dashboard del Profesional MUST incluir procesos activos asignados, cola de seguimiento propia, turnos propios próximos siete días y clientes visibles en sus legajos. MUST NOT incluir una métrica de reportes. Toda consulta o agregación de datos por proceso MUST aplicar la asignación vigente antes de contar, agrupar, ordenar o limitar. Los demás roles MUST conservar su comportamiento autorizado.

#### Scenario: Panel Profesional con datos propios

- **WHEN** un Profesional consulta `/dashboard`
- **THEN** procesos, cola de seguimiento, clientes y turnos contienen únicamente datos de su alcance autorizado
- **AND** el panel muestra accesos a procesos, legajos y agenda según CU 29, CU 18 y CU 8
- **AND** no se muestra la tarjeta «Reportes recientes»

#### Scenario: Distribución de procesos por estado

- **WHEN** el Profesional tiene procesos activos en dos o más estados
- **THEN** el dashboard muestra el gráfico de barras horizontales y los valores legibles en una lista accesible
- **AND** ambos resúmenes incluyen solamente procesos actualmente asignados

#### Scenario: Cero o un estado activo

- **WHEN** el Profesional no tiene procesos activos o todos están en un único estado
- **THEN** el dashboard conserva la lista o su estado vacío y no crea un canvas de gráfico

#### Scenario: Proceso reasignado

- **WHEN** un reporte pertenece a un proceso que ya no está asignado a su autor Profesional
- **THEN** `Reporte::visibleTo($profesional)` excluye el reporte
- **AND** las consultas del Profesional deniegan el acceso conforme a CU 24
