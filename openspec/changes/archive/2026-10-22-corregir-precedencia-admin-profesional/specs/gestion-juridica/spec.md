## ADDED Requirements

### Requirement: Aplicar precedencia de Administrador en roles múltiples

El sistema MUST autorizar al usuario que tenga el rol Administrador conforme a los 41 casos de uso de la matriz, aunque también tenga el rol Profesional. Las restricciones de asignación de procesos, agenda, documentos, legajos, historiales y reportes del Profesional MUST aplicarse sólo cuando el usuario no tenga el rol Administrador. La resolución MUST conservar los permisos de cada caso de uso y MUST NOT ampliar los permisos de ningún perfil sin Administrador.

#### Scenario: Administrador también tiene rol Profesional

- **WHEN** un usuario con los roles Administrador y Profesional consulta clientes, procesos, turnos, documentos, legajos, historiales o reportes autorizados a Administrador
- **THEN** el sistema muestra los registros autorizados sin restringirlos por la asignación del usuario Profesional
- **AND** las rutas directas de consulta y descarga aplican la misma autorización

#### Scenario: Profesional sin Administrador consulta registros asignados

- **WHEN** un Profesional sin rol Administrador consulta procesos, legajos, documentos, reportes o turnos
- **THEN** el sistema conserva el alcance por asignación y las capacidades permitidas por su rol
- **AND** deniega el acceso directo a registros fuera de ese alcance

#### Scenario: Administrador sin rol Profesional consulta registros

- **WHEN** un Administrador sin rol Profesional consulta cualquiera de las superficies autorizadas por la matriz
- **THEN** el sistema conserva su acceso de superadministrador

#### Scenario: Otro perfil múltiple no tiene Administrador

- **WHEN** un usuario tiene rol Profesional combinado con otro rol, pero no Administrador
- **THEN** el sistema no amplía sus permisos ni elimina el alcance profesional por asignación
