# Delta de especificación: gestión jurídica

## ADDED Requirements

### Requirement: Generar informe PDF de documentos por categoría

El sistema MUST permitir al Coordinador y al Administrador generar un informe PDF que resuma documentos por categoría para un período. El informe MUST permitir filtrar por período, estado de categoría y categoría, y MUST presentar cantidades de documentos y la última fecha de carga por categoría. Si no se ingresa un período, MUST usar hoy y los 29 días anteriores, inclusive. Si se ingresan fechas, MUST exigir ambas, validar el formato y existencia de cada fecha, rechazar fechas futuras, exigir `desde <= hasta` y limitar el rango a 366 días calendario inclusive. El informe MUST NOT incluir ni adjuntar los archivos originales. El Directivo MUST NOT obtener acceso a este informe por el solo hecho de poder consultar legajos. El Administrador MUST conservar acceso como superadministrador.

#### Scenario: CU 39 - Coordinador genera informe PDF

- **WHEN** un Coordinador solicita el informe con filtros válidos
- **THEN** el sistema genera un PDF agregado que respeta el período, estado y categoría seleccionados
- **AND** el resultado presenta las cantidades y la última fecha de carga sin exponer los archivos individuales

#### Scenario: CU 39 - Directivo intenta acceder al informe

- **WHEN** un Directivo solicita el informe desde la interfaz o una ruta directa
- **THEN** el sistema deniega el acceso y no revela sus resultados

#### Scenario: CU 39 - El período predeterminado cubre los últimos 30 días

- **WHEN** un Coordinador abre el informe sin indicar fechas
- **THEN** el sistema consulta desde hoy menos 29 días hasta hoy, inclusive

#### Scenario: CU 39 - El sistema rechaza un período inválido

- **WHEN** se envía una sola fecha, una fecha imposible o futura, un rango invertido o un período mayor a 366 días
- **THEN** el sistema conserva los filtros y muestra el error sin generar el PDF

### Requirement: Consultar dashboard propio del Directivo

El sistema MUST proporcionar al Directivo una variante estratégica del dashboard común HU-22, y el Administrador MUST poder consultarla por su condición de superadministrador. El dashboard MUST presentar procesos activos vigentes, clientes vigentes, reportes de los últimos 30 días y cuentas vigentes según las definiciones aprobadas en el diseño de CU 40. El gráfico horizontal de procesos activos por servicio MUST aplicar `Proceso::visibleTo($user)` antes de agrupar y MUST excluir procesos dados de baja, finalizados o rechazados. Los conteos MUST NOT mostrar filas personales ni conceder acceso implícito a agenda, documentos, archivos o colas operativas.

#### Scenario: CU 40 - Directivo consulta su dashboard

- **WHEN** un Directivo consulta el dashboard
- **THEN** el sistema presenta las métricas aprobadas y sus datos autorizados
- **AND** el gráfico por servicio incluye una lista accesible equivalente y un estado vacío cuando no hay resultados
- **AND** la pantalla no concede acceso adicional a agenda, documentación ni a consultas individuales de legajos

#### Scenario: CU 40 - Administrador consulta el dashboard

- **WHEN** un Administrador consulta el dashboard del Directivo
- **THEN** el sistema lo autoriza conforme a su condición de superadministrador

#### Scenario: CU 40 - Rol sin autorización intenta acceder

- **WHEN** un Secretario, Profesional o Coordinador solicita el dashboard por interfaz o ruta directa
- **THEN** el rol conserva su dashboard HU-22 autorizado, sin recibir las métricas ni el gráfico propios de CU 40
