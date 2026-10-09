## Requisitos añadidos

### Requirement: Auditar la baja lógica de documentos

La baja lógica MUST conservar el actor y la fecha de la operación, la referencia del documento, las versiones y todos los archivos físicos. MUST NOT borrar físicamente ningún archivo ni permitir CU11 a roles distintos de Secretario y Administrador.

#### Scenario: CU 11 - Baja lógica auditada

- **WHEN** Secretario o Administrador da de baja un documento
- **THEN** se conservan `deleted_at`, el usuario que ejecutó la acción, la referencia, el historial y los archivos físicos
- **AND** el documento deja de aparecer en la consulta ordinaria

### Requirement: Autorizar las rutas de documentos y versiones históricas

El acceso a documentos y versiones MUST comprobar permisos y alcance del proceso en servidor para cada solicitud, incluso mediante URL directa. Ver y descargar MUST ser operaciones separadas.

#### Scenario: Archivo ausente o recurso fuera de alcance

- **WHEN** se solicita un archivo físico ausente, un documento de otro proceso o una versión de otro documento
- **THEN** el servidor responde 404 sin revelar ni entregar el archivo

#### Scenario: Roles de lectura y descarga

- **WHEN** un usuario solicita ver o descargar un documento o su versión histórica
- **THEN** CU12 permite Secretario, Profesional asignado, Coordinador y Administrador
- **AND** CU19 permite Profesional asignado, Coordinador y Administrador
- **AND** Directivo no puede ver ni descargar; Secretario tampoco puede descargar
