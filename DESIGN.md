# Guía visual de Aequitas

Esta guía registra la base visual compartida implementada para Aequitas. Distingue las decisiones ya aplicadas de las recomendaciones que quedan para futuras tareas. La implementación alinea el acceso con el área autenticada y mantiene la prioridad de lectura y operación de las pantallas administrativas.

## Objetivo visual implementado

La aplicación usa una identidad jurídica sobria y legible. Figtree es la tipografía general; Playfair Display queda reservada para acentos editoriales de marca. El azul marino identifica la marca, las acciones primarias y la navegación activa. El dorado aparece en focos, indicadores y detalles puntuales. Las superficies administrativas usan blancos y neutros suaves, con texto oscuro y bordes discretos.

La base se declara en `resources/css/app.css` mediante variables CSS semánticas y en `tailwind.config.js` mediante utilidades reutilizables. Los componentes compartidos adoptan convenciones de radio, foco y elevación sin reemplazar estilos locales de las pantallas de módulos.

## Tokens compartidos implementados

| Token | Valor | Uso |
| --- | --- | --- |
| `aequitas-900` | `#172538` | Marca, texto principal y acciones primarias |
| `aequitas-800` | `#243850` | Hover de acción primaria |
| `aequitas-950` | `#101a2a` | Estado activo de acción y capa oscura |
| `aequitas-50` | `#edf1f5` | Fondo de navegación seleccionada y hover |
| `gold-600` | `#8c6936` | Foco y detalle funcional con contraste |
| `gold-300` | `#d8b776` | Acento decorativo sobre azul marino |
| `canvas` | `#f5f6f8` | Fondo general autenticado |
| `ink` | `#172538` | Texto principal |
| `muted` | `#5b6470` | Texto secundario |
| `line` | `#dde2e8` | Bordes y separadores |
| `positive` | `#18794e` | Feedback de éxito |
| `negative` / `negative-hover` / `negative-active` | `#b42318` / `#c43227` / `#912018` | Error y estados de acciones destructivas |
| `rounded-control` | `0.625rem` | Campos, botones y controles de navegación |
| `rounded-card` | `0.875rem` | Tarjetas |
| `rounded-panel` | `1rem` | Paneles |
| `shadow-panel` | sombra leve azul grisácea | Superficies elevadas de forma discreta |
| `shadow-floating` | sombra ambiental media | Menú móvil y contenido flotante |

Las fuentes disponibles son `font-sans` (Figtree) y `font-editorial` (Playfair Display). El uso editorial debe mantenerse puntual para no restar claridad a datos, formularios o navegación.

## Layout autenticado y navegación implementados

- El layout autenticado carga Figtree y Playfair Display, aplica el fondo neutro compartido y mantiene una cabecera clara con borde y sombra leve.
- La navegación lateral conserva los enlaces y permisos existentes. La marca usa azul marino; el elemento activo usa fondo azul pálido, texto marino y un indicador dorado.
- El botón de menú móvil tiene un área táctil mínima de 44 px, foco visible y atributos `aria-expanded` / `aria-controls`. El overlay mantiene el cierre del menú.
- Un enlace de salto al contenido permite recorrer la página por teclado.

## Botones, formularios y feedback implementados

- Los botones primarios usan azul marino y estados hover, focus, active y disabled visibles. Los secundarios conservan fondo blanco y borde neutro; los destructivos mantienen rojo para expresar su semántica.
- Los campos usan bordes neutros, texto oscuro y foco dorado. Labels y errores siguen siendo componentes separados y asociados en las vistas.
- Los errores usan texto rojo de contraste fuerte. Los estados de sesión anuncian el mensaje mediante una región `status`.
- Los estilos explícitos pasados por una vista continúan teniendo prioridad para permitir adaptaciones locales, incluido el login existente.

## Pantallas administrativas y alcance

La base visual compartida cubre el layout autenticado, la navegación, botones, campos, labels, errores y feedback. La primera adopción por módulo cubre clientes, turnos/agenda y comprobantes de pago: tablas de escritorio y tarjetas móviles conservan los datos y las acciones autorizadas, y los formularios aplican superficies y foco de la misma base. Los filtros, búsquedas, permisos y estados operativos existentes se mantienen.

Los demás módulos todavía pueden conservar utilidades locales o defaults anteriores hasta que se adapten por separado. Esta cobertura no incluye `layouts.static` ni `static-navigation`, usados por la versión legada de servicios.

## Recomendaciones futuras

1. Continuar la adopción por módulo en las pantallas administrativas pendientes, revisando colores, estados y contraste en cada flujo.
2. Revisar tablas, tarjetas, insignias, filtros y estados vacíos pendientes para aplicar radios y elevación compartidos sin alterar densidad de datos.
3. Limitar Playfair Display a la marca y a encabezados editoriales puntuales, manteniendo Figtree para contenido y controles.
4. Revisar por separado `layouts.static` y `static-navigation`, usados por la versión legada de servicios, cuando se planifique su retiro o alineación.

## Archivos principales

- Login de referencia: `resources/views/auth/login.blade.php` y `resources/views/layouts/guest.blade.php`.
- Layout autenticado: `resources/views/layouts/app.blade.php`.
- Navegación autenticada: `resources/views/layouts/navigation.blade.php` y `resources/views/components/navigation/nav-link.blade.php`.
- Tokens y estilos: `resources/css/app.css` y `tailwind.config.js`.
- Componentes compartidos: `resources/views/components/buttons/`, `forms/` y `feedback/`.
