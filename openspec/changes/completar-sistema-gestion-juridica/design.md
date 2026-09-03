# Diseño funcional de referencia

## Fuente de verdad

La fuente definitiva de alcance y permisos es el documento online **“Aequitas - Matriz de Módulos, Funciones y Permisos por Rol V2”**. OpenSpec y la documentación de `docs/` deben mantenerse sincronizados con esa matriz.

## Actores y límites

| Rol | Alcance |
|---|---|
| Secretario | Opera clientes, comprobantes, documentación y agenda según la matriz; también puede asignar o reasignar profesionales y consultar listados autorizados. |
| Profesional | Consulta y descarga información de procesos asignados, actualiza sus estados y crea, edita, elimina y consulta sus propios reportes. |
| Coordinador | Admite o rechaza procesos, registra motivos, supervisa legajos, consulta documentación, agenda autorizada, reportes y administra estados y especialidades. |
| Directivo | Consulta la operación autorizada y administra usuarios y servicios. |
| Administrador | Superadministrador del sistema; puede realizar todas las operaciones y administrar la configuración operativa. |
| Cliente | Registro administrativo y destinatario eventual de comunicaciones externas; no posee credenciales, permisos ni acceso al sistema. |

## Flujo principal

```text
Alta de cliente
  -> proceso automático de Consultoría en estado pendiente
  -> turno inicial con Coordinador
  -> admisión o rechazo con motivo
  -> asignación o reasignación de profesional y honorarios
  -> solicitud, carga y consulta de documentación
  -> turnos de seguimiento
  -> actualización de estados y reportes
  -> pagos, notificaciones y auditoría
```

## Matriz de permisos

`Administrador` puede ejecutar todos los casos de uso. En la tabla se indican los demás roles autorizados.

| Código | Caso de uso | Roles autorizados además de Administrador |
|---|---|---|
| CU 1 | Registrar Cliente | Secretario |
| CU 2 | Modificar Cliente | Secretario |
| CU 3 | Eliminar Cliente | Secretario |
| CU 4 | Agendar Turno Interno | Secretario |
| CU 4.1 | Agendar Turno de Seguimiento | Secretario |
| CU 5 | Agendar Turno Externo | Secretario |
| CU 6 | Modificar Turno | Secretario |
| CU 6.1 | Modificar Turno Externo | Secretario |
| CU 7 | Eliminar Turno | Secretario |
| CU 8 | Ver Agenda de un Profesional | Secretario, Profesional, Coordinador |
| CU 9 | Cargar Documentación | Secretario |
| CU 10 | Reemplazar Documentación | Secretario |
| CU 11 | Eliminar Documentación | Secretario |
| CU 12 | Visualizar Documentación | Secretario, Profesional, Coordinador |
| CU 13 | Registrar Comprobante de Pago | Secretario |
| CU 14 | Ver Comprobante de Pago | Secretario, Coordinador, Directivo |
| CU 15 | Admisión de Cliente | Coordinador |
| CU 15.1 | Registrar Motivo de Rechazo | Coordinador |
| CU 16 | Asignar Profesional | Secretario, Coordinador |
| CU 17 | Reasignar Profesional | Secretario, Coordinador |
| CU 18 | Consultar Legajo | Profesional asignado, Coordinador, Directivo |
| CU 19 | Descargar Documentación | Profesional asignado, Coordinador |
| CU 20 | Actualizar Estado de Proceso | Profesional asignado, Coordinador |
| CU 21 | Registrar Reporte | Profesional asignado |
| CU 22 | Editar Reporte | Profesional autor |
| CU 23 | Eliminar Reporte | Profesional autor |
| CU 24 | Consultar Reportes de Proceso | Profesional sobre sus reportes, Coordinador, Directivo |
| CU 25 | Agregar Nuevo Usuario | Directivo |
| CU 26 | Modificar Usuario | Directivo |
| CU 27 | Eliminar Usuario | Directivo |
| CU 28 | Listar y Filtrar Clientes | Secretario, Coordinador, Directivo |
| CU 29 | Listar y Filtrar Procesos | Secretario, Profesional, Coordinador, Directivo |
| CU 30 | Consultar Historial de Estados | Secretario, Profesional, Coordinador, Directivo |
| CU 31 | Iniciar y Cerrar Sesión | Secretario, Profesional, Coordinador, Directivo |
| CU 32 | Cambiar Contraseña / Perfil | Secretario, Profesional, Coordinador, Directivo |
| CU 33 | Gestionar Servicios | Directivo |
| CU 34 | Asignar Especialidad/Servicio a Profesional | Coordinador, Directivo |
| CU 35 | Consultar Registro de Notificaciones | Secretario, Coordinador, Directivo |
| CU 36 | Gestionar Estados de Proceso | Coordinador |
| CU 37 | Gestionar Categorías de Documentos | Coordinador |

## Reglas funcionales por módulo

### Clientes y procesos

- Registrar un cliente crea un proceso inicial de Consultoría en estado `pendiente` y un turno inicial con el Coordinador.
- El Coordinador admite o rechaza el proceso y debe registrar el motivo cuando corresponda.
- Secretario, Coordinador y Administrador pueden asignar o reasignar profesionales según servicio o especialidad; la reasignación actualiza honorarios.
- Los listados deben permitir búsqueda, filtros por estado, cliente y profesional asignado, y paginación.
- El historial de estados conserva estado anterior, estado nuevo, fecha, usuario y motivo.
- El Profesional solo puede operar sobre procesos asignados.

### Estados y categorías configurables

- Coordinador y Administrador pueden crear, editar, ordenar, activar y desactivar estados de proceso.
- Coordinador y Administrador pueden crear, editar, activar y eliminar categorías o tipos de documentos.
- No se puede desactivar un estado o categoría que deje datos activos sin una regla de migración o reemplazo.

### Turnos

- Solo Secretario y Administrador crean, modifican y cancelan turnos.
- Secretario, Profesional, Coordinador y Administrador pueden consultar la agenda de un profesional.
- Los turnos externos bloquean la jornada completa y almacenan su detalle.
- La reprogramación libera la disponibilidad anterior y la cancelación deja el turno en estado `cancelado`.
- Los cambios notifican a los involucrados según el evento.

### Documentos

- Secretario y Administrador cargan, reemplazan y eliminan documentación.
- Secretario, Profesional, Coordinador y Administrador pueden visualizarla.
- Profesional asignado, Coordinador y Administrador pueden descargarla.
- Solo se aceptan archivos PDF de hasta 20 MB.

### Reportes

- El Profesional asignado registra reportes con contenido y fecha.
- El autor puede editar o eliminar lógicamente sus reportes; el Administrador puede administrarlos.
- El Profesional consulta sus propios reportes; Coordinador, Directivo y Administrador consultan los reportes autorizados del proceso.

### Pagos y comunicaciones

- Secretario y Administrador registran comprobantes PDF descargados desde ARCA.
- Secretario, Coordinador, Directivo y Administrador pueden consultar comprobantes autorizados.
- El sistema registra canal, mensaje, destinatario, fecha, estado y resultado de notificaciones.
- Los canales son internos, correo y WhatsApp mediante Brevo. Brevo es una integración de salida; ARCA no se integra automáticamente.

### Administración y autenticación

- Directivo y Administrador pueden crear, modificar y eliminar lógicamente usuarios.
- La eliminación de un usuario verifica que no tenga procesos activos.
- Todos los roles internos pueden iniciar y cerrar sesión y modificar su contraseña o perfil.

## Política de conservación y baja

- **Baja lógica:** clientes, usuarios, procesos, servicios, estados, categorías y reportes.
- **Usuarios:** baja lógica condicionada a que no tengan procesos activos.
- **Turnos:** no se eliminan físicamente; se cancelan y conservan su historial.
- **Documentos y comprobantes:** se marcan como eliminados u ocultos, pero se conserva su referencia, historial y auditoría; no se elimina automáticamente el archivo físico.
- **Notificaciones:** se conservan como registro auditable y no se eliminan de la operación histórica.
- Las consultas operativas excluyen registros dados de baja, salvo vistas explícitas de auditoría o restauración.

## Requisitos no funcionales

- Autenticación obligatoria para usuarios internos.
- Autorización comprobada en interfaz y servidor.
- Auditoría de cambios sensibles y transiciones de estado.
- Validación de tipo y tamaño de archivos.
- Persistencia consistente de operaciones relacionadas.
