# Guía visual de Aequitas

Esta guía describe la interfaz que está implementada actualmente. Distingue el acceso (login) del área autenticada porque hoy usan tratamientos visuales distintos. Las recomendaciones de unificación aparecen al final y no representan cambios ya hechos en la aplicación.

## Alcance y fuentes

La referencia visual del login es `resources/views/auth/login.blade.php` junto con `resources/views/layouts/guest.blade.php` y `public/images/auth/estudio-juridico-login.png`. Para el área autenticada, las referencias principales son `resources/views/layouts/app.blade.php`, `resources/views/layouts/navigation.blade.php` y `resources/views/dashboard.blade.php`.

Los estilos globales se cargan desde `resources/css/app.css`. Tailwind está configurado en `tailwind.config.js`, donde la familia `sans` se extiende con Figtree. No hay actualmente una capa de variables CSS o un catálogo central de tokens de color y espaciado; varios estilos se declaran directamente en clases Blade.

## Identidad visual observada

La interfaz combina una presentación editorial y cálida en el acceso con una interfaz administrativa funcional en las pantallas autenticadas. La marca visible es **Aequitas · Gestión jurídica**. El login usa fotografía, azul marino, acentos dorados y títulos serif. El área autenticada conserva en gran medida los componentes base de Breeze: superficies blancas y grises, navegación índigo y controles con bordes redondeados.

## Tipografía

- **Figtree** es la familia sans-serif configurada como `font-sans` y cargada también en los layouts.
- **Playfair Display** se carga en el layout de acceso. Se aplica a la marca Aequitas y a los mensajes editoriales del panel fotográfico; no está establecida como tipografía general de las pantallas internas.
- El resto de la interfaz usa principalmente la escala tipográfica de Tailwind mediante clases como `text-sm`, `text-base`, `text-lg`, `text-2xl` y `text-3xl`.
- **Plus Jakarta Sans y Montserrat no están configuradas** en los archivos revisados.

## Colores implementados

Los valores siguientes describen usos encontrados en las vistas; no son todavía tokens globales.

| Uso | Valor o clase encontrada | Contexto |
| --- | --- | --- |
| Fondo del acceso | `#101a2a` | Fondo base de la página de login |
| Superposición del fondo | `#07111e` con transparencia | Oscurece la fotografía de fondo |
| Azul marino de marca | `#172538` | Marca, título y botón principal del login |
| Dorado de marca | `#a17b42`, `#8c6936`, `#b28a4b`, `#e4c687`, `#d8b776` | Etiquetas, enlaces, emblema y detalles del panel editorial |
| Superficie de acceso | `white` / `bg-white` | Tarjeta principal y panel del formulario |
| Neutros del acceso | `slate-200`, `slate-400`, `slate-500`, `slate-600`, `slate-700`, `slate-900` | Bordes, textos, ayudas y campos del formulario |
| Superficies internas | `gray-50`, `gray-100`, `white` | Fondo del dashboard, contenedor de la app y tarjetas |
| Navegación y acentos internos | `indigo-50`, `indigo-500`, `indigo-600`, `indigo-700`, `indigo-800` | Elementos activos, foco, enlaces, marca de navegación e indicadores |

Los campos del login declaran `bg-white` y `border-slate-200` en Blade. Si una captura los muestra azulados, puede reflejar el estado del navegador o del campo y no un token de superficie compartido.

## Login

- El fondo ocupa toda la pantalla y reutiliza `estudio-juridico-login.png`, con desenfoque, una capa oscura y un degradado radial.
- La tarjeta central tiene ancho máximo `max-w-6xl`, borde blanco de 10 px, esquinas muy redondeadas y sombra marcada.
- En escritorio se organiza en dos columnas asimétricas (`0.92fr` para el formulario y `1.08fr` para la fotografía). El panel visual se muestra desde el breakpoint `lg`.
- El panel izquierdo presenta la marca, el formulario y una nota de acceso seguro. El formulario usa labels visibles, errores junto al campo, opción de recordar sesión, enlace de recuperación y un botón de acción principal.
- El panel derecho superpone textos blancos y detalles dorados sobre la fotografía. En tamaños menores a `lg`, ese panel se oculta; el diseño actual no lo apila debajo del formulario.
- El botón del login sobrescribe el estilo base del componente para usar azul marino, texto blanco, esquinas `rounded-xl`, sombra y estados hover, focus y active.

## Área autenticada

- El layout principal usa una navegación lateral blanca de 16 rem en escritorio. En pantallas pequeñas muestra una barra superior y un menú lateral desplegable.
- La navegación activa usa fondo `indigo-50`, texto `indigo-700` y foco `indigo-500`; el estado inactivo usa texto gris y hover gris claro.
- El dashboard separa el encabezado del contenido. Usa fondos `gray-50` y `gray-100`, tarjetas blancas con bordes `gray-200`, esquinas `rounded-xl` y sombras suaves.
- Indicadores, insignias y enlaces emplean acentos índigo. Las tarjetas y grillas se adaptan mediante breakpoints de Tailwind.
- Los componentes compartidos de botones y campos conservan defaults Breeze en gris e índigo. Las vistas pueden personalizarlos mediante clases adicionales; el login es un ejemplo de esa personalización.

## Formas, espaciado y profundidad

- La aplicación usa la escala de espaciado de Tailwind, con utilidades explícitas como `px-4`, `p-5`, `gap-4`, `py-8` y sus variantes responsive.
- No hay una regla universal de radio: el login usa radios amplios (`rounded-[2.75rem]`, `rounded-[2rem]`, `rounded-xl`), mientras que el área interna combina `rounded-xl`, `rounded-lg`, `rounded-md` y otros valores de Breeze.
- El login usa una sombra ambiental fuerte para separar la tarjeta del fondo. En las pantallas internas predominan `shadow-sm` y bordes grises suaves.
- Los campos compartidos usan borde, radio y foco de la configuración de Tailwind Forms/Breeze. El login amplía esos estilos con clases propias.

## Interacción y accesibilidad

- Mantener labels asociados a sus campos y mostrar los errores junto al control correspondiente.
- Conservar estados de foco visibles; los componentes base usan anillos índigo y el login los adapta al dorado de marca.
- La navegación móvil expone `aria-expanded` y `aria-controls`, y permite abrir y cerrar el menú.
- Preservar los estados hover, focus, active y disabled que ya definen los componentes y las vistas.
- No usar únicamente el color para comunicar errores, estado activo o disponibilidad; conservar texto y semántica accesible.

## Recomendaciones para cambios futuros

Estas recomendaciones son propuestas para mantener coherencia; no describen el estado actual.

1. Tratar el login y el área autenticada como dos superficies del mismo producto. Reutilizar identidad de marca sin trasladar automáticamente la fotografía, los radios extremos ni el dorado a cada pantalla administrativa.
2. Mantener Figtree como sans-serif común y reservar Playfair Display para usos editoriales puntuales, salvo que una decisión de diseño aprobada amplíe su alcance.
3. Definir tokens semánticos compartidos para color, tipografía, radios, foco y elevación antes de reemplazar sistemáticamente los valores directos de las clases.
4. Acordar si índigo sigue siendo el acento funcional del área autenticada o si se migra a azul marino/dorado. Mientras no se implemente esa decisión, documentar ambas paletas como tratamientos existentes.
5. Si se modifica el login para móviles, describir y validar el comportamiento real del panel visual en cada breakpoint; la guía no debe afirmar que se apila cuando el código lo oculta.

## Archivos de referencia

- Login: `resources/views/auth/login.blade.php`
- Layout de acceso: `resources/views/layouts/guest.blade.php`
- Imagen de acceso: `public/images/auth/estudio-juridico-login.png`
- Layout autenticado: `resources/views/layouts/app.blade.php`
- Navegación: `resources/views/layouts/navigation.blade.php`
- Dashboard: `resources/views/dashboard.blade.php`
- Estilos globales: `resources/css/app.css`
- Configuración Tailwind: `tailwind.config.js`
- Componentes compartidos: `resources/views/components/buttons/`, `forms/`, `navigation/` y `branding/`
