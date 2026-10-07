# Tareas HU-13

## Preparación

- [x] Revisar `Turno`, el scheduler y la migración existente de estado; reutilizarla sin duplicar esquema.
- [x] Consultar Aequitas V2 y recuperar criterios de la tarjeta y OpenSpec.

## Implementación

- [x] Completar policy y middleware de rutas para alta, reprogramación y cancelación externas.
- [x] Añadir operaciones del scheduler externo con validación transaccional de jornada completa.
- [x] Implementar formulario y acciones Livewire para los tres flujos, con detalle obligatorio y relaciones consistentes.
- [x] Mostrar compromisos externos e historial en agenda/listado y ocultar escritura a roles no autorizados.
- [x] No implementar notificaciones ni recordatorios en HU-13; su integración queda en el módulo independiente de fase final.

## Validación y cierre

- [x] Agregar y ejecutar pruebas PHPUnit para permisos, integridad, disponibilidad, cancelación e historial.
- [x] Verificar en navegador real alta, reprogramación y cancelación; las denegaciones se cubren en PHPUnit. Crear datos identificables y limpiar solo esos registros.
- [x] Ejecutar Pint, pruebas Docker afectadas y `git diff --check`.
- [x] Revisar el diff y preparar un commit atómico antes del merge local a `dev`.
