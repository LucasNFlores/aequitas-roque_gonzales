# Diseño

La capa visual declara variables CSS y tokens Tailwind para azul marino Aequitas, dorado, texto, fondos, bordes y feedback semántico. Figtree queda como fuente sans-serif general; Playfair Display se registra como fuente editorial y se limita a acentos de marca. Las escalas compartidas definen radios de control, tarjeta y panel, además de elevación de superficie y elementos flotantes.

El layout autenticado usa fondo neutro claro, encabezado blanco y tipografía Figtree. La navegación usa azul marino en marca y selección, con una línea dorada discreta como indicador activo y anillos de foco dorados visibles. El menú móvil conserva Alpine, overlay y atributos ARIA, con objetivos táctiles de 44 px. El enlace para saltar al contenido mejora la navegación por teclado.

Los botones primarios adoptan azul marino; los secundarios mantienen superficie blanca y borde neutro; las acciones destructivas conservan su semántica roja. Los campos mantienen labels visibles, errores rojos asociados y foco dorado. Los mensajes de sesión anuncian su estado a tecnologías de asistencia.

No se migran clases locales de las pantallas de módulos. Las vistas que declaran colores explícitos y los componentes nativos de Livewire podrán conservar su apariencia previa hasta que su adaptación específica aplique estos tokens.

## Criterios de salida

- Tailwind compila los tokens, familias y componentes actualizados.
- La navegación conserva destinos y permisos, y responde a teclado/táctil en escritorio y móvil.
- Los controles compartidos preservan estados hover, focus, active, disabled y error con contraste legible.
- `DESIGN.md` describe el estado implementado y diferencia el trabajo futuro.
- No se ejecutan pruebas de backend.
