## MODIFIED Requirements

### Requirement: Gestionar reportes con autoría y alcance

El sistema MUST asociar cada reporte al proceso y al Profesional actualmente asignado como autor. Profesional asignado y Administrador MUST poder registrar reportes; Administrador MUST quedar identificado por separado cuando registra en nombre del Profesional. CU22/CU23 MUST permitir editar o dar de baja lógicamente únicamente al Profesional autor o Administrador. Las consultas operativas MUST excluir reportes dados de baja y conservar sus versiones. El Profesional MUST consultar solo sus propios reportes dentro de procesos que tiene asignados actualmente. Coordinador, Directivo y Administrador MUST consultar reportes e historial dentro del alcance de sus legajos. Secretario MUST NOT consultar reportes ni su historial.

#### Scenario: CU 21 - Registrar como Profesional asignado

- **WHEN** el Profesional asignado registra un reporte con contenido y fecha
- **THEN** el sistema asocia el reporte al proceso y al Profesional autor y registra al Profesional como actor

#### Scenario: CU 21 - Administrador registra para el Profesional asignado

- **WHEN** un Administrador registra un reporte para un proceso con Profesional asignado
- **THEN** el Profesional asignado queda como autor y el Administrador queda identificado como quien lo registró
- **AND** el Profesional asignado puede consultar, editar y dar de baja su reporte según CU22/CU23

#### Scenario: CU 21 - Rechazar registro sin profesional asignado

- **WHEN** un Administrador intenta registrar un reporte en un proceso sin Profesional asignado
- **THEN** el sistema rechaza el registro y no crea un reporte sin autor profesional

#### Scenario: CU 22 - Editar reporte

- **WHEN** el Profesional autor o Administrador edita un reporte
- **THEN** el sistema conserva la autoría y registra el historial del cambio

#### Scenario: CU 23 - Eliminar reporte

- **WHEN** el Profesional autor o Administrador elimina un reporte
- **THEN** el sistema aplica baja lógica, mantiene el historial y excluye el reporte de consultas operativas

#### Scenario: CU 24 - Consultar reportes de proceso

- **WHEN** Profesional, Coordinador, Directivo o Administrador consulta reportes del proceso
- **THEN** Profesional ve solo sus reportes dentro de procesos asignados actualmente y los demás roles acceden solo mediante legajos permitidos
- **AND** Secretario, usuarios sin alcance y solicitudes directas fuera de CU24 reciben acceso denegado
