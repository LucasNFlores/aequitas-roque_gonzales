# Guía visual de Aequitas

Esta guía registra la base visual compartida implementada para Aequitas. El área autenticada ofrece un botón para alternar entre los temas Claro y Oscuro y mantiene la prioridad de lectura y operación de las pantallas administrativas.

## Objetivo visual implementado

La aplicación usa una identidad jurídica sobria y legible. Figtree es la tipografía general; Playfair Display queda reservada para acentos editoriales de marca. El azul marino identifica la marca, las acciones primarias y la navegación activa. El dorado aparece en focos, indicadores y detalles puntuales. Claro conserva las superficies blancas y neutras suaves, el texto oscuro y los bordes discretos. Oscuro usa superficies marino-grisáceas, texto claro, bordes visibles y tonos de estado moderados.

La base se declara en `resources/css/app.css` mediante variables CSS semánticas por tema y en `tailwind.config.js` mediante utilidades reutilizables. Las utilidades de paletas Tailwind heredadas también se enlazan a variables de tema para adaptar vistas existentes. Los componentes compartidos adoptan convenciones de radio, foco y elevación.

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

Las variables de superficie (`--aequitas-surface`, `--aequitas-surface-muted`, `--aequitas-control`), texto, bordes y feedback semántico tienen valores claros por defecto y valores oscuros bajo `[data-theme="dark"]`. Las paletas Tailwind heredadas usadas en feedback, badges, tablas y formularios también se adaptan al tema. Los controles nativos respetan `color-scheme`.

## Layout autenticado y navegación implementados

- El layout autenticado carga Figtree y Playfair Display, aplica el fondo neutro compartido y mantiene una cabecera clara con borde y sombra leve.
- Un botón accesible de alternancia Claro / Oscuro está en la navegación común y guarda la elección en `localStorage`.
- Un script temprano en el layout autenticado aplica la preferencia antes de cargar los estilos. Sin preferencia guardada, la aplicación inicia en Claro. El login y los layouts estáticos no adoptan el tema oscuro.
- La navegación lateral conserva los enlaces y permisos existentes. La marca usa azul marino; el elemento activo usa fondo azul pálido, texto marino y un indicador dorado.
- El botón de menú móvil tiene un área táctil mínima de 44 px, foco visible y atributos `aria-expanded` / `aria-controls`. El overlay mantiene el cierre del menú.
- Un enlace de salto al contenido permite recorrer la página por teclado.

## Botones, formularios y feedback implementados

- Los botones primarios usan azul marino y estados hover, focus, active y disabled visibles. Los secundarios conservan fondo blanco y borde neutro; los destructivos mantienen rojo para expresar su semántica.
- Los campos usan bordes neutros, texto oscuro y foco dorado. Labels y errores siguen siendo componentes separados y asociados en las vistas.
- Los errores usan texto rojo de contraste fuerte. Los estados de sesión anuncian el mensaje mediante una región `status`.
- Los estilos explícitos pasados por una vista continúan teniendo prioridad para permitir adaptaciones locales, incluido el login existente.

## Pantallas administrativas y alcance

La base visual compartida cubre el layout autenticado, la navegación, botones, campos, labels, errores y feedback. En Claro y Oscuro también cubre las pantallas activas de dashboard, perfil, clientes, turnos/agenda, comprobantes, procesos, legajos, documentos, reportes, usuarios, servicios, estados y categorías. Tablas de escritorio, tarjetas móviles, formularios, modales, vacíos, errores, badges y estados operativos conservan su contenido y acciones autorizadas.

Los gráficos canvas consultan los tokens actuales y se vuelven a dibujar cuando cambia el tema. Esta cobertura no incluye `layouts.static` ni `static-navigation`, usados por la versión legada de servicios.

## Recomendaciones futuras

1. Revisar por separado `layouts.static` y `static-navigation`, usados por la versión legada de servicios, cuando se planifique su retiro o alineación.
2. Limitar Playfair Display a la marca y a encabezados editoriales puntuales, manteniendo Figtree para contenido y controles.

## Archivos principales

- Login de referencia: `resources/views/auth/login.blade.php` y `resources/views/layouts/guest.blade.php`.
- Layout autenticado: `resources/views/layouts/app.blade.php`.
- Navegación autenticada: `resources/views/layouts/navigation.blade.php` y `resources/views/components/navigation/nav-link.blade.php`.
- Tokens y estilos: `resources/css/app.css` y `tailwind.config.js`.
- Componentes compartidos: `resources/views/components/buttons/`, `forms/` y `feedback/`.
