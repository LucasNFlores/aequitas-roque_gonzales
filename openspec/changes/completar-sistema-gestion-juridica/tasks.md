# Tareas de formalización documental

## Fuente y alcance

- [x] Leer metadatos de la hoja online y confirmar la pestaña de gid 708841836.
- [x] Inventariar los 41 CU, sus cinco roles, permisos y restricciones.
- [x] Contrastar las decisiones de comprobantes y Notificaciones con la matriz.
- [x] Revisar la especificación base, los cambios OpenSpec relacionados y docs/ vigentes.

## Especificación y cambio

- [x] Convertir la especificación base a Purpose, Requirements y escenarios sin omitir requisitos vigentes.
- [x] Corregir el conteo desactualizado y eliminar la tabla de permisos duplicada e incompleta.
- [x] Declarar skip_specs: true porque este cambio es exclusivamente documental.
- [x] Mantener en los cambios funcionales especializados sus deltas de comportamiento.

## Validación

- [x] Ejecutar openspec validate gestion-juridica --type spec --strict --no-interactive.
- [x] Ejecutar openspec validate completar-sistema-gestion-juridica --type change --strict --no-interactive.
- [x] Ejecutar openspec validate --all --strict --no-interactive y clasificar sus resultados.
- [x] Ejecutar git diff --check y revisar el diff final.
