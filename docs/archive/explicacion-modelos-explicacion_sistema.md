# Explicación del Sistema de Gestión Jurídica

## Introducción

Este sistema es una aplicación web diseñada para administrar un estudio jurídico que ofrece servicios profesionales en las áreas Civil, Comercial y de Familia. Su propósito es centralizar y digitalizar todas las operaciones del estudio: desde el registro de clientes hasta el seguimiento de procesos legales, la gestión de turnos y la documentación asociada.

---

## Actores del Sistema

El sistema cuenta con cinco roles autenticables diferenciados y un cliente administrativo sin acceso:

- **Secretario**: Es el encargado del registro inicial de clientes, la carga de documentación, el agendamiento de turnos y la gestión de comprobantes de pago.
- **Cliente**: Es la persona que solicita los servicios del estudio. No tiene credenciales, permisos ni acceso al sistema.
- **Profesional**: Es el abogado o especialista que atiende los procesos asignados. Puede consultar y descargar su documentación, actualizar estados y administrar sus propios reportes.
- **Coordinador**: Es quien evalúa la viabilidad de los casos, decide si admitir o rechazar un cliente, asigna profesionales y supervisa el avance de los procesos. También administra estados y especialidades autorizadas.
- **Directivo**: Consulta la operación autorizada y puede administrar usuarios y servicios.
- **Administrador**: Es el superadministrador y puede realizar todas las operaciones del sistema.

---

## Entidades del Sistema

### Usuario

Representa a un empleado autenticable. Puede ser Secretario, Profesional, Coordinador, Directivo o Administrador. Opcionalmente, puede tener datos profesionales asociados como DNI, teléfono, domicilio, fecha de nacimiento y fecha de ingreso al estudio. Los usuarios tienen roles y permisos que controlan qué acciones pueden realizar.

### Cliente

Es la persona física que concurre al estudio para solicitar servicios legales. Cada cliente tiene un nombre, apellido, DNI (único), teléfono, correo electrónico, domicilio y fecha de nacimiento. Un cliente puede tener uno o varios procesos abiertos simultáneamente.

### Servicio

Define los tipos de trabajo que ofrece el estudio, como Consultoría, y tiene un costo asociado. Cada servicio está relacionado con uno o más procesos.

### Proceso

Representa un caso legal abierto para un cliente. Cada proceso tiene un nombre, descripción, fecha de inicio, tipo (Civil, Comercial o Familia) y un estado que refleja su situación actual (pendiente, admitido, iniciado, en proceso, finalizado, en espera o rechazado). Si un proceso es rechazado, se registra el motivo.

Un proceso siempre pertenece a un cliente y a un servicio. Además, tiene un profesional asignado (el abogado que lo atiende) y opcionalmente un coordinador que supervisa el caso.

### Turno

Representa una cita programada entre un cliente y un profesional. Cada turno tiene fecha, hora y un tipo que puede ser consulta inicial, seguimiento o externo (como una audiencia judicial). Los turnos externos bloquean la disponibilidad del profesional para todo el día.

Los turnos pueden estar vinculados a un proceso específico (como un seguimiento del caso) o no estar vinculados (como una consulta inicial).

### Documento

Es un archivo digital (formato PDF, máximo 20MB) asociado a un proceso. Tiene un nombre, un tipo de documento y una ruta de almacenamiento en el servidor.

### Reporte

Es un acta o registro generado por un profesional que documenta reuniones o avances en un proceso. Cada reporte tiene contenido de texto y una fecha de creación.

### Comprobante de Pago

Es el archivo PDF del comprobante de pago descargado desde ARCA (la autoridad tributaria), asociado a un cliente. Incluye la fecha de subida y una descripción opcional.

### Notificación

Es un mensaje automático enviado por el sistema para informar a clientes o usuarios sobre eventos importantes, como la confirmación de un turno o el rechazo de un caso. Las notificaciones se envían por email o WhatsApp y tienen un estado que indica si fueron enviadas correctamente o si fallaron.

---

## Flujos Principales del Sistema

### Registro de un Nuevo Cliente

Cuando un cliente se presenta por primera vez en el estudio, el Secretario o Administrador registra sus datos personales en el sistema. Automáticamente se crea un proceso con el servicio "Consultoría" en estado pendiente y un turno inicial con el Coordinador.

### Admisión del Cliente

El coordinador abre el legajo electrónico del cliente, evalúa la viabilidad del caso y decide si admitirlo o rechazarlo. Si lo admite, el proceso cambia a estado "admitido" y se solicita la asignación de un profesional. Si se rechaza, se registra el motivo y el cliente es notificado.

### Asignación de Profesional

El Secretario, Coordinador o Administrador selecciona un profesional disponible según su especialidad y el tipo de servicio requerido. El profesional asignado recibe una notificación y el Secretario también es informado para coordinar los siguientes pasos.

### Solicitud de Documentación

Una vez admitido el cliente, el Secretario o Administrador solicita y carga los documentos necesarios según el tipo de servicio. Estos documentos son PDF de hasta 20 MB y quedan disponibles según los permisos del rol.

### Agendamiento de Turnos con el Profesional

El Secretario o Administrador agenda un nuevo turno entre el cliente y el profesional designado. Los turnos pueden ser internos (consultas en el estudio) o externos (audiencias judiciales). Los turnos externos bloquean la disponibilidad del profesional para todo el día.

### Seguimiento del Proceso

El profesional asignado puede acceder al legajo de sus procesos y consultar su documentación. A medida que avanza el caso, actualiza el estado del proceso (iniciado, en proceso, finalizado o en espera). Cada actualización queda registrada en el historial del proceso.

### Registro de Reportes

El profesional puede registrar actas de reuniones o acciones realizadas durante el seguimiento del caso. Estos reportes quedan visibles para el coordinador y el directivo, permitiendo un control efectivo del avance de cada proceso.

### Gestión de Pagos

El Secretario o Administrador carga el comprobante de pago PDF descargado manualmente desde ARCA y lo asocia al cliente correspondiente. El sistema no integra automáticamente con ARCA.

### Modificación y Cancelación de Turnos

Si un turno debe modificarse, el Secretario o Administrador puede cambiar la fecha y hora. El sistema verifica la disponibilidad del profesional y notifica a todos los involucrados. Si se cancela un turno externo, se desbloquea la disponibilidad del profesional y se conserva el turno con estado cancelado.

---

## Modelo de Datos: Relaciones entre Entidades

La siguiente descripción explica cómo se relacionan las entidades entre sí:

- Un **Usuario** puede tener muchos **Turnos** (como profesional), muchos **Reportes** (como autor), muchos **Procesos** como profesional asignado y muchos **Procesos** como coordinador.
- Un **Cliente** puede tener muchos **Procesos**, muchos **Turnos**, muchos **Comprobantes de Pago** y muchas **Notificaciones**.
- Un **Servicio** puede estar asociado a muchos **Procesos**.
- Un **Proceso** puede tener muchos **Turnos** (seguimientos), muchos **Documentos** y muchos **Reportes**.
- Una **Notificación** puede estar vinculada a un **Usuario** y/o a un **Cliente**.

El sistema aplica una política mixta. Clientes, usuarios, procesos, servicios, estados, categorías y reportes utilizan eliminación lógica. Los turnos se cancelan y conservan su historial. Documentos y comprobantes se ocultan o marcan como eliminados sin borrar automáticamente el archivo físico. Las notificaciones se conservan como registros auditables.

---

## Estados de un Proceso

Un proceso atraviesa diferentes estados a lo largo de su ciclo de vida:

1. **Pendiente**: El cliente fue registrado pero aún no fue evaluado por el coordinador.
2. **Admitido**: El coordinador evaluó el caso y decidió aceptarlo.
3. **Iniciado**: El profesional comenzó a trabajar en el caso.
4. **En Proceso**: El caso está activo y en desarrollo.
5. **Finalizado**: El caso fue completado.
6. **En Espera**: El caso está pausado, esperando alguna acción externa.
7. **Rechazado**: El coordinador decidió no atender el caso. Se registra el motivo del rechazo.

---

## Notificaciones Automáticas

El sistema envía notificaciones automáticas en los siguientes momentos:

- Cuando se agenda un turno, tanto el cliente como el profesional reciben la confirmación.
- Cuando el coordinador admite o rechaza un caso, el cliente es notificado con el resultado y, en caso de rechazo, el motivo correspondiente.
- Cuando se asigna un profesional a un proceso, tanto el profesional como el Secretario reciben la información.
- Cuando se modifica o cancela un turno, los involucrados son notificados.

Las notificaciones se envían internamente, por email y por WhatsApp utilizando el servicio externo Brevo.

---

## Resumen de Funcionalidades por Rol

| Rol             | Funcionalidades principales                                                                                 |
| --------------- | ----------------------------------------------------------------------------------------------------------- |
| **Secretario**  | Registrar/modificar/eliminar clientes, gestionar agenda autorizada, documentación, comprobantes y asignaciones |
| **Coordinador** | Evaluar casos, admitir/rechazar, asignar/reasignar profesionales, consultar reportes y administrar estados |
| **Profesional** | Consultar procesos asignados, visualizar/descargar documentación, actualizar estados y administrar reportes propios |
| **Directivo**   | Gestionar usuarios y servicios, consultar legajos, comprobantes y reportes autorizados |
| **Administrador** | Ejecutar todas las operaciones y administrar la configuración del sistema |
