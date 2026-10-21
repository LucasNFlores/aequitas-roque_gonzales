# Base visual compartida de Aequitas

## Problema

El login ya presenta la identidad Aequitas con Figtree, azul marino, dorado moderado y acentos editoriales Playfair Display. El área autenticada aún usa defaults índigo y grises de Breeze, con estilos sin convenciones comunes para color, tipografía, foco, radios y elevación.

## Alcance

- Definir tokens reutilizables en Tailwind y variables CSS para marca, superficies, texto, estados, radios y elevación.
- Alinear el layout autenticado, la navegación y los componentes compartidos de botones, campos y feedback.
- Mantener Figtree para lectura y uso operativo; reservar Playfair Display para acentos editoriales.
- Actualizar `DESIGN.md` con las decisiones implementadas y las recomendaciones pendientes.
- Verificar el build del frontend y la interfaz afectada en escritorio y móvil.

## Restricciones

- No rediseñar pantallas de módulos ni migrar masivamente sus clases locales.
- No cambiar permisos, rutas ni destinos de navegación.
- No ejecutar pruebas de backend.
- Conservar labels, errores junto a campos, estados de foco, disabled y navegación móvil accesible.

## Áreas afectadas

`tailwind.config.js`, `resources/css/app.css`, layout autenticado, navegación y componentes compartidos de botones, formularios y feedback, además de `DESIGN.md`.

## Enfoque

Introducir tokens semánticos Aequitas y aplicarlos en los puntos compartidos. Usar el azul marino para acciones primarias y navegación; reservar el dorado para foco y acentos; mantener neutros de alto contraste en superficies administrativas. Aplicar radios de control/tarjeta/panel y sombras leves coherentes.

## Riesgos

Los componentes compartidos también aparecen en formularios de acceso y CRUD; sus clases explícitas seguirán prevaleciendo donde una pantalla ya las define. Las vistas de módulos con utilidades índigo directas necesitarán adaptación posterior por sus respectivos chats.
