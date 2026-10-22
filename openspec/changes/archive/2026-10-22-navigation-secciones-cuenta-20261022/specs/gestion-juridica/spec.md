## MODIFIED Requirements

### Requirement: Presentar navegación acorde a rol y permisos

Las navegaciones autenticadas MUST ofrecer los destinos operativos implementados que correspondan a los permisos del usuario según la matriz Aequitas V2. Cada enlace operativo MUST ocultarse cuando el usuario no tenga el permiso exigido por la ruta. La navegación lateral MUST agrupar los destinos en secciones funcionales y MUST NOT mostrar encabezados de sección sin enlaces autorizados para el usuario. Las variantes de navegación MUST conservar el mismo conjunto de destinos autorizados. Los accesos comunes de inicio MUST seguir disponibles. Las acciones de tema, perfil y cierre de sesión MUST estar disponibles desde un desplegable asociado a la sesión activa. La guía técnica MUST NOT estar disponible como ruta ni como destino de navegación. Ocultar un enlace MUST NOT reemplazar la autorización del servidor para solicitudes directas.

#### Scenario: Empleado navega funciones autorizadas por secciones

- **WHEN** Secretario, Profesional, Coordinador, Directivo o Administrador inicia sesión
- **THEN** la navegación presenta los destinos implementados que corresponden a sus permisos en la matriz, agrupados por función
- **AND** no se muestran encabezados de secciones vacías
- **AND** todas las variantes de navegación muestran los mismos destinos operativos autorizados

#### Scenario: Empleado navega funciones autorizadas

- **WHEN** Secretario, Profesional, Coordinador, Directivo o Administrador inicia sesión
- **THEN** la navegación presenta los destinos implementados que corresponden a sus permisos en la matriz
- **AND** todas las variantes de navegación muestran los mismos destinos operativos autorizados

#### Scenario: Empleado abre las acciones de su sesión

- **WHEN** un empleado autenticado activa la tarjeta que identifica su sesión
- **THEN** el desplegable muestra el control de tema, el acceso a su perfil y la acción de cerrar sesión
- **AND** cuando el desplegable está cerrado, esas acciones no ocupan espacio visible en la navegación

#### Scenario: Empleado no autorizado solicita una función

- **WHEN** un empleado sigue una URL directa a una función que su rol no tiene autorizada
- **THEN** el servidor deniega la solicitud aunque el usuario haya construido la URL sin usar la navegación
