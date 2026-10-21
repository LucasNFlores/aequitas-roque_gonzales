# Diseño

Las pantallas adoptan la paleta y los tokens semánticos de Aequitas existentes. Los títulos operativos y datos usan Figtree; Playfair Display permanece reservado a acentos editoriales de marca. Las acciones primarias usan `aequitas-900` con hover `aequitas-800`; los focos e indicadores usan `gold-600`; superficies usan blanco, `canvas` y neutros con bordes `line`. Éxito, error y acciones destructivas mantienen sus colores semánticos.

La bandeja de procesos conserva todos los filtros, las columnas y acciones controladas por `@can`, así como sus modales Livewire/Alpine y la paginación. En pantallas pequeñas, la grilla de filtros colapsa a una columna y la tabla continúa contenida con desplazamiento horizontal.

El alta de proceso adicional mantiene su formulario existente, campos, errores, token de envío, cliente fijo, protección contra doble envío y destino de cancelación. Legajos mantienen búsqueda, conteos y datos autorizados por rol. Las tarjetas del proceso conservan turnos, documentos, reportes, comprobantes e historial con sus condiciones de visibilidad actuales.

La pantalla de documentos conserva las acciones disponibles según policy, versiones históricas, filtros, modales, carga y estados. El historial del reporte conserva versión actual, actor y fecha de cada versión anterior. Se limitan las modificaciones a vistas específicas de procesos, legajos, documentos e historial de reportes; no se cambian layout, navegación, CSS global, Tailwind config ni componentes compartidos.

## Criterios de salida

- Todas las pantallas del alcance usan colores y radios semánticos existentes, con foco visible y contraste legible.
- Se conservan roles, permisos, acciones, estados, filtros, contenido y alcance de los datos.
- Los flujos son utilizables en viewport de escritorio y móvil, sin desbordamiento de página.
- Los assets de frontend se compilan y las interacciones se revisan en el navegador integrado.
- No se ejecutan pruebas de backend.
