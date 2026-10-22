# Delta de especificación: gestión jurídica

## MODIFIED Requirements

### Requirement: Generar informe PDF de documentos por categoría (CU 39)

El sistema MUST permitir al Coordinador, al Directivo y al Administrador generar un informe PDF que resuma documentos por categoría para un período. El informe MUST permitir filtrar por período, estado de categoría y categoría, y MUST presentar cantidades de documentos y la última fecha de carga por categoría. Si no se ingresa un período, MUST usar hoy y los 29 días anteriores, inclusive. Si se ingresan fechas, MUST exigir ambas, validar el formato y existencia de cada fecha, rechazar fechas futuras, exigir `desde <= hasta` y limitar el rango a 366 días calendario inclusive. El informe MUST NOT incluir ni adjuntar los archivos originales. El permiso para consultar el informe MUST NOT conceder permisos para gestionar categorías, visualizar documentación individual ni descargar archivos de legajos. El Administrador MUST conservar acceso como superadministrador.

#### Scenario: CU 39 - Directivo genera el informe PDF

- **WHEN** un Directivo solicita el informe con filtros válidos desde la interfaz o una ruta directa
- **THEN** el sistema genera el PDF agregado con los filtros aplicados
- **AND** el Directivo no puede gestionar categorías ni acceder a archivos individuales por este permiso

#### Scenario: CU 39 - Roles sin permiso intentan acceder

- **WHEN** un Secretario o Profesional solicita el informe desde la interfaz o una ruta directa
- **THEN** el sistema deniega el acceso y no revela sus resultados
