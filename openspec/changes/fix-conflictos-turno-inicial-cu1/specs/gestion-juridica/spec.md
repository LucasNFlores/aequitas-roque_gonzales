## MODIFIED Requirements

### Requirement: Gestionar clientes y el alta inicial

El sistema MUST permitir al Secretario y al Administrador registrar, modificar y dar de baja lógicamente clientes. El registro MUST crear un proceso inicial de Consultoría en estado pendiente y un turno inicial con el Coordinador. El alta MUST impedir conflictos de disponibilidad y mantenerse atómica. La baja MUST conservar los datos históricos y MUST rechazarse si el cliente tiene procesos activos.

#### Scenario: CU 1 - Registrar Cliente

- **WHEN** Secretario o Administrador registra un cliente con datos válidos y existe la configuración inicial requerida y hay disponibilidad para el Coordinador
- **THEN** el sistema crea el cliente, un proceso de Consultoría pendiente y un turno inicial con un Coordinador en una operación consistente
- **AND** crea el registro interno mínimo dirigido al Coordinador

#### Scenario: CU 1 - Conflicto en el turno inicial

- **WHEN** Secretario o Administrador registra un cliente y el Coordinador ya tiene un turno activo en esa fecha y hora, o un compromiso externo activo en esa fecha
- **THEN** el sistema informa el conflicto en el campo de fecha y hora inicial y no persiste cliente, proceso, turno ni registro interno

#### Scenario: CU 1 - Horario liberado por cancelación

- **WHEN** Secretario o Administrador registra un cliente en la fecha y hora de un turno cancelado del Coordinador, sin otro conflicto activo
- **THEN** el sistema completa el alta inicial y conserva el turno cancelado como historial
