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

> **Implementación completa CU25-27, CU34:** ver `docs/gestion-usuarios-roles-servicios.md` para detalle de altas/modificaciones/baja lógica, validación `email único`, roles desde BD, password inicial `1234`, búsqueda por `nombre/email/DNI`, relación N:M `user_servicios` (múltiples servicios por profesional), `UserPolicy` con bloqueo por procesos activos, `StoreUserRequest`/`UpdateUserRequest`, `UserController` + `Livewire/Usuarios/Index` (Blade+Alpine+Tailwind), rutas `users.*` protegidas, registro público `/register` deshabilitado (solo CU25), y pruebas `UserManagementTest`/`UsuarioIndexTest` (82 tests).

El alta la realizan exclusivamente Directivo/Administrador vía `POST /usuarios` (CU25) con roles traídos de `roles` (Spatie). La modificación valida unicidad con `Rule::unique->ignore` sin duplicar. La baja es lógica (`SoftDeletes`, `deleted_at`) y verifica que el usuario no tenga procesos en `pendiente/admitido/iniciado/en_proceso/en_espera`; sólo `finalizado/rechazado` permite desactivar, conservando trazabilidad (`withTrashed`). El perfil propio (`DELETE /profile`) reutiliza la misma regla de negocio y aborta `403` si tiene procesos activos.

La asociación de servicios/especialidades es N:M vía tabla intermedia `user_servicios` (PK compuesta `user_id,servicio_id` evita duplicados, `sync()` idempotente). Sólo profesionales (`hasRole Profesional`) pueden tener servicios asignados; Coordinador, Directivo y Administrador están autorizados por `asignar_especialidad_servicio`.

### Cliente

Persona cuyos datos se administran en el sistema. Su alta crea un proceso inicial de Consultoría y un turno inicial con el Coordinador.

> **Implementación CRUD:** ver `docs/crud-clientes.md` para detalle de modelo `Cliente`, migración reversible, factory/seeder, requests `StoreClienteRequest`/`UpdateClienteRequest`, policy `ClientePolicy` (Administrador/Secretario), controller resource, rutas `clientes.*`, vistas Blade `resources/views/clientes/*` y pruebas `ClienteTest`. Cumple estándar `crud` mínimo; interfaz Blade + Tailwind responsive, extensible a Livewire/Alpine.

### Servicio

Área o servicio ofrecido por el estudio, con un costo de referencia. Directivo y Administrador pueden gestionarlo (CRUD en `app/Livewire/Servicios/Index.php` y `ServicioController` con `gestionar_servicios`). Las especialidades o servicios atendibles por cada profesional son administradas por Coordinador, Directivo y Administrador vía relación N:M `user_servicios` (`User::servicios()` / `Servicio::usuarios()`), con UI híbrida `livewire/usuarios/index.blade.php` + `UserController::updateServicios` y validación `exists:servicios,id`.

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
- Directivo y Administrador pueden administrar usuarios (CU25-27: `listar_usuarios`, `agregar_usuarios`, `modificar_usuarios`, `eliminar_usuarios`, `editar_roles`); el registro público `/register` está deshabilitado.
- Coordinador, Directivo y Administrador pueden administrar o asignar especialidades (CU34: `asignar_especialidad_servicio` sólo a `Profesional`, vía `user_servicios` con PK compuesta).
- Coordinador y Administrador gestionan estados de procesos y categorías de documentos.
- Las autorizaciones se aplican en la interfaz (`@can('manageRoles',$user)`/`@can('delete',$user)`), las rutas (`permission:*`), las políticas (`UserPolicy::delete` verifica procesos activos) y las consultas (`visibleTo`).

> Detalle de matriz: `RoleSeeder.php:15` y `docs/gestion-usuarios-roles-servicios.md:3`.

## Estados de proceso

Los estados iniciales contemplan `pendiente`, `admitido`, `iniciado`, `en_proceso`, `finalizado`, `en_espera` y `rechazado`. Coordinador y Administrador pueden crear, editar, ordenar, activar y desactivar estados, siempre que no queden datos activos sin una transición válida.

El proceso almacena la clave estable del estado y el nombre visible proviene del catálogo. El historial conserva también estados inactivos para mantener la trazabilidad.

## Política de conservación y baja

- Clientes, usuarios, procesos, servicios, estados, categorías y reportes utilizan baja lógica (`SoftDeletes`, `deleted_at`).
- La baja de un usuario exige verificar que no tenga procesos activos (`estado not in [finalizado, rechazado]` en `procesosComoProfesional`/`procesosComoCoordinador`); la misma regla se aplica al borrado del perfil propio (`ProfileController::destroy` aborta `403`).
- Los turnos pasan a estado `cancelado` y conservan su historial.
- Documentos y comprobantes se marcan como eliminados u ocultos, conservando referencia, historial y auditoría.
- La baja de documentos o comprobantes no elimina automáticamente el archivo físico.
- Las notificaciones se conservan como registro auditable.
- Las consultas operativas excluyen registros dados de baja (`User::query()` ignora `deleted_at`), salvo vistas de auditoría o restauración (`withTrashed`).

> Implementación: `UserPolicy.php:30` `forceDelete false`, `UserController::destroy`/`Livewire::deleteUser`, `ProfileController.php:43` centralizado.

## Notificaciones automáticas

Se notifican, según el evento y los destinatarios autorizados, el alta de cliente, admisión, rechazo, asignación o reasignación de profesional, creación, modificación y cancelación de turnos, y otros cambios relevantes del proceso.
