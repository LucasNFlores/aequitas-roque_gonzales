# Diseño: precedencia de Administrador sobre el alcance del Profesional

## Contexto

Spatie permite varias asignaciones de rol y `UserController`/Livewire las guarda con `syncRoles`. El permiso de Administrador ya está presente en la matriz para todos los CU. El problema no está en la matriz ni en la asignación de permisos: ciertas policies, scopes y consultas aplican filtros de Profesional por la mera presencia de ese rol.

## Decisión

Agregar a `User` un predicado explícito que indique cuándo debe aplicarse la restricción de asignación del Profesional: tiene rol Profesional y no tiene rol Administrador. Reutilizarlo en policies y consultas que actualmente limitan al actor por `profesional_id`.

No cambiar el bypass de Gate ni los permisos de Spatie. Las policies seguirán comprobando la capacidad técnica correspondiente; sólo se omite el filtro de asignación cuando la cuenta también tiene Administrador.

## Superficies cubiertas

- Scopes `Cliente::visibleTo`, `Proceso::visibleTo`, `Turno::visibleTo`, `Documento::visibleTo` y `Reporte::visibleTo`.
- Policies de Cliente, Proceso, Turno, Documento y Reporte, incluidas consulta de legajo, consulta de historial, actualización de estado y creación/consulta de reportes.
- Bandejas y filtros de agenda/turnos y procesos, detalle de legajo, turnos sin proceso e historiales asociados.
- Acceso directo a documentos y versiones para visualización y descarga, resuelto por las policies del documento.

## Compatibilidad

- Solo Administrador conserva el acceso de superadministrador ya concedido.
- Solo Profesional mantiene el filtro por asignación actual.
- Profesional combinado con un rol distinto de Administrador continúa sujeto al filtro profesional existente; no se altera ninguna otra combinación.
- No se tocan roles, permisos, archivos de notificaciones, eventos ni proveedores externos.

## Pruebas de aceptación

- Los tres perfiles se comparan sobre clientes, procesos, turnos/agenda, documentos y versiones, legajos, historiales y reportes.
- Los scopes devuelven todos los registros autorizados a Administrador y sólo los asignados al Profesional.
- Las policies permiten al perfil combinado consultar registros ajenos que Administrador puede consultar, incluida vista y descarga documental, y deniegan al Profesional no asignado.
- Las rutas directas y los componentes Livewire muestran el mismo resultado de autorización y filtrado.
- Un perfil sin Administrador no recibe resultados ni operaciones nuevos por el cambio.

## Riesgos y mitigaciones

| Riesgo | Mitigación |
| --- | --- |
| Un query secundario conserva el filtro profesional | Auditar todos los `hasRole('Profesional')` del código y verificar listados, relaciones y filtros de agenda/legajo. |
| Se amplía un perfil profesional combinado con otro rol | Usar un predicado que excluya específicamente Administrador y cubrir Profesional solo en pruebas. |
| Un cambio de scope oculta la autorización por CU | Mantener `can(...)` en los scopes y las comprobaciones de policy; no modificar Gate ni el seeder. |
| Cambia el comportamiento de carga, escritura o notificaciones | Probar autorización de lectura y mantener fuera de alcance las superficies no requeridas. |
