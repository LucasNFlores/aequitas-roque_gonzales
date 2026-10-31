## ADDED Requirements

### Requirement: Preservar el contexto de retorno al detalle del proceso

Cuando una acción relacionada con un proceso se inicia desde su detalle en el legajo y termina correctamente, el sistema MUST conservar el cliente y el proceso de origen y MUST ofrecer el retorno al mismo detalle. La conservación del contexto MUST NOT ampliar permisos ni reemplazar la autorización de las rutas de destino.

#### Scenario: Agendar un turno desde el detalle del proceso

- **WHEN** un usuario autorizado inicia la creación de un turno desde el detalle de un proceso y guarda el turno correctamente
- **THEN** el sistema retorna al detalle de ese mismo proceso y cliente

#### Scenario: Editar los datos del cliente desde el detalle del proceso

- **WHEN** un usuario autorizado inicia la edición del cliente desde el detalle de uno de sus procesos y guarda los cambios correctamente
- **THEN** el sistema retorna al detalle del mismo proceso y cliente

#### Scenario: Volver desde el historial de un reporte

- **WHEN** un usuario consulta el historial de un reporte abierto desde el detalle de un proceso y activa la acción de retorno
- **THEN** el sistema retorna al detalle del proceso al que pertenece el reporte

#### Scenario: Salir de la gestión documental

- **WHEN** un usuario autorizado abre la gestión documental desde el detalle de un proceso y activa la acción de retorno
- **THEN** el sistema retorna al detalle del mismo proceso y cliente
