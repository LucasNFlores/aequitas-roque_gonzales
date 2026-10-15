# Diseño: acceso contextual desde la raíz

## Decisiones

- La ruta raíz comprobará si hay una sesión autenticada: visitantes irán a la ruta nombrada `login` y usuarios autenticados a `dashboard`.
- Se reutilizará el dashboard actual, que arma la vista a partir de los permisos vigentes y no de una prioridad fija entre roles.
- `/login`, el envío del formulario y las reglas de verificación de correo permanecen sin cambios.

## Verificación

- Cubrir con pruebas feature los destinos de `/` para visitantes y usuarios autenticados.
- Ejecutar la prueba PHPUnit afectada, Pint y `git diff --check`.
