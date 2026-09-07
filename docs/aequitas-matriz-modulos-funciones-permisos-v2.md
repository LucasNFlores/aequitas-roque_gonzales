+# Aequitas — Matriz de Módulos, Funciones y Permisos por Rol V2

> Copia Markdown de la pestaña **Aequitas - Matriz de Módulos, Funciones y Permisos por Rol V2** de la [matriz funcional en Google Sheets](https://docs.google.com/spreadsheets/d/13L_GwiJ1DvFIU2CaaXYfjaDDXHQEhSmcOST4z9bl1Oc/edit).

- **Fuente de verdad:** Google Sheets.
- **Pestaña:** Aequitas - Matriz de Módulos, Funciones y Permisos por Rol V2.
- **Última sincronización local:** 2026-09-07.
- **Casos de uso:** 40.
- **Roles:** Secretario, Profesional, Coordinador, Directivo y Administrador.
- **Convención:** Sí indica autorización explícita en la matriz; No indica que el rol no está autorizado. El Administrador es superadministrador y queda autorizado en todos los casos de uso.

## Matriz

| Módulo / Caso de Uso | Código CU | Descripción / Acción | Secretario | Profesional | Coordinador | Directivo | Administrador | Alcance / Notas |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Gestión de Clientes | CU 1 | Registrar Cliente | Sí | No | No | No | Sí | Registra datos personales, crea proceso en 'Consultoría' y turno inicial con Coordinador |
| Gestión de Clientes | CU 2 | Modificar Cliente | Sí | No | No | No | Sí | Actualiza datos personales y de contacto del cliente |
| Gestión de Clientes | CU 3 | Eliminar Cliente | Sí | No | No | No | Sí | Baja lógica; se verifica que no tenga procesos activos |
| Gestión de Clientes | CU 28 | Listar y Filtrar Clientes | Sí | No | Sí | Sí | Sí | Vista con filtros por estado de admisión, búsqueda y paginación |
| Gestión de Turnos y Agenda | CU 4 | Agendar Turno Interno | Sí | No | No | No | Sí | Cita entre cliente y profesional/coordinador; notifica a las partes |
| Gestión de Turnos y Agenda | CU 4.1 | Agendar Turno de Seguimiento | Sí | No | No | No | Sí | Turno vinculado a legajo o caso activo |
| Gestión de Turnos y Agenda | CU 5 | Agendar Turno Externo | Sí | No | No | No | Sí | Compromiso externo; bloquea la jornada del profesional |
| Gestión de Turnos y Agenda | CU 6 | Modificar Turno | Sí | No | No | No | Sí | Reprogramación de fecha y hora; libera la disponibilidad anterior |
| Gestión de Turnos y Agenda | CU 6.1 | Modificar Turno Externo | Sí | No | No | No | Sí | Actualiza fecha y libera o reasigna el bloqueo de agenda |
| Gestión de Turnos y Agenda | CU 7 | Eliminar Turno | Sí | No | No | No | Sí | Cancelación del turno; conserva historial y libera disponibilidad |
| Gestión de Turnos y Agenda | CU 8 | Ver Agenda de un Profesional | Sí | Sí | Sí | No | Sí | Visualiza turnos agendados y disponibilidad |
| Gestión Documental | CU 9 | Cargar Documentación | Sí | No | No | No | Sí | Sube archivos PDF de hasta 20 MB al legajo |
| Gestión Documental | CU 10 | Reemplazar Documentación | Sí | No | No | No | Sí | Sustituye la versión conservando referencia e historial |
| Gestión Documental | CU 11 | Eliminar Documentación | Sí | No | No | No | Sí | Marca el documento como eliminado u oculto; conserva auditoría y archivo físico |
| Gestión Documental | CU 12 | Visualizar Documentación | Sí | Sí | Sí | No | Sí | Visualización directa según el alcance del rol |
| Gestión Documental | CU 19 | Descargar Documentación | No | Sí | Sí | No | Sí | Descarga de archivos del legajo asignado o supervisado |
| Gestión de Pagos / ARCA | CU 13 | Registrar Comprobante de Pago | Sí | No | No | No | Sí | Carga manual del PDF descargado desde ARCA; sin integración automática |
| Gestión de Pagos / ARCA | CU 14 | Ver Comprobante de Pago | Sí | No | Sí | Sí | Sí | Consulta y visualización de comprobantes vinculados |
| Gestión de Procesos / Admisión | CU 15 | Admisión de Cliente | No | No | Sí | No | Sí | Evalúa viabilidad, decide admisión y actualiza el proceso |
| Gestión de Procesos / Admisión | CU 15.1 | Registrar Motivo de Rechazo | No | No | Sí | No | Sí | Registra causal de rechazo y actualiza el estado |
| Gestión de Procesos / Admisión | CU 16 | Asignar Profesional | Sí | No | Sí | No | Sí | Asigna según especialidad o servicio y fija honorarios |
| Gestión de Procesos / Admisión | CU 17 | Reasignar Profesional | Sí | No | Sí | No | Sí | Cambia profesional y redefine honorarios |
| Gestión de Procesos / Admisión | CU 18 | Consultar Legajo | No | Sí | Sí | Sí | Sí | Acceso al expediente e historial según rol y asignación |
| Gestión de Procesos / Admisión | CU 20 | Actualizar Estado de Proceso | No | Sí | Sí | No | Sí | Actualiza estados dentro del proceso autorizado |
| Gestión de Procesos / Admisión | CU 29 | Listar y Filtrar Procesos | Sí | Sí | Sí | Sí | Sí | Bandeja filtrable por estado, cliente o profesional asignado |
| Gestión de Procesos / Admisión | CU 30 | Consultar Historial de Estados del Proceso | Sí | Sí | Sí | Sí | Sí | Auditoría de cambios, fechas, motivos y usuario interviniente |
| Seguimiento y Reportes | CU 21 | Registrar Reporte | No | Sí | No | No | Sí | Reporte creado por el profesional asignado |
| Seguimiento y Reportes | CU 22 | Editar Reporte | No | Sí | No | No | Sí | Solo el autor o Administrador; conserva historial |
| Seguimiento y Reportes | CU 23 | Eliminar Reporte | No | Sí | No | No | Sí | Baja lógica por el autor o Administrador; conserva auditoría |
| Seguimiento y Reportes | CU 24 | Consultar Reportes de Proceso | No | Sí | Sí | Sí | Sí | El Profesional consulta sus propios reportes; los demás roles según autorización |
| Administración y Seguridad | CU 25 | Agregar Nuevo Usuario | No | No | No | Sí | Sí | Alta de usuarios y asignación de rol |
| Administración y Seguridad | CU 26 | Modificar Usuario | No | No | No | Sí | Sí | Edición de datos y roles de usuarios |
| Administración y Seguridad | CU 27 | Eliminar Usuario | No | No | No | Sí | Sí | Baja lógica; se verifica que no posea procesos activos |
| Administración y Seguridad | CU 31 | Iniciar y Cerrar Sesión (Login/Logout) | Sí | Sí | Sí | Sí | Sí | Autenticación obligatoria para usuarios internos; el cliente no accede |
| Administración y Seguridad | CU 32 | Cambiar Contraseña / Perfil | Sí | Sí | Sí | Sí | Sí | Actualización de credenciales o perfil por el propio usuario |
| Parámetros del Sistema | CU 33 | Gestionar Servicios (CRUD) | No | No | No | Sí | Sí | Alta, edición y baja lógica de servicios y costos de referencia |
| Parámetros del Sistema | CU 34 | Asignar Especialidad/Servicio a Profesional | No | No | Sí | Sí | Sí | Vincula profesionales con los servicios que pueden atender |
| Comunicaciones y Notificaciones | CU 35 | Consultar Registro de Notificaciones | Sí | No | Sí | Sí | Sí | Auditoría de envíos internos, Email y WhatsApp mediante Brevo |
| Parámetros del Sistema | CU 36 | Gestionar Estados de Proceso | No | No | Sí | No | Sí | CRUD, ordenamiento, activación y desactivación segura de estados |
| Parámetros del Sistema | CU 37 | Gestionar Categorías de Documentos | No | No | Sí | No | Sí | CRUD, activación y baja lógica de categorías o tipos documentales |

## Alineación con el sistema

- La matriz define el alcance funcional y la autorización por rol.
- `database/seeders/RoleSeeder.php` contiene los permisos técnicos de Spatie y debe mantenerse alineado con esta tabla.
- CU 31 y CU 32 son capacidades transversales de autenticación y perfil; se aplican a los usuarios internos según la matriz.
- Cuando se agregue o modifique un caso de uso, primero debe actualizarse la matriz fuente, luego este archivo y finalmente la implementación, OpenSpec y las pruebas relacionadas.
- Si existe una diferencia entre esta copia y Drive, Drive tiene prioridad hasta que el cambio sea acordado y reflejado en el código.

## Procedimiento de actualización

1. Leer la pestaña exacta de la matriz en Drive.
2. Comparar códigos CU, descripción, módulo, notas y las cinco columnas de roles.
3. Actualizar esta copia Markdown.
4. Verificar `RoleSeeder`, rutas, policies, vistas y pruebas afectadas.
5. Ejecutar las pruebas relacionadas y registrar cualquier diferencia pendiente en OpenSpec.
