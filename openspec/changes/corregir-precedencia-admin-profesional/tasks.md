# Tareas: precedencia de Administrador en roles múltiples

## Preparación

- [x] Leer la tarjeta Trello, AGENTS.md, instrucciones de skills, documentación afectada y especificación vigente.
- [x] Verificar metadatos de la hoja online, pestaña correcta, 41 CU y autorización del Administrador en todos.
- [x] Auditar policies, scopes, rutas, controladores y componentes que filtran por rol Profesional.
- [x] Definir alcance, diseño, criterios de aceptación y riesgos antes de implementar.

## Implementación

- [x] Incorporar una comprobación reutilizable para aplicar el alcance de Profesional sólo cuando el actor no tiene rol Administrador.
- [x] Corregir scopes, policies, componentes y consultas secundarias afectados, manteniendo permisos Spatie y demás perfiles.
- [x] Añadir pruebas PHPUnit para Administrador, Profesional y Administrador + Profesional en clientes, procesos, agenda/turnos, documentos/descargas, legajos, historial y reportes.

## Validación y cierre

- [x] Ejecutar la prueba PHPUnit focalizada y Pint para los archivos PHP modificados; ejecutar también los tests relacionados de autorización, legajos, turnos y reportes.
- [x] Validar todos los cambios OpenSpec en modo estricto y revisar `git diff --check`.
- [x] Revisar diff, crear commit convencional atómico y dejar limpia la rama dedicada.
- [x] Integrar la rama terminada en `dev` sin publicar commits al remoto.
- [x] Verificar la tarjeta Trello y moverla a Done sólo tras completar todos los criterios.
