# Adaptar pantallas comunes a la base visual de Aequitas

## Problema

La base visual compartida ya está integrada y el login funciona como referencia de marca, pero las vistas activas de recuperación y confirmación de acceso, el perfil y el dashboard todavía conservan estilos Breeze, índigo y grises locales. Esto deja una experiencia desigual entre el acceso y la operación diaria.

## Alcance

- Adaptar recuperación de contraseña, restablecimiento, confirmación de contraseña y verificación de correo.
- Adaptar el perfil y sus formularios de información, contraseña y eliminación de cuenta.
- Adaptar la vista de dashboard para sus variantes de Secretario, Profesional, Coordinador, Directivo y Administrador, incluidos gráficos, filtros, listas, estados vacíos y errores asociados.
- Reutilizar Figtree, los tokens visuales compartidos, las superficies claras, las esquinas y las sombras existentes.
- Preservar rutas, permisos, destinos, datos, validaciones y comportamiento de cada pantalla.
- Revisar las pantallas reales en escritorio y móvil con navegación e interacciones.

## Fuera de alcance

- Cambiar el login, los layouts, la navegación, los tokens globales, Tailwind config, CSS global o componentes compartidos.
- Cambiar lógica de servidor, permisos, consultas, rutas o contenido funcional de los módulos.
- Ejecutar pruebas de backend.

## Criterios de salida

- Las vistas del alcance usan la base visual existente sin colores locales índigo ni estilos Breeze incompatibles.
- Los estados de validación, éxito, peligro, foco, selección, vacío y deshabilitado siguen claros y accesibles.
- El dashboard conserva el alcance y el contenido autorizado de cada rol.
- Los flujos y controles existentes funcionan con teclado, puntero y pantalla móvil.
- El frontend compila y las pantallas afectadas se revisan en navegador integrado en escritorio y móvil.

## Riesgos

- Una sustitución de clases en el dashboard podría alterar la jerarquía de métricas o la legibilidad de los gráficos; la adaptación mantendrá estructura y densidad actuales.
- Los estilos de controles deben seguir alineados con los componentes compartidos para conservar foco, error y accesibilidad.
