# Delta de especificación: gestión jurídica

## ADDED Requirements

### Requirement: Generar informe PDF de documentos por categoría

El sistema MUST permitir al Coordinador y al Administrador generar un informe PDF que resuma documentos por categoría para un período. El informe MUST permitir filtrar por período, estado de categoría y categoría, y MUST presentar cantidades de documentos y la última fecha de carga por categoría. El informe MUST NOT incluir ni adjuntar los archivos originales. El Directivo MUST NOT obtener acceso a este informe por el solo hecho de poder consultar legajos. El Administrador MUST conservar acceso como superadministrador.

#### Scenario: CU 39 - Coordinador genera informe PDF

- **WHEN** un Coordinador solicita el informe con filtros válidos
- **THEN** el sistema genera un PDF agregado que respeta el período, estado y categoría seleccionados
- **AND** el resultado presenta las cantidades y la última fecha de carga sin exponer los archivos individuales

#### Scenario: CU 39 - Directivo intenta acceder al informe

- **WHEN** un Directivo solicita el informe desde la interfaz o una ruta directa
- **THEN** el sistema deniega el acceso y no revela sus resultados

### Requirement: Consultar dashboard propio del Directivo

El sistema MUST proporcionar al Directivo un dashboard propio. El conjunto de indicadores MUST definirse y aprobarse antes de cerrar la implementación. Cada indicador MUST respetar el alcance de datos autorizado para el Directivo y MUST NOT conceder acceso implícito a documentos, archivos, legajos o reportes. El Administrador MUST poder acceder por su condición de superadministrador.

#### Scenario: CU 40 - Directivo consulta su dashboard

- **WHEN** un Directivo consulta el dashboard
- **THEN** el sistema presenta únicamente los indicadores acordados para este rol y sus datos autorizados
- **AND** la pantalla no concede acceso adicional a documentación ni a consultas individuales de legajos

#### Scenario: CU 40 - Administrador consulta el dashboard

- **WHEN** un Administrador consulta el dashboard del Directivo
- **THEN** el sistema lo autoriza conforme a su condición de superadministrador

#### Scenario: CU 40 - Rol sin autorización intenta acceder

- **WHEN** un Secretario, Profesional o Coordinador solicita el dashboard por interfaz o ruta directa
- **THEN** el sistema deniega el acceso
