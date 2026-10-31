## ADDED Requirements

### Requirement: Tema claro y oscuro en el área autenticada

El área autenticada MUST ofrecer un botón accesible que alterne entre Claro y Oscuro y conservar la elección en el navegador. Cuando no haya una elección guardada, MUST iniciar en Claro. El tema MUST aplicarse antes del primer render visible. Los tokens semánticos compartidos y las pantallas de dashboard, perfil, clientes, turnos, comprobantes, procesos, legajos, documentos, reportes, usuarios, servicios, estados y categorías MUST mantener texto, controles y estados legibles en ambos temas. Los gráficos canvas MUST reflejar el tema activo. El cambio visual MUST NOT modificar permisos, rutas, consultas ni lógica de negocio y MUST NOT rediseñar el login.

#### Scenario: Persona alterna y recarga el tema

- **WHEN** una persona autenticada activa el botón para alternar de tema y vuelve a cargar una pantalla
- **THEN** la preferencia guardada se aplica antes de mostrar el área autenticada
- **AND** los controles, formularios, tablas, badges, diálogos y gráficos mantienen contraste legible

#### Scenario: Una persona usa módulos de distintos roles

- **WHEN** consulta pantallas de los roles autorizados en escritorio o en un viewport móvil
- **THEN** la apariencia seleccionada se aplica a las pantallas activas incluidas en el alcance
- **AND** los controles y destinos visibles siguen sujetos a los mismos permisos existentes
