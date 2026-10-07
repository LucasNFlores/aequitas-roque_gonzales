## ADDED Requirements

### Requirement: Consultar la agenda con filtros acotados y alcance por rol

La consulta de agenda CU 8 MUST permanecer de solo lectura. Secretario, Coordinador y Administrador MUST poder elegir el profesional y período autorizados. El Profesional MUST quedar limitado a su propia agenda aun cuando altere filtros o estado Livewire. Directivo MUST recibir denegación. Los rangos MUST ser inclusivos y no superar 90 días; la consulta MUST mostrar turnos cancelados solo como historial y MUST NOT inferir franjas libres ni duraciones no definidas.

#### Scenario: Un rol autorizado consulta la agenda seleccionada

- **WHEN** Secretario, Coordinador o Administrador consulta un profesional y un rango de hasta 90 días
- **THEN** el sistema muestra los turnos del rango inclusivo, incluidos los cancelados como historial

#### Scenario: Profesional manipula el filtro

- **WHEN** un Profesional consulta la agenda o modifica sus filtros y propiedades de Livewire
- **THEN** el servidor conserva el alcance en su propia agenda y no expone turnos de otros profesionales

#### Scenario: Directivo intenta consultar la agenda

- **WHEN** un Directivo solicita CU 8 desde la interfaz o una petición directa
- **THEN** el sistema deniega el acceso sin exponer información de la agenda

#### Scenario: Período inválido o demasiado amplio

- **WHEN** el rango informado tiene fechas inválidas o excede 90 días
- **THEN** el sistema rechaza o valida el filtro y no ejecuta una consulta fuera del límite

#### Scenario: Agenda muestra disponibilidad e historial sin inventar intervalos

- **WHEN** se representa una agenda con turnos externos o cancelados
- **THEN** el externo programado bloquea su jornada, el cancelado no ocupa disponibilidad y la vista no crea intervalos libres ni acciones de escritura
