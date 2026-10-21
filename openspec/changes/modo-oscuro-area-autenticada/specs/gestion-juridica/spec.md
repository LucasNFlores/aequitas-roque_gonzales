## ADDED Requirements

### Requirement: Tema claro, oscuro y del sistema en el área autenticada

El área autenticada MUST ofrecer los temas Claro, Oscuro y Sistema mediante un control accesible y conservar la elección en el navegador. Cuando no haya una elección guardada, MUST iniciar en Claro. Sistema MUST seguir `prefers-color-scheme`. El tema resuelto MUST aplicarse antes del primer render visible. Los tokens semánticos compartidos y las pantallas de dashboard, perfil, clientes, turnos, comprobantes, procesos, legajos, documentos, reportes, usuarios, servicios, estados y categorías MUST mantener texto, controles y estados legibles en ambos temas. Los gráficos canvas MUST reflejar el tema resuelto. El cambio visual MUST NOT modificar permisos, rutas, consultas ni lógica de negocio y MUST NOT rediseñar el login.

#### Scenario: Persona cambia y recarga el tema

- **WHEN** una persona autenticada elige Claro u Oscuro y vuelve a cargar una pantalla
- **THEN** la preferencia guardada se aplica antes de mostrar el área autenticada
- **AND** los controles, formularios, tablas, badges, diálogos y gráficos mantienen contraste legible

#### Scenario: Persona elige el tema del sistema

- **WHEN** una persona elige Sistema o no tiene preferencia previa
- **THEN** la interfaz sigue el tema del sistema operativo mientras Sistema esté seleccionado
- **AND** la opción inicial sin preferencia explícita continúa siendo Claro

#### Scenario: Una persona usa módulos de distintos roles

- **WHEN** consulta pantallas de los roles autorizados en escritorio o en un viewport móvil
- **THEN** la apariencia seleccionada se aplica a las pantallas activas incluidas en el alcance
- **AND** los controles y destinos visibles siguen sujetos a los mismos permisos existentes
