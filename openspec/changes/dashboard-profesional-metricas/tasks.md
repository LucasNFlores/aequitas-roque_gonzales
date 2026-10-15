# Tareas

## Especificación

- [x] Confirmar el alcance aprobado del dashboard Profesional y no modificar otros roles.
- [x] Definir criterios de cartera actual, representación accesible y exclusión de reportes.

## Implementación

- [x] Limitar `Reporte::visibleTo()` a procesos actualmente asignados al Profesional.
- [x] Quitar la tarjeta de reportes únicamente para consultas que requieren alcance profesional.
- [x] Reutilizar el gráfico de estados para Profesional y conservar sus vacíos y fallback accesibles.
- [x] Actualizar especificación canónica y documentación del dashboard.

## Verificación y entrega

- [x] Agregar pruebas para scope reasignado, datos ajenos, tarjeta ausente, gráfico con dos estados y fallback con cero/uno.
- [x] Ejecutar PHPUnit focalizado, Pint y build Vite.
- [x] Verificar en navegador dashboard Profesional y rol comparativo Directivo, permisos, enlaces, denegación de agenda, gráfico y viewport móvil; limpiar QA.
- [x] Revisar diff, hacer commits atómicos Conventional Commits e integrar en `dev` local limpio.
