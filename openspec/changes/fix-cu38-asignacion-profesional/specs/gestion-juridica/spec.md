## MODIFIED Requirements

### Requirement: Gestionar procesos, admisión, asignaciones y consulta autorizada

#### Scenario: CU 38 - Crear Proceso Adicional

- **WHEN** Secretario o Administrador crea un proceso para un cliente ya registrado
- **THEN** el sistema crea un proceso pendiente ligado al cliente existente, sin modificar al cliente ni generar un turno automático
- **AND** el proceso queda sin profesional y sin honorarios; la petición CU 38 rechaza cualquier `profesional_id`
- **AND** la asignación posterior se realiza mediante CU 16, que valida profesional activo, rol Profesional y compatibilidad con el servicio, y fija los honorarios con el costo de referencia vigente
- **AND** si la asignación falla, el proceso permanece sin cambios
