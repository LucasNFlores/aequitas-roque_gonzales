# Tareas: creación de procesos adicionales

## Preparación

- [x] Confirmar en la matriz online CU 38 y sus roles antes de aplicar código.
- [x] Revisar el contrato actual de `Proceso`, sus relaciones y el catálogo activo de estados, servicios y usuarios Coordinador.
- [x] Definir la protección contra doble envío en la interfaz y el servidor sin imponer una restricción funcional no aprobada sobre procesos similares.

## Autorización y dominio

- [x] Agregar el permiso técnico específico de CU 38 y asignarlo a Secretario y Administrador en el seeder correspondiente.
- [x] Incorporar una policy o capacidad de servidor para crear procesos adicionales y negar los roles no autorizados.
- [x] Implementar una acción o servicio transaccional que reciba un cliente existente, cree un único proceso `pendiente` y no cree ni modifique un turno.

## Interfaz y validación

- [x] Crear una ruta protegida y una entrada de interfaz desde el contexto de un cliente existente.
- [x] Agregar un Form Request con validación de datos del proceso y referencias activas de servicio, Coordinador y estado inicial.
- [x] Mostrar el resultado en el legajo o detalle del proceso sin confundirlo con el alta inicial de cliente.

## Pruebas y validación

- [x] Cubrir creación exitosa por Secretario y Administrador, con cliente existente y estado `pendiente`.
- [x] Verificar que no se duplican clientes ni se crean turnos automáticamente.
- [x] Cubrir roles prohibidos, referencias inválidas o inactivas y reenvío de formulario.
- [x] Ejecutar las pruebas focalizadas, el formateo requerido para PHP modificado y revisar el flujo resultante en la interfaz.
