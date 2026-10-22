# Compactar la escala tipográfica de la interfaz

## Problema

La escala actual de texto hace que los contenidos y la navegación ocupen demasiado espacio visual. Se solicita reducir el tamaño tipográfico general a aproximadamente dos tercios y mantener una escala de valores estándar y legible.

## Alcance

- Ajustar en Tailwind la escala de utilidades tipográficas compartidas.
- Reducir el tamaño base del texto de la interfaz autenticada y pública.
- Mantener una jerarquía clara entre texto auxiliar, contenido y encabezados.
- Actualizar la guía visual del proyecto.

## Restricciones

- Mantener el texto base en 12 px como mínimo para lectura normal.
- No reducir objetivos táctiles, espaciado, iconos ni dimensiones de controles.
- No cambiar contenido, rutas, permisos ni comportamiento de las pantallas.

## Criterios de salida

- El texto base se renderiza a 12 px.
- La escala de utilidades usa pasos tipográficos redondeados y consistentes.
- Se conserva contraste y jerarquía visual en Claro y Oscuro.
- El bundle de frontend compila correctamente.

## Riesgos

La escala de utilidades afecta muchas pantallas al mismo tiempo. La verificación debe cubrir clases pequeñas, contenido general y encabezados para evitar que etiquetas o títulos pierdan legibilidad.
