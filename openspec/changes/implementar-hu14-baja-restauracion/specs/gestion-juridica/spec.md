## ADDED Requirements

### Requirement: Administrar el ciclo de vida de categorías documentales bajo CU 37

Coordinador y Administrador MUST poder crear, editar, activar, desactivar y dar de baja lógicamente categorías documentales según CU 37. Las categorías inactivas o dadas de baja MUST NOT ofrecerse para cargas nuevas. El sistema MUST conservar asociaciones, metadatos y archivos históricos, denegar la eliminación física y no desactivar categorías cuando deje documentos activos sin una transición válida.

#### Scenario: Crear o editar categoría

- **WHEN** Coordinador o Administrador crea o edita una categoría con nombre válido
- **THEN** el sistema conserva el cambio y lo deja disponible para nuevas cargas solo si está activa y no tiene baja lógica

#### Scenario: Desactivar categoría con documentos activos

- **WHEN** Coordinador o Administrador desactiva una categoría y sus documentos activos quedarían sin una transición válida
- **THEN** el sistema rechaza la operación y conserva las asociaciones documentales

#### Scenario: Dar de baja una categoría

- **WHEN** Coordinador o Administrador da de baja lógicamente una categoría
- **THEN** el sistema conserva la categoría, la relación documental, tipo_documento y los archivos físicos para consulta histórica

#### Scenario: Restaurar una categoría

- **WHEN** Coordinador o Administrador restaura una categoría dada de baja
- **THEN** el sistema limpia la baja lógica y conserva su valor activo o inactivo previo; solo una categoría activa y restaurada se ofrece para nuevas cargas

#### Scenario: Documento histórico sin categoría

- **WHEN** se consulta un documento histórico sin asociación inequívoca a una categoría
- **THEN** el sistema conserva y muestra su tipo_documento y archivo sin asignar una categoría inventada

#### Scenario: Solicitar borrado físico

- **WHEN** cualquier usuario intenta borrar físicamente una categoría, documento o archivo
- **THEN** el sistema deniega la acción
