# Diseño: grupos colapsables en la barra lateral

## Navegación

Dashboard queda dentro del grupo «Inicio». Inicio, Gestión jurídica, Administración e Informes pasan a encabezados accionables compactos. Cada encabezado alterna su propio panel y muestra un chevron que cambia con el estado.

Al cargar una página, solo se abre automáticamente el grupo que contiene la ruta activa; los demás se inician cerrados. Si la sesión actual no tiene destinos de un grupo, ese grupo no se renderiza. Los enlaces mantienen sus directivas de autorización.

## Accesibilidad y dimensiones

Cada encabezado es un botón de ancho completo con `aria-expanded`, `aria-controls`, foco visible y una zona interactiva de al menos 44 px. Las categorías usan 11 px en mayúsculas, con menos margen vertical entre ellas. Los paneles usan transición breve y `x-cloak` para evitar contenido desplegado antes de iniciar Alpine.
