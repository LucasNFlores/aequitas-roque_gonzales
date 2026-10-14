### Requirement: Completar búsqueda y filtro de clientes (CU 28)

El sistema MUST permitir a Secretario, Coordinador, Directivo y Administrador buscar clientes por nombre, apellido o DNI; filtrar por estado de admisión de sus procesos; combinar búsqueda y estado; y paginar con orden determinista conservando los parámetros aplicados. Profesional MUST NOT acceder al listado de CU 28 por URL directa. Cada cliente MUST aparecer una sola vez por resultado. Cuando existen procesos con estados distintos, el filtro MUST incluir al cliente si cualquiera coincide con el estado seleccionado; puede aparecer en varios filtros de estado y solo una vez en cada resultado.

#### Scenario: Buscar cliente

- **WHEN** un rol autorizado busca por nombre, apellido o DNI
- **THEN** el listado devuelve coincidencias y conserva el término al paginar

#### Scenario: Filtrar y combinar por estado

- **WHEN** un rol autorizado selecciona un estado, con o sin término de búsqueda
- **THEN** el listado muestra los clientes coincidentes y conserva los criterios al paginar

#### Scenario: Resultado vacío y limpieza

- **WHEN** no hay clientes que coincidan con los criterios
- **THEN** la interfaz comunica el resultado vacío y permite limpiar filtros

#### Scenario: Profesional intenta acceder al listado

- **WHEN** Profesional solicita el listado, incluso directamente por URL
- **THEN** el sistema deniega CU 28 y conserva únicamente las rutas autorizadas por CU 18
