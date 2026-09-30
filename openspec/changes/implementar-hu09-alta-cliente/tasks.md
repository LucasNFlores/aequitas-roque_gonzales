# Tareas de implementación

- [x] Añadir la relación explícita de Coordinador a `Turno`.
- [x] Habilitar el canal de notificación `interno`.
- [x] Alinear el servicio inicial del catálogo con `Consultoría` y renombrar el registro legado existente con una migración reversible.
- [x] Validar que el estado inicial `pendiente` exista, esté activo y no tenga baja lógica.
- [x] Agregar unicidad de base de datos al correo y detener la migración si hay duplicados existentes.
- [x] Crear la acción transaccional de alta inicial.
- [x] Incorporar fecha y hora inicial en la solicitud y formulario de alta.
- [x] Conectar la acción al controlador de clientes y manejar errores recuperables.
- [x] Agregar pruebas de éxito, autorización, DNI/correo duplicados, estado inactivo o eliminado y reversión tras un fallo intermedio.
- [x] Revisar el diff con `git diff --check`.
- [x] Ejecutar Pint y las pruebas afectadas dentro de Sail.
