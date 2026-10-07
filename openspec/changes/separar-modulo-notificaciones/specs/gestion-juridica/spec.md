## ADDED Requirements

### Requirement: Entregar notificaciones como módulo independiente al final

El sistema MUST mantener las capacidades de notificaciones dentro del alcance final y MUST entregarlas como un módulo independiente después de completar y aceptar el resto del núcleo funcional. Los flujos de clientes, procesos y turnos MUST poder operar sin depender de un proveedor externo de notificaciones. La integración de sus eventos con el módulo MUST ocurrir después de validar el módulo de forma aislada.

#### Scenario: El núcleo funciona sin proveedor externo

- **WHEN** un usuario ejecuta una operación de clientes, procesos o turnos antes de habilitar el módulo final
- **THEN** la operación principal se completa sin requerir credenciales ni respuesta de Brevo u otro proveedor

#### Scenario: Inicio del módulo final

- **WHEN** los demás módulos funcionales ya están terminados y aceptados y comienza la fase de Notificaciones
- **THEN** se revisan los costos y condiciones vigentes de Brevo antes de confirmar el proveedor o combinación de proveedores para correo y WhatsApp

#### Scenario: Integración posterior a la validación aislada

- **WHEN** el módulo de Notificaciones ya fue validado de forma independiente
- **THEN** los productores de eventos de clientes, procesos, turnos y otros módulos se conectan mediante contratos explícitos

#### Scenario: Registro interno existente durante el alta

- **WHEN** se planifica la integración del evento de alta de cliente
- **THEN** el registro interno mínimo existente se revisa e incorpora al contrato del módulo, sin considerarlo una entrega completa de CU-35 o HU-18–HU-20
