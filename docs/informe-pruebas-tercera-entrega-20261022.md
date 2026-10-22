# Informe de pruebas — tercera entrega

**Fecha:** 22/10/2026

**Entorno de navegador:** SQLite demo aislado, preparado para la verificación; no se usaron datos preexistentes.

**Alcance:** pruebas dirigidas de procesos, clientes, turnos, reportes y documentos; recorrido de navegador; validación de entrada inválida y acceso denegado.

## Pruebas automatizadas

Los resultados de esta sección corresponden a comandos ejecutados en `dev`, informados y verificados por el coordinador. Esta rama documental no volvió a ejecutar las pruebas.

### Transiciones de proceso

- **Caso:** reglas de transición de `Proceso::transitionTo()`.
- **Precondiciones:** PHPUnit y dependencias del proyecto disponibles.
- **Datos:** casos unitarios incluidos en `ProcesoTransitionTest.php`.
- **Resultado esperado:** las reglas cubiertas producen el resultado esperado sin requerir base de datos.
- **Resultado obtenido:** 4 pruebas aprobadas, 11 aserciones.
- **Evidencia:** comando ejecutado: `php artisan test --compact tests/Unit/ProcesoTransitionTest.php`; archivo `tests/Unit/ProcesoTransitionTest.php`.

### Actualización de estado de proceso

- **Caso:** validación y autorización del PATCH de estado, incluidos los casos permitidos y la petición permitida sin `motivo`.
- **Precondiciones:** PHPUnit y dependencias del proyecto disponibles.
- **Datos:** casos de `ProcesoStateUpdateValidationTest.php`.
- **Resultado esperado:** errores de validación o autorización se manejan como respuestas controladas; cuando `motivo` se omite, la actualización no lanza una excepción.
- **Resultado obtenido:** 9 pruebas aprobadas, 65 aserciones.
- **Evidencia:** comando ejecutado: `php artisan test --compact tests/Feature/ProcesoStateUpdateValidationTest.php`; archivo `tests/Feature/ProcesoStateUpdateValidationTest.php`.

### Acceso y recorridos de legajo

- **Caso:** pruebas de consulta y acciones del legajo.
- **Precondiciones:** PHPUnit y datos preparados por las pruebas.
- **Datos:** escenarios de `LegajoTest.php`.
- **Resultado esperado:** las respuestas y acciones cubiertas por la prueba coinciden con sus aserciones.
- **Resultado obtenido:** 12 pruebas aprobadas, 77 aserciones.
- **Evidencia:** comando ejecutado: `php artisan test --compact tests/Feature/LegajoTest.php`.

### Clientes

- **Caso:** pruebas de alta y operaciones de clientes.
- **Precondiciones:** PHPUnit y datos preparados por las pruebas.
- **Datos:** escenarios de `ClienteTest.php`.
- **Resultado esperado:** las respuestas y validaciones cubiertas por la prueba coinciden con sus aserciones.
- **Resultado obtenido:** 6 pruebas aprobadas, 52 aserciones.
- **Evidencia:** comando ejecutado: `php artisan test --compact tests/Feature/ClienteTest.php`.

### Turnos

- **Caso:** creación y operaciones de turnos.
- **Precondiciones:** PHPUnit y datos preparados por las pruebas.
- **Datos:** escenarios de `TurnoTest.php`.
- **Resultado esperado:** los recorridos y resultados cubiertos por la prueba coinciden con sus aserciones.
- **Resultado obtenido:** 24 pruebas aprobadas, 134 aserciones.
- **Evidencia:** comando ejecutado: `php artisan test --compact tests/Feature/TurnoTest.php`.

### Reportes

- **Caso:** recorridos de reportes.
- **Precondiciones:** PHPUnit y datos preparados por las pruebas.
- **Datos:** escenarios de `ReporteFlowTest.php`.
- **Resultado esperado:** las operaciones y respuestas cubiertas por la prueba coinciden con sus aserciones.
- **Resultado obtenido:** 15 pruebas aprobadas, 90 aserciones. La ejecución anterior al nuevo caso había pasado 14 pruebas y 81 aserciones.
- **Evidencia:** comando ejecutado en `dev`: `php artisan test --compact tests/Feature/ReporteFlowTest.php`; el resultado final incluye el nuevo caso sobre el formulario de Profesional.

### Formulario de reporte para Profesional asignado (prueba HTTP)

- **Caso:** comprobar que el formulario de creación se renderiza y que el POST puede crear el reporte.
- **Precondiciones:** PHPUnit y datos preparados por la prueba de feature; usuario Profesional asignado con permiso y autorización confirmados para el escenario.
- **Datos:** sesión de Profesional asignado en el detalle de Proceso; caso `test_assigned_professional_sees_and_uses_the_report_form_on_process_detail` que cubre renderizado y envío de creación.
- **Resultado esperado:** la respuesta incluye el formulario y el POST completa la creación cubierta por las aserciones.
- **Resultado obtenido:** prueba dirigida aprobada, 1 prueba y 9 aserciones; el caso también pasó dentro de `ReporteFlowTest.php`, cuya ejecución final tuvo 15 pruebas y 90 aserciones.
- **Evidencia:** comando dirigido ejecutado en `dev`: `php artisan test --compact --filter=test_assigned_professional_sees_and_uses_the_report_form_on_process_detail` (1 pasada, 9 aserciones); suite completa: `php artisan test --compact tests/Feature/ReporteFlowTest.php` (15 pruebas, 90 aserciones).

### Documentos y categorías

- **Caso:** carga y operaciones de documentos y categorías.
- **Precondiciones:** PHPUnit y datos preparados por las pruebas.
- **Datos:** escenarios de `DocumentoCategoriaFlowTest.php`.
- **Resultado esperado:** los recorridos y respuestas cubiertos por la prueba coinciden con sus aserciones.
- **Resultado obtenido:** 23 pruebas aprobadas, 118 aserciones.
- **Evidencia:** comando ejecutado: `php artisan test --compact tests/Feature/DocumentoCategoriaFlowTest.php`.

### Formato PHP

- **Caso:** formato de los archivos PHP modificados en la integración.
- **Precondiciones:** Pint instalado como dependencia del proyecto.
- **Datos:** archivos PHP modificados.
- **Resultado esperado:** Pint termina sin errores de formato pendientes.
- **Resultado obtenido:** Pint pasó para el archivo actualizado de pruebas.
- **Evidencia:** comando ejecutado en `dev`: `vendor/bin/pint --format agent tests/Feature/ReporteFlowTest.php`.

## Recorrido en navegador con SQLite demo aislado

### Flujo cliente → legajo → admisión → profesional → turno → documento → reporte → historial

- **Caso:** recorrido funcional desde el alta de un cliente hasta la consulta del historial de un reporte.
- **Precondiciones:** entorno SQLite demo aislado; usuario con permisos para las operaciones realizadas; base de prueba sin datos preexistentes.
- **Datos:** cliente `Ana Demo Entrega` (ID 1); proceso y turno inicial creados durante el alta; Profesional asignado sintético `Demo Profesional Entrega` (ID 3); turno `Seguimiento`; documento PDF de categoría `Judicial`; texto del reporte indicado abajo.
- **Resultado esperado:** completar y observar en navegador las operaciones del recorrido, crear un reporte desde la sesión del Profesional asignado y mantener el proceso de origen hasta el retorno desde su historial.
- **Resultado obtenido:** se creó el cliente con su proceso y turno inicial; se admitió el proceso y se asignó el profesional; se agendó `Seguimiento` desde el detalle usando los IDs de cliente y proceso y se volvió al detalle exacto; se cargó el PDF `Judicial`; el Profesional asignado vio el formulario, creó el reporte, abrió su historial y volvió al detalle exacto.
- **Evidencia:** recorrido en navegador sobre SQLite demo aislado, con resultado visible y rutas consignadas en los casos siguientes. No hay archivos PNG guardados en el repositorio.

### Entrada inválida: DNI duplicado

- **Caso:** intentar registrar un cliente con un DNI ya existente.
- **Precondiciones:** SQLite demo aislado contiene el cliente de prueba con DNI `98765432`; se abre `/clientes/create`.
- **Datos:** DNI `98765432`.
- **Resultado esperado:** el formulario rechaza el dato duplicado y muestra una indicación comprensible.
- **Resultado obtenido:** se mostró: “Ya existe un cliente registrado con este DNI. Verificá el número o buscá al cliente existente.”
- **Evidencia:** ruta `/clientes/create`, dato enviado y mensaje visible durante el recorrido aislado. No se adjuntó captura como archivo.

### Acceso denegado: usuario sin rol

- **Caso:** abrir el legajo de un cliente con un usuario sintético sin rol.
- **Precondiciones:** SQLite demo aislado con el cliente de prueba ID 1; sesión autenticada como usuario sin rol.
- **Datos:** ruta `/clientes/1/legajo`.
- **Resultado esperado:** se deniega el acceso a la ruta protegida.
- **Resultado obtenido:** respuesta HTTP 403 y texto `USER DOES NOT HAVE THE RIGHT PERMISSIONS.`
- **Evidencia:** ruta, usuario de prueba, estado HTTP y texto observados en la sesión del navegador. La captura se vio en la sesión, pero no se guardó un PNG en el repositorio.

### Creación de reporte por el Profesional asignado e historial

- **Caso:** confirmar manualmente la visibilidad del formulario para el Profesional asignado, crear un reporte, abrir su historial y regresar al detalle.
- **Precondiciones:** sesión autenticada como Profesional asignado sintético ID 3; SQLite demo aislado con proceso ID 1 asignado; servidor reiniciado después de limpiar la caché de permisos en el almacenamiento que realmente consulta.
- **Datos:** texto “Se verificó el registro desde la ficha con el profesional asignado.”; historial `/procesos/1/reportes/2/historial`.
- **Resultado esperado:** mostrar el formulario autorizado, confirmar la creación y el autor, presentar el reporte en historial y devolver al proceso original.
- **Resultado obtenido:** el formulario apareció; al enviar el texto, la UI mostró “Reporte registrado correctamente.” y presentó como autor/registrador a `Demo Profesional Entrega`. El historial mostró el reporte y el botón “Volver al proceso” regresó a `/clientes/1/legajo/procesos/1`; el navegador quedó en el detalle con el formulario y el reporte nuevo visibles.
- **Evidencia:** antes de la corrección se había limpiado la caché en `manual-demo-storage`, mientras que el servidor consultaba el almacenamiento predeterminado del worktree. Al ejecutar `php artisan permission:cache-reset` con la base manual y la caché/archivos locales que usa el servidor, reiniciarlo y volver a autenticar al Profesional, el formulario apareció y el recorrido completó los resultados indicados. La evidencia son los mensajes, autor y rutas observados; no se guardó un archivo PNG.

## Criterios y limitaciones

- Los resultados automatizados y comandos corresponden a ejecuciones en `dev`; esta rama documental no volvió a ejecutar esas pruebas.
- El recorrido de navegador se hizo en SQLite demo aislado y no representa datos existentes.
- Las capturas observadas no están disponibles como archivos del repositorio; las rutas, pasos y resultados consignados arriba permiten repetir la observación.
- La diferencia inicial de visibilidad se debió a limpiar la caché de permisos en un almacenamiento distinto del consultado por el servidor. Tras restablecerla en el entorno activo, reiniciar el servidor y volver a autenticar, el formulario y la creación se verificaron manualmente; no se cambió ninguna regla de permisos.
