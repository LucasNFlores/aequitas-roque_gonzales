# Completar el perfil de usuarios internos

## Why

La cuenta interna almacena el nombre en un único campo `name`, sin apellido separado. La fecha de ingreso se permite vacía y los formularios no ofrecen un valor inicial al crear una cuenta.

## What Changes

- Agregar `apellido` al modelo y a la tabla `users`, conservando los valores existentes sin inferir apellidos automáticamente.
- Capturar y guardar apellido en la gestión administrativa y en el perfil propio.
- Presentar y buscar usuarios por nombre y apellido en las pantallas y selectores que muestran personal.
- Completar `fecha_ingreso` con la fecha actual al crear usuarios desde Livewire o el CRUD HTTP, incluso si la petición omite el campo.
- Limitar cada cuenta a un único rol en todo el sistema y mostrar una lista de selección única.
- Normalizar cuentas existentes conservando el rol de mayor jerarquía: Administrador > Directivo > Coordinador > Profesional > Secretario.
- Añadir pruebas para persistencia, búsqueda, fecha por defecto y unicidad/normalización de roles.

## Restricciones

- Mantener `name` como el campo de nombres requerido por Laravel y existente en la base.
- La nueva columna `apellido` será nullable en la migración para no invalidar cuentas existentes; el formulario exigirá apellido al crear o actualizar el perfil.
- No dividir automáticamente `name` de cuentas existentes: nombres compuestos y apellidos no se pueden inferir con seguridad.
- La fecha actual se asigna al crear cuentas; las cuentas existentes sin fecha mantienen el valor desconocido hasta que se complete explícitamente.
- Si una cuenta tiene varios roles, conservar solo el primero según la prioridad declarada; cualquier rol no listado queda por debajo de los cinco roles oficiales.
- La base de datos debe impedir que una cuenta vuelva a recibir más de un rol.

## Áreas afectadas

`User`, migración de usuarios, solicitudes, controladores, Livewire, formularios, búsquedas, selectores y presentación de usuarios, fábricas/seeders, documentación del modelo y pruebas PHPUnit.

## Criterios de salida

- `users.apellido` persiste el apellido y las vistas muestran el nombre completo sin alterar los datos previos.
- La búsqueda de usuarios coincide con nombres y apellidos.
- Una cuenta nueva recibe `fecha_ingreso` igual a hoy tanto desde el formulario como ante una petición sin fecha.
- Cada cuenta tiene cero o un rol, y los registros múltiples previos conservan únicamente el rol de mayor prioridad.
- Los formularios administrativos permiten seleccionar un único rol.
- Las pruebas focalizadas, Pint y `git diff --check` terminan correctamente.

## Riesgos

- Las cuentas existentes conservan el nombre original en `name` y no tendrán apellido hasta que se complete; al hacerlo, el dato existente debe corregirse manualmente si ya contiene el nombre completo.
- Usar solo el nombre en algún selector o historial podría ocultar el nuevo apellido; se deben revisar las vistas que muestran usuarios relacionados.
