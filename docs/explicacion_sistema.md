# Explicación del Sistema de Gestión Jurídica

## Fuente funcional

La fuente definitiva de casos de uso y permisos es la matriz online **“Aequitas - Matriz de Módulos, Funciones y Permisos por Rol V2”**. OpenSpec y esta documentación deben mantenerse alineados con ella.

## Actores del sistema

- **Secretario:** registra y modifica clientes, gestiona documentación y comprobantes, administra la agenda autorizada y puede asignar o reasignar profesionales.
- **Profesional:** consulta y descarga la información de sus procesos asignados, actualiza estados y administra sus propios reportes.
- **Coordinador:** evalúa procesos, admite o rechaza casos, registra motivos, supervisa legajos, consulta reportes y administra estados y especialidades.
- **Directivo:** consulta la operación autorizada y administra usuarios y servicios.
- **Administrador:** superadministrador; puede realizar todas las operaciones del sistema.
- **Cliente:** dato administrativo y posible destinatario de comunicaciones externas. No tiene credenciales, permisos ni acceso al sistema.

## Entidades principales

### Usuario

Representa a un empleado autenticable. Sus permisos dependen del rol asignado. Los roles son Secretario, Profesional, Coordinador, Directivo y Administrador.

### Cliente

Persona cuyos datos se administran en el sistema. Su alta crea un proceso inicial de Consultoría y un turno inicial con el Coordinador.

> **Implementación CRUD:** ver `docs/crud-clientes.md` para detalle de modelo `Cliente`, migración reversible, factory/seeder, requests `StoreClienteRequest`/`UpdateClienteRequest`, policy `ClientePolicy` (Administrador/Secretario), controller resource, rutas `clientes.*`, vistas Blade `resources/views/clientes/*` y pruebas `ClienteTest`. Cumple estándar `crud` mínimo; interfaz Blade + Tailwind responsive, extensible a Livewire/Alpine.

### Servicio

Área o servicio ofrecido por el estudio, con un costo de referencia. Directivo y Administrador pueden gestionarlo. Las especialidades o servicios atendibles por cada profesional son administradas por Coordinador, Directivo y Administrador.

### Proceso

Caso asociado a un cliente y un servicio. Conserva coordinador, profesional, fechas, tipo, descripción, honorarios, estado y motivo de rechazo.

El estado se resuelve desde un catálogo configurable y cada transición queda registrada con estado anterior, estado nuevo, fecha, usuario y motivo.

### Turno

Cita interna, de seguimiento o externa. Los turnos externos bloquean la jornada completa del profesional. Se cancelan, pero no se eliminan físicamente.

### Documento

Archivo PDF de hasta 20 MB asociado a un proceso. Se puede visualizar, descargar, reemplazar u ocultar según los permisos del rol.

### Reporte

Registro de avance, reunión o actuación asociado a un proceso. El profesional autor puede crearlo, editarlo, eliminarlo lógicamente y consultar sus propios reportes.

### Comprobante de pago

PDF descargado desde ARCA y cargado manualmente al sistema. El sistema no realiza una integración automática con ARCA.

### Notificación

Registro de una comunicación interna o externa. Los canales externos son correo y WhatsApp mediante Brevo. Se conservan canal, mensaje, destinatario, fecha, estado y resultado.

## Flujo principal

1. El Secretario o Administrador registra al cliente.
2. El sistema crea un proceso de Consultoría en estado `pendiente`.
3. El sistema crea un turno inicial con el Coordinador.
4. El Coordinador admite o rechaza el proceso y registra el motivo si corresponde.
5. Secretario, Coordinador o Administrador asignan un profesional compatible.
6. Se solicitan y cargan los documentos necesarios.
7. Se agendan turnos de seguimiento.
8. El Profesional actualiza estados y registra reportes.
9. Se cargan comprobantes de pago y se envían notificaciones.
10. Los cambios sensibles quedan auditados.

## Reglas de permisos

- Solo Secretario y Administrador pueden crear, modificar y cancelar turnos.
- Profesional, Coordinador, Directivo y Administrador pueden consultar el legajo según su alcance.
- Solo el Profesional asignado, Coordinador y Administrador pueden descargar documentación.
- El Profesional solo opera sobre procesos que tiene asignados.
- Directivo y Administrador pueden administrar usuarios.
- Coordinador, Directivo y Administrador pueden administrar o asignar especialidades según el caso.
- Coordinador y Administrador gestionan estados de procesos y categorías de documentos.
- Las autorizaciones se aplican en la interfaz, las rutas, las políticas y las consultas.

## Estados de proceso

Los estados iniciales contemplan `pendiente`, `admitido`, `iniciado`, `en_proceso`, `finalizado`, `en_espera` y `rechazado`. Coordinador y Administrador pueden crear, editar, ordenar, activar y desactivar estados, siempre que no queden datos activos sin una transición válida.

El proceso almacena la clave estable del estado y el nombre visible proviene del catálogo. El historial conserva también estados inactivos para mantener la trazabilidad.

## Política de conservación y baja

- Clientes, usuarios, procesos, servicios, estados, categorías y reportes utilizan baja lógica.
- La baja de un usuario exige verificar que no tenga procesos activos.
- Los turnos pasan a estado `cancelado` y conservan su historial.
- Documentos y comprobantes se marcan como eliminados u ocultos, conservando referencia, historial y auditoría.
- La baja de documentos o comprobantes no elimina automáticamente el archivo físico.
- Las notificaciones se conservan como registro auditable.
- Las consultas operativas excluyen registros dados de baja, excepto en vistas de auditoría o restauración.

## Notificaciones automáticas

Se notifican, según el evento y los destinatarios autorizados, el alta de cliente, admisión, rechazo, asignación o reasignación de profesional, creación, modificación y cancelación de turnos, y otros cambios relevantes del proceso.
