## ADDED Requirements

### Requirement: Registrar cliente con proceso inicial y turno del Coordinador según CU 1

Secretario y Administrador MUST poder registrar un cliente y crear en una operación consistente un proceso inicial de Consultoría pendiente y un turno inicial con un Coordinador activo. El sistema MUST validar la configuración requerida y MUST NOT conservar registros parciales ante un error. El registro interno dirigido al Coordinador MUST limitarse a este flujo.

#### Scenario: Alta inicial completa

- **WHEN** Secretario o Administrador registra datos únicos válidos, fecha y hora iniciales y existe Coordinador, servicio y estado pendiente activos
- **THEN** el sistema crea un cliente, un proceso inicial de Consultoría pendiente, un turno inicial vinculado al Coordinador y el registro interno mínimo, de forma consistente

#### Scenario: Rol no autorizado intenta registrar un cliente

- **WHEN** Profesional, Coordinador o Directivo intenta ejecutar CU 1
- **THEN** el sistema deniega la operación sin crear datos

#### Scenario: Datos duplicados o configuración ausente

- **WHEN** el DNI o correo no es único, o falta el Coordinador, el servicio inicial o el estado pendiente activo
- **THEN** el sistema rechaza el alta y no deja cliente, proceso, turno ni registro interno parcial

#### Scenario: Falla una escritura durante el alta

- **WHEN** ocurre un error luego de comenzar la creación de los registros relacionados
- **THEN** el sistema revierte la operación completa

#### Scenario: Se registra el aviso interno mínimo

- **WHEN** se crea el registro interno dirigido al Coordinador como parte del alta
- **THEN** el registro queda asociado al cliente y no envía correo ni WhatsApp ni habilita una bandeja general
