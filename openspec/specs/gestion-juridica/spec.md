# Especificación del sistema de gestión jurídica

## Fuente de verdad

La matriz online **“Aequitas - Matriz de Módulos, Funciones y Permisos por Rol V2”** es la fuente definitiva de alcance, casos de uso y permisos. Esta especificación documenta sus reglas confirmadas y las decisiones complementarias aprobadas.

## Usuarios autenticables

- El sistema debe ser utilizado únicamente por empleados del estudio jurídico.
- Los roles autenticables son Secretario, Profesional, Coordinador, Directivo y Administrador.
- El Administrador es un superadministrador con capacidad para realizar todas las operaciones.
- El Directivo puede administrar usuarios y servicios, además de consultar la información autorizada.
- El cliente es un dato administrativo sin credenciales, permisos, login ni acceso al sistema.

## Clientes y procesos

- El sistema debe permitir registrar, modificar y dar de baja lógicamente clientes.
- Al registrar un cliente debe crear un proceso inicial de Consultoría en estado `pendiente` y un turno inicial con el Coordinador.
- El proceso debe conservar cliente, servicio, coordinador, profesional, fechas, tipo, descripción, honorarios y motivo de rechazo.
- El Coordinador debe poder admitir o rechazar el proceso y registrar el motivo de rechazo.
- Secretario, Coordinador y Administrador deben poder asignar o reasignar profesionales según servicio o especialidad.
- Los listados de clientes y procesos deben permitir búsqueda, filtros y paginación.
- El historial de estados debe conservar estado anterior, estado nuevo, fecha, usuario y motivo.
- El Profesional solo debe consultar y operar procesos que tenga asignados.

## Estados y categorías

- Coordinador y Administrador deben poder crear, editar, ordenar, activar y desactivar estados de procesos.
- Coordinador y Administrador deben poder administrar categorías o tipos de documentos.
- El sistema debe impedir desactivar estados o categorías que dejen datos activos sin una transición válida.

## Turnos

- Solo Secretario y Administrador pueden crear, modificar y cancelar turnos.
- Secretario, Profesional, Coordinador y Administrador pueden consultar la agenda de un profesional.
- El sistema debe impedir conflictos de agenda.
- Un turno externo debe bloquear la jornada completa y guardar su detalle.
- La reprogramación debe liberar la disponibilidad anterior.
- La cancelación debe conservar el turno con estado `cancelado` y liberar la disponibilidad.
- Los cambios de turno deben generar notificaciones a los involucrados.

## Documentos

- Secretario y Administrador pueden cargar, reemplazar y eliminar documentación.
- Secretario, Profesional, Coordinador y Administrador pueden visualizar documentación.
- Profesional asignado, Coordinador y Administrador pueden descargar documentación.
- Solo se deben aceptar archivos PDF de hasta 20 MB.

## Reportes

- El Profesional asignado debe poder registrar reportes con contenido y fecha.
- El Profesional autor puede editar o eliminar lógicamente sus reportes.
- El Profesional debe poder consultar sus propios reportes.
- Coordinador, Directivo y Administrador deben poder consultar reportes autorizados del proceso.
- El Administrador debe poder administrar reportes.

## Pagos y notificaciones

- Secretario y Administrador deben poder registrar comprobantes PDF descargados desde ARCA.
- El sistema no debe integrar automáticamente con ARCA.
- Secretario, Coordinador, Directivo y Administrador deben poder consultar comprobantes autorizados.
- El sistema debe registrar canal, mensaje, destinatario, fecha, estado y resultado de cada notificación.
- Las notificaciones deben poder enviarse internamente, por correo y por WhatsApp mediante Brevo.

## Administración y autenticación

- Directivo y Administrador deben poder agregar, modificar y eliminar lógicamente usuarios.
- La baja lógica de un usuario debe verificar que no tenga procesos activos.
- Todos los roles internos deben poder iniciar y cerrar sesión y cambiar su contraseña o perfil.
- Los permisos deben validarse tanto en la interfaz como en el servidor.

## Estándar de interfaz

- Las vistas deben utilizar Tailwind CSS para estilos, layout y comportamiento responsive.
- Las interacciones locales que no requieren servidor deben resolverse con Alpine.js.
- Las vistas interactivas que consultan o modifican información deben utilizar Livewire, conservando autorización y validación en el servidor.
- Las vistas estáticas o simples pueden utilizar Blade + Tailwind sin Livewire.
- El estándar no exige convertir la aplicación MVC en una arquitectura basada en API.

## Interfaz de gestión de servicios

- La gestión de servicios debe estar disponible en `/servicios` mediante una interfaz híbrida de Blade, Livewire, Alpine.js y Tailwind CSS.
- Alpine.js debe controlar localmente la visibilidad y las transiciones de los modales de alta, edición y confirmación de baja.
- Livewire debe ejecutar en el servidor la autorización, validación, creación, actualización y baja lógica de servicios.
- La aplicación debe conservar la versión tradicional del CRUD en `/servicios-viejo` como referencia para el equipo.
- La adopción de esta interfaz no requiere separar el módulo en una API ni modificar las reglas de permisos existentes.

## Conservación y eliminación

- Clientes, usuarios, procesos, servicios, estados, categorías y reportes deben admitir baja lógica.
- Los turnos no deben eliminarse físicamente; deben cancelarse conservando su historial.
- Documentos y comprobantes deben marcarse como eliminados u ocultos, conservando referencia, historial y auditoría.
- La eliminación lógica de documentos o comprobantes no debe borrar automáticamente el archivo físico.
- Las notificaciones deben conservarse como registro auditable.
- Las consultas operativas deben excluir registros dados de baja, salvo vistas explícitas de auditoría o restauración.

## Criterio de completitud

La aplicación se considerará alineada cuando los 40 casos de uso de la matriz puedan ejecutarse desde la interfaz con autorización por rol, alcance del Profesional, persistencia consistente, estados y categorías configurables, archivos validados, conservación histórica, notificaciones y pruebas automatizadas de los casos principales.
