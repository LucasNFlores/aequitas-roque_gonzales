# Diseño

La ruta `/tutorial` y la vista `tutorial.index` se eliminan. Ambas navegaciones dejan de presentar el destino. La especificación vigente aclara que la guía técnica no es una ruta ni un destino de navegación.

La prueba de layout comprueba que el enlace no aparezca para los cinco roles y que las solicitudes a `/tutorial` respondan 404. No se modifican los demás destinos ni las decisiones históricas de HU-21.

## Criterios de salida

- No existe la ruta ni la vista de la guía.
- Ninguna variante de navegación contiene el enlace.
- La prueba focalizada pasa para la respuesta 404 y la navegación de los cinco roles.
- Pint y `git diff --check` pasan.

## Riesgo

Los favoritos y enlaces externos existentes a `/tutorial` ya no encontrarán la página.
