## ADDED Requirements

### Requirement: Consultar el historial auditable de estados de procesos

El sistema MUST registrar en cada transición el proceso, estado anterior, estado nuevo, fecha, usuario interviniente y motivo. Secretario, Profesional, Coordinador, Directivo y Administrador MUST poder consultar el historial conforme a CU 30; el Profesional MUST quedar limitado a procesos asignados.

#### Scenario: Usuario autorizado consulta transiciones

- **WHEN** Secretario, Profesional, Coordinador, Directivo o Administrador consulta el historial de un proceso autorizado
- **THEN** el sistema muestra las transiciones con estado anterior y nuevo, fecha, usuario y motivo

#### Scenario: Profesional solicita historial de un proceso ajeno

- **WHEN** un Profesional consulta el historial de un proceso no asignado, incluso por URL directa
- **THEN** el sistema deniega el acceso y no entrega el historial

#### Scenario: Proceso recibe su estado inicial

- **WHEN** se crea un proceso con un estado inicial válido
- **THEN** el sistema registra la entrada inicial con estado anterior nulo y el estado inicial asociado

### Requirement: Administrar el catálogo de estados de proceso

Coordinador y Administrador MUST poder crear, editar, ordenar, activar y desactivar estados según CU 36. Un estado inactivo MUST NOT usarse en nuevas transiciones. El sistema MUST rechazar la desactivación de un estado utilizado por procesos no dados de baja y MUST conservar los estados inactivos para lectura histórica.

#### Scenario: Coordinador o Administrador mantiene estados

- **WHEN** Coordinador o Administrador crea, edita, ordena, activa o desactiva un estado sin dejar procesos activos sin transición válida
- **THEN** el sistema guarda la configuración del catálogo y su orden

#### Scenario: Estado utilizado no puede desactivarse

- **WHEN** Coordinador o Administrador intenta desactivar un estado usado por procesos no dados de baja
- **THEN** el sistema rechaza la operación y conserva el estado disponible para la lectura del historial

#### Scenario: Estado inactivo se intenta usar en una transición

- **WHEN** un usuario autorizado intenta cambiar un proceso a un estado inactivo
- **THEN** el sistema rechaza la transición y conserva el estado actual
