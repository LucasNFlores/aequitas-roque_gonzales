## ADDED Requirements

### Requirement: Crear procesos adicionales para clientes existentes

Secretario y Administrador MUST poder crear un proceso adicional para un cliente existente mediante CU 38. La operación MUST crear el proceso pendiente vinculado a ese cliente, MUST NOT crear o modificar el cliente y MUST NOT crear un turno automáticamente. Profesional, Coordinador y Directivo MUST ser rechazados.

#### Scenario: Secretario crea un proceso adicional

- **WHEN** Secretario inicia la operación desde un cliente existente y envía datos válidos
- **THEN** el sistema crea un proceso pendiente asociado al cliente seleccionado y continúa por la admisión vigente
- **AND** no crea otro cliente ni un turno inicial

#### Scenario: Administrador crea un proceso adicional

- **WHEN** Administrador realiza CU 38 con datos válidos
- **THEN** el sistema crea el proceso pendiente del cliente existente sin duplicar datos del cliente ni generar un turno

#### Scenario: Rol sin CU 38 intenta la operación

- **WHEN** Profesional, Coordinador o Directivo intenta crear un proceso adicional
- **THEN** el sistema deniega la operación y no persiste datos

#### Scenario: Referencia requerida no está disponible

- **WHEN** el cliente, servicio, Coordinador o estado inicial no existe o no está habilitado para uso operativo
- **THEN** el sistema rechaza la creación sin dejar un proceso parcial

#### Scenario: Se reenvía el mismo formulario

- **WHEN** el formulario se reenvía con el mismo token de envío
- **THEN** el sistema devuelve el proceso original y no crea un duplicado accidental
