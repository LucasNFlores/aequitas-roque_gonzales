# Diseño

El login sigue intacto y sirve como referencia de marca. Las pantallas de acceso reutilizan la composición clara del layout invitado; aplican azul marino a acciones principales, dorado a focos y vínculos puntuales, texto oscuro y estados de éxito/error con significado visible. Figtree sigue siendo la fuente de lectura y controles. Playfair Display queda reservada para los acentos de marca existentes.

Perfil y dashboard conservan el layout autenticado. Se aplican los tokens `aequitas-900`, `gold-600`, `canvas`, `ink`, `muted`, `line`, `rounded-control`, `rounded-card`, `rounded-panel`, `shadow-panel` y `shadow-floating` ya definidos. Las tarjetas, indicadores, separadores, etiquetas y estados vacíos usan superficies claras con contraste legible. Acciones destructivas y errores conservan su semántica de peligro.

La estructura responsiva, los datos, los textos operativos, los enlaces, las validaciones y las condiciones por rol permanecen iguales. Los gráficos mantienen su alternativa de valores accesibles. Filtros y controles mantienen nombres, estados seleccionados, errores asociados y tamaños táctiles utilizables.

No se editarán layouts, navegación, CSS global, configuración de Tailwind ni componentes compartidos. Si durante la implementación fuera necesaria una de esas superficies, se informará al chat centro antes de modificarla.

## Criterios de salida

- Las vistas incluidas no mantienen utilidades índigo heredadas para acciones, focos, selección o fondos de tarjetas.
- Los flujos de invitado y el perfil conservan sus formularios y mensajes.
- Las variantes del dashboard mantienen sus secciones y alcances por rol.
- El build frontend termina correctamente; no se ejecutan pruebas backend.
- Se verifica navegación e interacción a 1280 px y 375 px en navegador integrado.
