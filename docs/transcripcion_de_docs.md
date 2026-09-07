# Trabajo Práctico Integrador
### Tecnicatura Superior de Desarrollo de Software

**Integrantes:** Flores Lucas, Espinola Yamila, Gonzalez Lucia  
**Profesor:** Villalba Carlos
**Instituto:** Superior Roque Gonzalez  
**Año:** 2026

---

## 1. Fuente funcional vigente

La matriz online **“Aequitas - Matriz de Módulos, Funciones y Permisos por Rol V2”** es la fuente definitiva de casos de uso, alcance y permisos. Este documento transcribe sus reglas funcionales y registra las decisiones complementarias aprobadas en OpenSpec.

El sistema es una aplicación web interna para centralizar clientes, procesos jurídicos, turnos, documentación, reportes, comprobantes, usuarios, servicios y notificaciones del estudio.

La copia tabular exacta y sincronizada de la matriz está en [aequitas-matriz-modulos-funciones-permisos-v2.md](aequitas-matriz-modulos-funciones-permisos-v2.md). Este documento conserva la transcripción funcional ampliada.

El cliente se registra como dato administrativo. No inicia sesión, no tiene credenciales, no posee permisos y no accede al sistema.

## 2. Actores

- **Secretario:** gestiona clientes, agenda autorizada, documentación, comprobantes y asignaciones autorizadas.
- **Profesional:** trabaja sobre procesos asignados, consulta y descarga su documentación, actualiza estados y administra sus reportes.
- **Coordinador:** evalúa y supervisa procesos, registra admisiones o rechazos, asigna profesionales y administra estados o especialidades autorizadas.
- **Directivo:** consulta la operación autorizada y administra usuarios y servicios.
- **Administrador:** superadministrador con capacidad para realizar todas las operaciones.
- **Cliente:** registro administrativo sin acceso al sistema.

## 3. Flujo principal

1. El Secretario o Administrador registra al cliente.
2. El sistema crea automáticamente un proceso de Consultoría en estado `pendiente`.
3. El sistema crea un turno inicial con el Coordinador.
4. El Coordinador admite o rechaza el proceso y registra el motivo si corresponde.
5. Secretario, Coordinador o Administrador asignan o reasignan el profesional según servicio o especialidad.
6. Se cargan los documentos necesarios en el legajo.
7. Se agendan turnos de seguimiento.
8. El Profesional actualiza el estado y registra reportes.
9. Se cargan comprobantes PDF descargados desde ARCA.
10. El sistema registra y envía notificaciones según el evento y el destinatario.

## 4. Requisitos funcionales

| Código | Módulo | Caso de uso | Roles autorizados además del Administrador |
|---|---|---|---|
| CU 1 | Gestión de Clientes | Registrar Cliente | Secretario |
| CU 2 | Gestión de Clientes | Modificar Cliente | Secretario |
| CU 3 | Gestión de Clientes | Eliminar Cliente | Secretario |
| CU 4 | Gestión de Turnos y Agenda | Agendar Turno Interno | Secretario |
| CU 4.1 | Gestión de Turnos y Agenda | Agendar Turno de Seguimiento | Secretario |
| CU 5 | Gestión de Turnos y Agenda | Agendar Turno Externo | Secretario |
| CU 6 | Gestión de Turnos y Agenda | Modificar Turno | Secretario |
| CU 6.1 | Gestión de Turnos y Agenda | Modificar Turno Externo | Secretario |
| CU 7 | Gestión de Turnos y Agenda | Eliminar Turno | Secretario |
| CU 8 | Gestión de Turnos y Agenda | Ver Agenda de un Profesional | Secretario, Profesional, Coordinador |
| CU 9 | Gestión Documental | Cargar Documentación | Secretario |
| CU 10 | Gestión Documental | Reemplazar Documentación | Secretario |
| CU 11 | Gestión Documental | Eliminar Documentación | Secretario |
| CU 12 | Gestión Documental | Visualizar Documentación | Secretario, Profesional, Coordinador |
| CU 19 | Gestión Documental | Descargar Documentación | Profesional asignado, Coordinador |
| CU 13 | Gestión de Pagos / ARCA | Registrar Comprobante de Pago | Secretario |
| CU 14 | Gestión de Pagos / ARCA | Ver Comprobante de Pago | Secretario, Coordinador, Directivo |
| CU 15 | Gestión de Procesos / Admisión | Admisión de Cliente | Coordinador |
| CU 15.1 | Gestión de Procesos / Admisión | Registrar Motivo de Rechazo | Coordinador |
| CU 16 | Gestión de Procesos / Admisión | Asignar Profesional | Secretario, Coordinador |
| CU 17 | Gestión de Procesos / Admisión | Reasignar Profesional | Secretario, Coordinador |
| CU 18 | Gestión de Procesos / Admisión | Consultar Legajo | Profesional asignado, Coordinador, Directivo |
| CU 20 | Gestión de Procesos / Admisión | Actualizar Estado de Proceso | Profesional asignado, Coordinador |
| CU 28 | Gestión de Clientes | Listar y Filtrar Clientes | Secretario, Coordinador, Directivo |
| CU 29 | Gestión de Procesos / Admisión | Listar y Filtrar Procesos | Secretario, Profesional, Coordinador, Directivo |
| CU 30 | Gestión de Procesos / Admisión | Consultar Historial de Estados | Secretario, Profesional, Coordinador, Directivo |
| CU 21 | Seguimiento y Reportes | Registrar Reporte | Profesional asignado |
| CU 22 | Seguimiento y Reportes | Editar Reporte | Profesional autor |
| CU 23 | Seguimiento y Reportes | Eliminar Reporte | Profesional autor |
| CU 24 | Seguimiento y Reportes | Consultar Reportes de Proceso | Profesional sobre sus reportes, Coordinador, Directivo |
| CU 25 | Administración y Seguridad | Agregar Nuevo Usuario | Directivo |
| CU 26 | Administración y Seguridad | Modificar Usuario | Directivo |
| CU 27 | Administración y Seguridad | Eliminar Usuario | Directivo |
| CU 31 | Administración y Seguridad | Iniciar y Cerrar Sesión | Secretario, Profesional, Coordinador, Directivo |
| CU 32 | Administración y Seguridad | Cambiar Contraseña / Perfil | Secretario, Profesional, Coordinador, Directivo |
| CU 33 | Parámetros del Sistema | Gestionar Servicios | Directivo |
| CU 34 | Parámetros del Sistema | Asignar Especialidad/Servicio a Profesional | Coordinador, Directivo |
| CU 35 | Comunicaciones y Notificaciones | Consultar Registro de Notificaciones | Secretario, Coordinador, Directivo |
| CU 36 | Parámetros del Sistema | Gestionar Estados de Proceso | Coordinador |
| CU 37 | Parámetros del Sistema | Gestionar Categorías de Documentos | Coordinador |

El Administrador está autorizado en todos los casos de uso, por su carácter de superadministrador.

## 5. Reglas funcionales complementarias

### Clientes y procesos

- El alta de cliente debe crear el proceso inicial de Consultoría y el turno inicial con el Coordinador.
- Los listados deben admitir búsqueda, filtros por estado, cliente y profesional, y paginación.
- La asignación o reasignación puede actualizar el profesional, servicio y honorarios.
- El historial de estados conserva estado anterior, estado nuevo, fecha, usuario y motivo.

### Estados de procesos

Coordinador y Administrador pueden crear, editar, ordenar, activar y desactivar estados. No se puede desactivar un estado que deje datos activos sin una transición o reemplazo válido.

### Categorías de documentos

Coordinador y Administrador pueden crear, editar, activar y eliminar categorías o tipos de documentos. No se puede eliminar o desactivar una categoría utilizada por datos activos sin una regla de reemplazo.

### Turnos

- Solo Secretario y Administrador crean, modifican y cancelan turnos.
- Los turnos externos bloquean la jornada completa y guardan su detalle.
- La reprogramación libera la disponibilidad anterior.
- La cancelación conserva el registro con estado `cancelado`.
- Los cambios de agenda notifican a los involucrados.

### Documentación

- Solo se aceptan archivos PDF de hasta 20 MB.
- La carga, sustitución y eliminación lógica corresponden al Secretario y Administrador.
- La visualización corresponde a Secretario, Profesional, Coordinador y Administrador.
- La descarga corresponde al Profesional asignado, Coordinador y Administrador.

### Reportes

- El Profesional asignado registra reportes con contenido y fecha.
- El autor puede editar o eliminar lógicamente sus reportes.
- El Profesional consulta sus propios reportes.
- Coordinador, Directivo y Administrador consultan los reportes autorizados del proceso.

### Pagos

El comprobante es un PDF descargado manualmente desde ARCA y almacenado en el sistema. No existe integración automática con ARCA.

### Notificaciones

El sistema registra canal, mensaje, destinatario, fecha, estado y resultado. Los canales son internos, correo y WhatsApp mediante Brevo.

## 6. Administración y autenticación

- Directivo y Administrador pueden agregar, modificar y eliminar lógicamente usuarios.
- La baja de un usuario requiere verificar que no tenga procesos activos.
- Todos los roles internos pueden iniciar y cerrar sesión y modificar su contraseña o perfil.
- El cliente no puede iniciar sesión ni ser sujeto de permisos.

## 7. Política de conservación y baja

- Clientes, usuarios, procesos, servicios, estados, categorías y reportes utilizan baja lógica.
- Los turnos no se eliminan físicamente: pasan a estado `cancelado` y conservan su historial.
- Documentos y comprobantes se marcan como eliminados u ocultos, pero conservan referencia, historial y auditoría.
- La baja de documentos o comprobantes no elimina automáticamente el archivo físico.
- Las notificaciones se conservan como registros auditables.
- Las consultas operativas excluyen registros dados de baja, salvo vistas de auditoría o restauración.

## 8. Requisitos no funcionales

- Autenticación obligatoria para usuarios internos.
- Autorización aplicada en interfaz, rutas, políticas y consultas.
- Auditoría de cambios sensibles y transiciones de estado.
- Validación de tipo y tamaño de archivos.
- Persistencia consistente de operaciones relacionadas.
- Acceso mediante navegador web.

## 9. Modelo de dominio

Las entidades principales son Usuario, Cliente, Servicio, Proceso, Turno, Documento, Categoría de Documento, Reporte, Comprobante de Pago, Estado de Proceso y Notificación.

- Cliente tiene procesos, turnos, comprobantes y notificaciones.
- Proceso pertenece a un cliente y servicio, y puede tener coordinador, profesional, turnos, documentos y reportes.
- Profesional y Coordinador son usuarios con roles y permisos diferenciados.
- Servicio se relaciona con procesos y con las especialidades asignadas a profesionales.
- Los registros históricos mantienen sus relaciones aunque la entidad operativa se encuentre dada de baja.
