# Propuesta: usar la raíz como acceso al sistema

## Problema

La ruta `/` muestra la página pública de bienvenida, aunque el portal está destinado a empleados internos. Esto agrega un paso para llegar al inicio de sesión y no orienta a quienes ya tienen una sesión activa.

## Alcance

- Enviar a las personas no autenticadas que visitan `/` al inicio de sesión.
- Enviar a las personas autenticadas al dashboard existente, que adapta sus indicadores y accesos a sus permisos.
- Mantener disponibles `/login` y el flujo de autenticación actual.

## Restricciones y áreas afectadas

- No definir rutas distintas por nombre de rol: el dashboard vigente resuelve el contenido según los permisos efectivos y admite usuarios con más de un rol.
- Afecta `routes/web.php`, las pruebas de autenticación y la especificación `gestion-juridica`.

## Criterios de salida

- Una visita anónima a `/` redirige a `/login`.
- Una visita autenticada a `/` redirige a `/dashboard`, donde se conserva el contenido autorizado para el usuario.
- Las pruebas focalizadas de autenticación pasan y el código PHP cumple Pint.
- El diff queda revisado y sin errores de espacios.

## Riesgos

- `/dashboard` requiere correo verificado; se conserva el flujo existente de verificación para cuentas autenticadas que todavía no verificaron su correo.
- La aplicación utiliza un dashboard común con contenido condicionado por permisos, no páginas separadas para cada rol.
