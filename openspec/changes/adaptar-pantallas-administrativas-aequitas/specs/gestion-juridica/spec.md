## ADDED Requirements

### Requirement: Presentar la administración activa con la identidad visual Aequitas

Las pantallas activas de usuarios y roles, servicios, asignaciones de servicios a profesionales, estados de proceso, categorías documentales e informe documental por categoría MUST usar la base visual Aequitas compartida para tipografía, colores, superficies, foco y estados. MUST conservar los datos, acciones, permisos por rol, rutas, filtros, validaciones y estados disponibles antes de la adaptación. La presentación MUST seguir siendo usable en escritorio y móvil, con labels visibles, errores asociados a sus campos, foco visible y controles táctiles accesibles.

#### Scenario: Usuario opera un módulo administrativo autorizado

- **WHEN** un usuario con permiso accede a una pantalla incluida y usa sus filtros, formularios, acciones o modales
- **THEN** el sistema conserva el comportamiento y alcance por rol existente y presenta controles con la base visual Aequitas
- **AND** los estados de carga, error, confirmación y vacío se distinguen con texto y color semántico
- **AND** el contenido y las acciones siguen disponibles en anchos de escritorio y móvil
