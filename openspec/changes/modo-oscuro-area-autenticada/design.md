# Diseño

El documento autenticado resolverá la preferencia `light`, `dark` o `system` desde `localStorage` antes de cargar los estilos visibles. Una cookie del navegador reflejará la preferencia para que la opción seleccionada también se renderice correctamente después de una recarga. La preferencia inicial será `light`; la opción `system` seguirá `prefers-color-scheme` y reaccionará a cambios del sistema mientras permanezca activa. El login y los layouts estáticos quedan fuera de esta resolución.

Las variables semánticas CSS actuales conservarán sus valores claros. Un conjunto paralelo bajo `[data-theme="dark"]` aportará superficies marino-grisáceas, texto claro, bordes visibles y tonos de estado desaturados. Las utilidades de colores Tailwind ya usadas por las vistas activas se apoyarán en variables con alpha para heredar esos valores sin alterar sus contratos ni su lógica.

El selector estará en la navegación compartida con etiquetas visibles y agrupación accesible. Cada cambio se guardará localmente y actualizará `data-theme` sin recarga. Los controles compartidos, formularios, tablas, overlays y feedback usarán los tokens resultantes. Los colores de foco y destructivos conservarán una separación semántica.

El gráfico de estados consultará los tokens CSS al dibujar y volverá a renderizarse al cambiar de tema. La validación visual abarcará pantallas representativas de perfiles operativos distintos en escritorio y 390 px.

## Criterios de salida

- Claro conserva el aspecto actual y es el valor inicial cuando no hay preferencia guardada.
- Claro, Oscuro y Sistema son seleccionables por teclado y tecnologías de asistencia; la elección sobrevive recargas.
- El tema correcto se aplica antes de mostrar la interfaz autenticada.
- Vistas cubiertas, componentes compartidos, estados, overlays y gráficos son legibles en ambos temas.
- Login, permisos y lógica de negocio no cambian.
- `npm run build`, `php artisan view:cache`, `git diff --check` y la revisión visual de escritorio/móvil finalizan correctamente; no se ejecutan pruebas de backend.
