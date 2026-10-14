## MODIFIED Requirements

### Requirement: Gestionar reportes con autoría, historial y alcance

El sistema MUST asociar cada reporte al proceso y conservar la autoría original. Profesional autor y Administrador MUST poder editarlo. Cada edición que cambie contenido o fecha MUST guardar los valores anteriores, el usuario que realizó la edición y la fecha-hora del cambio antes de actualizar el reporte, en una operación atómica. Una solicitud que no cambie esos valores MUST NOT crear una versión. El sistema MUST conservar varias versiones y mantenerlas tras la baja lógica CU23. El Profesional MUST consultar únicamente sus propios reportes y solo dentro de procesos que tiene asignados. Coordinador, Directivo y Administrador MUST poder consultar reportes autorizados conforme a CU24; Secretario MUST NOT consultar reportes ni su historial.

#### Scenario: CU 22 - Editar reporte y conservar versión anterior

- **WHEN** el Profesional autor o Administrador edita contenido o fecha mediante una petición autorizada
- **THEN** el sistema conserva en una versión los valores anteriores, la identidad del actor y la fecha-hora de la edición
- **AND** actualiza el reporte en la misma operación

#### Scenario: CU 22 - No crear versión sin cambios

- **WHEN** el Profesional autor o Administrador guarda los mismos valores de contenido y fecha
- **THEN** el sistema no crea una versión histórica

#### Scenario: CU 22 - Conservar varias versiones y la baja lógica

- **WHEN** un reporte se edita varias veces y luego se da de baja lógicamente por CU23
- **THEN** el sistema conserva todas las versiones anteriores con actor y fecha-hora

#### Scenario: CU 24 - Consultar historial de reportes

- **WHEN** Profesional, Coordinador, Directivo o Administrador solicita el historial de un reporte por una ruta directa
- **THEN** el sistema verifica CU24 y el alcance del reporte/proceso antes de mostrar sus versiones
- **AND** Profesional solo accede a sus reportes dentro de procesos asignados
- **AND** Secretario y usuarios sin alcance reciben acceso denegado
