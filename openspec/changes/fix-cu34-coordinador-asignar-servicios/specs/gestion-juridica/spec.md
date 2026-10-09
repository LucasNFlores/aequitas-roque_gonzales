## MODIFIED Requirements

### Requirement: Gestionar servicios y asignaciones de especialidad

Directivo y Administrador MUST poder gestionar servicios. Coordinador, Directivo y Administrador MUST poder vincular profesionales activos con servicios activos que pueden atender, desde una interfaz dedicada a CU34 que no requiere `listar_usuarios` ni expone la administración de CU25–27. Secretario y Profesional MUST NOT acceder a esa interfaz ni ejecutar la asignación mediante una petición directa. La autorización MUST validarse en el servidor y conservar la relación asignada o retirada.

#### Scenario: CU 34 - Asignar Especialidad o Servicio a Profesional

- **WHEN** Coordinador, Directivo o Administrador abre CU34
- **THEN** el sistema presenta profesionales activos y sus servicios activos para gestionar sus asignaciones
- **AND** guarda la relación de un servicio válido al profesional activo seleccionado o la retira cuando se desmarca
- **AND** CU34 funciona sin conceder acceso a CU25–27 ni a `listar_usuarios`

#### Scenario: Roles no autorizados no pueden gestionar CU34

- **WHEN** Secretario o Profesional intenta abrir la interfaz CU34 o enviar directamente una asignación
- **THEN** el servidor deniega la petición y no modifica ninguna relación
