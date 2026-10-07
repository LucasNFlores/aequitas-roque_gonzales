## ADDED Requirements

### Requirement: Gestionar compromisos externos

El sistema SHALL permitir a Secretario y Administrador registrar, reprogramar y cancelar turnos externos de profesionales. Profesional, Coordinador y Directivo SHALL ser rechazados en las acciones de escritura, aunque puedan consultar la agenda por CU8. El turno externo SHALL exigir detalle, bloquear toda la fecha del profesional mientras esté programado y conservarse con estado cancelado al anularse.

#### Scenario: Alta de compromiso externo

- **WHEN** Secretario o Administrador registran un compromiso con profesional activo, fecha válida y detalle
- **THEN** se guarda un turno con tipo externo, estado programado y disponibilidad diaria bloqueada

#### Scenario: Rechazo de alta por rol

- **WHEN** Profesional, Coordinador o Directivo intentan abrir o invocar el alta externa
- **THEN** la interfaz omite la acción y la ruta o acción directa responde 403

#### Scenario: Reprogramación

- **WHEN** Secretario o Administrador reprograman un compromiso a una fecha disponible
- **THEN** el sistema conserva el mismo turno, libera la fecha anterior y bloquea la nueva

#### Scenario: Conflicto al reprogramar

- **WHEN** la nueva fecha ya contiene otro turno activo del profesional
- **THEN** se rechaza la operación y el compromiso mantiene su fecha anterior

#### Scenario: Cancelación e historial

- **WHEN** Secretario o Administrador cancelan un compromiso programado
- **THEN** el registro se conserva con estado cancelado y deja de bloquear la fecha

#### Scenario: Notificaciones diferidas

- **WHEN** se registra, reprograma o cancela un compromiso dentro de HU-13
- **THEN** no se envía ni registra una notificación en HU-13; el evento queda para integrarse en el módulo independiente de fase final, después de validar ese módulo
