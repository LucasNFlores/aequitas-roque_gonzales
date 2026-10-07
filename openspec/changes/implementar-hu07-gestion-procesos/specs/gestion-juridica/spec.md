## ADDED Requirements

### Requirement: Admitir o rechazar procesos pendientes según CU 15 y CU 15.1

Coordinador y Administrador MUST poder admitir o rechazar procesos pendientes. El rechazo MUST requerir una causal no vacía y MUST conservar la causal en el proceso y en el historial. El cambio general de estado MUST NOT omitir estas reglas específicas.

#### Scenario: Admisión de proceso pendiente

- **WHEN** Coordinador o Administrador admite un proceso pendiente
- **THEN** el sistema lo transiciona al estado activo admitido y registra la transición

#### Scenario: Rechazo con causal

- **WHEN** Coordinador o Administrador rechaza un proceso pendiente con una causal no vacía
- **THEN** el sistema guarda la causal en el proceso y en el historial y transiciona a rechazado

#### Scenario: Rechazo sin causal

- **WHEN** Coordinador o Administrador intenta rechazar un proceso sin informar una causal
- **THEN** el sistema rechaza la operación y conserva el estado actual

#### Scenario: Rol no autorizado intenta admitir o rechazar

- **WHEN** Secretario, Profesional o Directivo intenta admitir o rechazar un proceso
- **THEN** el sistema deniega la operación

### Requirement: Asignar y reasignar profesionales compatibles según CU 16 y CU 17

Secretario, Coordinador y Administrador MUST poder asignar y reasignar profesionales activos con rol Profesional y compatibles con el servicio del proceso. Al guardar la asignación, el sistema MUST tomar los honorarios del costo de referencia vigente del servicio y MUST NOT recalcular registros históricos.

#### Scenario: Asignación o reasignación válida

- **WHEN** Secretario, Coordinador o Administrador asigna o reasigna un Profesional activo compatible
- **THEN** el sistema persiste el profesional elegido y los honorarios vigentes del servicio en una operación consistente

#### Scenario: Profesional incompatible o inactivo

- **WHEN** se intenta asignar un usuario inactivo, no Profesional o incompatible con el servicio
- **THEN** el sistema rechaza la operación y conserva la asignación y honorarios anteriores

#### Scenario: Rol no autorizado asigna un profesional

- **WHEN** Profesional o Directivo intenta asignar o reasignar un proceso
- **THEN** el sistema deniega la operación

### Requirement: Filtrar la bandeja de procesos sin ampliar el alcance del Profesional

Secretario, Profesional, Coordinador, Directivo y Administrador MUST poder listar y filtrar procesos según CU 29. El Profesional MUST consultar solo procesos asignados, y el servidor MUST volver a aplicar ese alcance antes de buscar, filtrar o paginar.

#### Scenario: Roles autorizados consultan la bandeja

- **WHEN** cualquiera de los cinco roles consulta o filtra la bandeja
- **THEN** el sistema permite buscar y paginar por estado, cliente y profesional asignado
- **AND** conserva los filtros adicionales aprobados por servicio, coordinador y rango de fecha de inicio

#### Scenario: Profesional intenta ampliar el alcance

- **WHEN** el Profesional manipula un filtro, parámetro o URL para consultar procesos ajenos
- **THEN** el servidor devuelve solo los procesos asignados a ese Profesional

### Requirement: Actualizar otros estados de un proceso autorizado según CU 20

El Profesional asignado, Coordinador y Administrador MUST poder actualizar estados permitidos de un proceso autorizado. La operación MUST registrar la transición. La admisión y el rechazo MUST realizarse mediante sus flujos específicos.

#### Scenario: Usuario autorizado actualiza otro estado

- **WHEN** el Profesional asignado, Coordinador o Administrador selecciona un estado activo distinto de admitido o rechazado
- **THEN** el sistema guarda el cambio y su historial

#### Scenario: Usuario intenta omitir admisión o rechazo

- **WHEN** un usuario intenta aplicar admitido o rechazado mediante el cambio general de estado
- **THEN** el sistema rechaza la operación y exige el flujo CU 15 o CU 15.1 correspondiente
