# Cambios de especificación: Gestión jurídica

## Requisito: Poblar el entorno con datos ficticios de demostración

El seeding estándar MUST conservar las cinco cuentas internas y sus roles vigentes (Secretario, Profesional, Coordinador, Directivo y Administrador), sin crear el rol Supervisor. MUST generar varios profesionales asociados a servicios, suficientes clientes y procesos distribuidos entre los estados existentes, con historial de estados fechado dentro de los últimos seis meses.

El conjunto de demostración MUST incluir turnos futuros programados, cancelados y compromisos externos sin conflictos activos; reportes recientes e históricos; y comprobantes y documentos vinculados a clientes y procesos. Los archivos PDF requeridos por las descargas MUST existir en almacenamiento local.

Las ejecuciones repetidas MUST conservar las cantidades de registros propios del demo, MUST respetar las relaciones y las bajas lógicas, y MUST NOT actualizar ni eliminar datos ajenos a esos fixtures.

### Escenario: La base de datos se siembra más de una vez

- **WHEN** se ejecuta el seeding estándar sobre una base ya poblada
- **THEN** las cuentas y roles existentes se conservan y no se agrega Supervisor
- **AND** los profesionales, servicios, clientes, procesos, historiales, agenda, reportes, comprobantes y documentos del demo no se duplican
- **AND** las relaciones permanecen válidas y cada ruta PDF de demo existe

### Escenario: El demo muestra actividad de agenda sin conflictos

- **WHEN** se cargan los turnos de demostración
- **THEN** hay turnos futuros programados, turnos cancelados y compromisos externos
- **AND** ningún turno programado coincide con otro turno activo del profesional y cada compromiso externo ocupa un día sin turnos activos
