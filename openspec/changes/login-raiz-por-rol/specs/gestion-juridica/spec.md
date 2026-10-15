## ADDED Requirements

### Requirement: Usar la raíz como acceso contextual al sistema

La ruta `/` MUST enviar a las personas no autenticadas al inicio de sesión. Si la persona ya está autenticada, `/` MUST enviarla al dashboard operativo existente, que MUST mostrar únicamente los datos y destinos permitidos por sus permisos efectivos.

#### Scenario: Una persona sin sesión abre la raíz

- **WHEN** una persona no autenticada solicita `/`
- **THEN** el sistema la redirige al inicio de sesión
- **AND** no muestra la página pública de bienvenida

#### Scenario: Una persona autenticada abre la raíz

- **WHEN** un empleado autenticado solicita `/`
- **THEN** el sistema lo redirige a `/dashboard`
- **AND** el dashboard conserva los datos y destinos autorizados para sus permisos y roles
