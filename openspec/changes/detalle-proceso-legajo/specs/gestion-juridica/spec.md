## ADDED Requirements

### Requirement: Consultar procesos resumidos desde el legajo

El legajo MUST presentar cada proceso autorizado como un resumen y MUST permitir abrir una vista de detalle por proceso. La vista de detalle MUST incluir los datos del proceso y del cliente, turnos, documentos y versiones, reportes, comprobantes e historial, respetando las autorizaciones existentes para cada relación y acción. Cada reporte MUST identificar su autor, quién lo registró y su fecha.

#### Scenario: Usuario autorizado abre el detalle desde un resumen

- **WHEN** un usuario con CU18 selecciona un proceso visible en el legajo de un cliente
- **THEN** el sistema abre el detalle de ese proceso y muestra sus relaciones autorizadas

#### Scenario: Consultar autoría de un reporte

- **WHEN** un usuario autorizado abre el detalle de un proceso con reportes
- **THEN** cada reporte identifica al profesional autor, al usuario que lo registró y su fecha
- **AND** los reportes históricos sin usuario registrante muestran una indicación de registro anterior

#### Scenario: Profesional solicita un proceso no asignado

- **WHEN** un Profesional solicita por URL el detalle de un proceso del cliente que no tiene asignado
- **THEN** el sistema responde como recurso no encontrado y no carga sus relaciones

#### Scenario: Proceso pertenece a otro cliente

- **WHEN** un usuario con CU18 solicita un proceso anidado bajo un cliente diferente
- **THEN** el sistema deniega la resolución del proceso y no revela sus datos

#### Scenario: Un usuario sin CU18 solicita el detalle

- **WHEN** un usuario sin permiso de consultar legajos abre un detalle por URL directa
- **THEN** el sistema deniega el acceso

### Requirement: Mostrar acciones del detalle según permisos

La vista de detalle MUST mostrar cada acción solo cuando la policy o el permiso existente correspondiente la autoriza. Cada operación de edición MUST volver a comprobar la autorización en el servidor. CU18 por sí solo MUST NOT habilitar cambios de datos, estados o relaciones.

#### Scenario: Editar datos del cliente

- **WHEN** un usuario consulta un proceso y tiene permiso de actualizar su cliente
- **THEN** ve la acción para editar datos del cliente y la ruta de actualización conserva la autorización de `ClientePolicy`

#### Scenario: Actualizar estado del proceso

- **WHEN** un usuario consulta un proceso y la policy autoriza actualizar su estado dentro del alcance asignado
- **THEN** ve la acción correspondiente y el servidor valida el nuevo estado y su autorización al guardar

#### Scenario: Usuario de solo consulta abre una acción de edición

- **WHEN** un usuario con CU18 pero sin autorización de edición solicita una operación directa
- **THEN** el sistema rechaza la operación y no modifica el proceso ni sus relaciones
