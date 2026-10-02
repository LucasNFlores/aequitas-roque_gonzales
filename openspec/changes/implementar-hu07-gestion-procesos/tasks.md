# Tareas HU-07

## Revisión previa

- [x] Confirmar árbol limpio, actualizar `dev` desde `origin/dev` y abrir rama dedicada.
- [x] Leer la tarjeta HU-07, OpenSpec, documentación funcional y matriz online.
- [x] Evaluar la bandeja, políticas, catálogo de estados e historial ya existentes.
- [x] Registrar alcance, decisiones, criterios de salida y riesgos antes del código.

## Persistencia y reglas

- [x] Agregar `honorarios` nullable a procesos con migración reversible.
- [x] Implementar admisión y rechazo de pendientes, incluyendo causal obligatoria e historial.
- [x] Implementar asignación/reasignación validando estado del usuario, rol y compatibilidad del servicio.
- [x] Actualizar honorarios desde el servicio al guardar asignación o reasignación.

## Autorización y bandeja

- [x] Proteger acciones según permisos de matriz tanto en Livewire como en UI.
- [x] Impedir que el cambio general de estado omita las reglas especiales de admisión/rechazo.
- [x] Agregar filtros por servicio, coordinador y fechas; completar búsqueda y conservar paginación.
- [x] Mostrar información actualizada de cliente, servicio, estado, coordinador, profesional y honorarios.
- [x] Mantener fuera de listados, historial y acciones los procesos ajenos al Profesional.

## Calidad y entrega

- [x] Agregar pruebas PHPUnit de permisos, validaciones, persistencia, compatibilidad, filtros y alcance.
- [x] Ejecutar migraciones/pruebas dentro de Docker y formatear PHP con Pint.
- [x] Validar en el navegador los flujos de admitir, rechazar, asignar, reasignar, consultar historial y filtrar.
- [x] Eliminar solo los registros de prueba creados para la validación web.
- [x] Actualizar OpenSpec/documentación afectada, inspeccionar diff y ejecutar `git diff --check`.
- [x] Integrar la rama terminada en `dev` local; no hacer push.
