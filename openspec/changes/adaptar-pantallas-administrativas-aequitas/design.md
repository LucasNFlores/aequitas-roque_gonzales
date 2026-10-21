# Diseño

## Dirección visual

Aplicar la identidad Aequitas ya declarada por `DESIGN.md`: Figtree en toda la UI operativa, `font-editorial` solo para acentos editoriales puntuales, `aequitas-900` como marca y acción principal, `aequitas-50` para selección/hover, `gold-600` para foco e indicadores, `canvas` para superficies suaves, `ink` para texto principal, `muted` para texto de apoyo y `line` para bordes. Mantener `positive` y `negative` para comunicar estados semánticos. No añadir colores globales ni componentes compartidos.

## Estructura y componentes

- Mantener la composición y los datos de los módulos. Afinar jerarquía de título, descripción, controles de búsqueda/filtro, acciones y listados con las clases locales de cada vista.
- Presentar tablas dentro de paneles claros con encabezados diferenciados, separadores sutiles y filas con hover discreto; conservar desbordamiento controlado cuando la densidad tabular lo requiera.
- Mantener modales con título asociado, cierre accesible, límites de altura y scroll interno en pantallas pequeñas; usar botones de ancho completo en móvil y alineación al final en escritorio.
- Preservar labels visibles, errores junto al campo, disabled/loading y confirmaciones de baja/restauración.
- Reutilizar el layout y componentes existentes sin modificar archivos compartidos.

## Interacción y autorización

Las directivas `@can`, `wire:*`, `x-*`, formularios HTTP, rutas, filtros, búsqueda, paginación, sincronización de roles y servicios, confirmaciones y mensajes permanecen iguales. No se concede acceso adicional a ningún rol. Se verifica visualmente el flujo de controles ya presentes.

## Responsive y accesibilidad

Diseño mobile-first, campos en una columna que pasan a rejilla cuando hay ancho, acciones con objetivos cómodos, tablas con scroll local solo donde sea necesario y botones que no dependan del hover. Mantener foco visible dorado, `aria-modal`, `aria-labelledby`, labels y texto de estado existentes.

## Criterios y riesgos

Se compilarán los assets y se revisarán las seis superficies en escritorio y móvil, interactuando con búsqueda/filtros, formularios y modales sin ejecutar pruebas de backend. La revisión debe confirmar que no se alteraron bindings, permisos ni navegación. El riesgo principal es el acoplamiento de apertura local Alpine con acciones Livewire; las ediciones quedarán limitadas a atributos `class` salvo correcciones de presentación necesarias y no funcionales.
