# Tareas: HU-10 y dependencias del legajo

## Especificación

- [x] Leer HU-10 y verificar HU-15, HU-16 y HU-17 en Trello.
- [x] Confirmar la pestaña y los permisos CU18, CU12, CU19, CU14 y CU21–CU24 en la matriz de Drive.
- [x] Revisar la especificación y el código existente antes de proponer el cambio.
- [x] Actualizar la especificación de gestión jurídica con consulta del legajo, alcance y separación de permisos.

## Backend y autorización

- [x] Implementar búsqueda y ficha de legajo con eager loading y estados vacíos.
- [x] Asegurar el alcance de Profesional y denegar acceso a Secretario/usuarios sin CU18.
- [x] Registrar versiones al reemplazar documentos y proteger la lectura/descarga histórica.
- [x] Implementar alta, edición, consulta y baja lógica de reportes según CU21–CU24.
- [x] Implementar la vista autenticada del PDF de comprobantes según CU14.

## Interfaz

- [x] Añadir entrada de navegación autorizada y búsqueda por DNI/nombre.
- [x] Presentar procesos, turnos, documentos/versiones, reportes, comprobantes e historial.
- [x] Confirmar que Directivo recibe metadatos documentales sin rutas ni contenido de archivos.
- [x] Revisar estados vacíos y uso responsive en el navegador de Codex.

## Validación y entrega

- [x] Añadir o actualizar pruebas PHPUnit para roles, alcance, relaciones, versiones y archivos.
- [x] Ejecutar las pruebas backend afectadas y Pint; corregir fallos.
- [x] Probar la UI desde el navegador de Codex y verificar navegación, búsqueda y permisos.
- [x] Actualizar la documentación afectada, inspeccionar el diff y ejecutar `git diff --check`.
