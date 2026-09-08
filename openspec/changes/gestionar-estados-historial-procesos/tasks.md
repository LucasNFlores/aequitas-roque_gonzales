# Tareas de implementación

## Modelo y persistencia

- [x] Crear catálogo `EstadoProceso`, factory, relaciones y seeder de estados base.
- [x] Crear `HistorialEstadoProceso`, factory, relaciones y migración indexada.
- [x] Convertir `procesos.estado` a texto extensible y conservar compatibilidad con `pendiente`.
- [x] Registrar automáticamente el estado inicial y las transiciones con motivo y usuario.

## Autorización y rutas

- [x] Completar políticas de estados e historial.
- [x] Registrar rutas protegidas para procesos y administración de estados.
- [x] Mantener el alcance de procesos asignados al Profesional en consultas y acciones.
- [x] Agregar navegación visible sólo para permisos autorizados.

## Livewire y vistas

- [x] Implementar listado y filtros de procesos con consulta de historial.
- [x] Implementar cambio de estado con validación de estado activo y motivo.
- [x] Implementar catálogo de estados con alta, edición, orden, activación y desactivación segura.
- [x] Integrar Blade, Alpine.js y Tailwind CSS siguiendo la interfaz existente.

## Calidad y documentación

- [x] Agregar pruebas PHPUnit de rutas, permisos, alcance, transiciones y estados.
- [x] Actualizar la documentación local del modelo y esquema afectados.
- [x] Ejecutar Pint, pruebas relacionadas, compilación frontend y `git diff --check`.
