# Diseño: pantalla CU34 independiente

## Decisión

Agregar `/profesionales/servicios` como página Livewire dedicada. No reutilizar `/usuarios`: su consulta y sus acciones abarcan CU25–27. La consulta de CU34 limita resultados a usuarios no eliminados con rol Profesional y carga solo relaciones de servicios no eliminados.

## Autorización y persistencia

- Proteger la ruta con `auth` y `permission:asignar_especialidad_servicio`.
- Repetir autorización en `mount`, apertura del editor y guardado Livewire.
- Actualizar la relación N:M `user_servicios` con `sync()` dentro de una transacción.
- Validar identificadores contra registros de `servicios` cuyo `deleted_at` sea nulo.
- Mantener la petición HTTP existente protegida por el mismo permiso y reforzar su validación de servicios activos.

## Navegación

Agregar un enlace llamado “Servicios por profesional” condicionado a `@can('asignar_especialidad_servicio')` en las dos navegaciones del proyecto. El enlace de CU25–27 continúa condicionado a `listar_usuarios`.

## Verificación

Pruebas de ruta y navegación para Secretario, Profesional, Coordinador, Directivo y Administrador; peticiones directas permitidas/denegadas; validación de profesional/servicios activos; y asignación, reemplazo y retiro persistentes. Validar además los cinco perfiles en el navegador contra Docker.
