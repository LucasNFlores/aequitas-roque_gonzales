# Explicación del Sistema de Gestión Jurídica

## Introducción

Este sistema es una aplicación web diseñada para administrar un estudio jurídico que ofrece servicios profesionales en las áreas Civil, Comercial y de Familia. Su propósito es centralizar y digitalizar todas las operaciones del estudio: desde el registro de clientes hasta el seguimiento de procesos legales, la gestión de turnos y la documentación asociada.

---

## Actores del Sistema

El sistema cuenta con cinco tipos de usuarios con roles diferenciados:

- **Secretario**: Es el encargado del registro inicial de clientes, la carga de documentación, el agendamiento de turnos y la gestión de comprobantes de pago.
- **Cliente**: Es la persona que solicita los servicios del estudio. No interactúa directamente con el sistema, sino que recibe notificaciones.
- **Profesional**: Es el abogado o especialista que atiende los casos. Puede consultar legajos, actualizar estados de procesos y generar reportes.
- **Coordinador**: Es quien evalúa la viabilidad de los casos, decide si admitir o rechazar un cliente, asigna profesionales y supervisa el avance de los procesos.
- **Directivo**: Tiene acceso completo al sistema y puede gestionar usuarios y consultar reportes.

---

## Entidades del Sistema

### Usuario

Representa a cualquier persona que accede al sistema. Puede ser un coordinador, un profesional o un directivo. Opcionalmente, un usuario puede tener datos profesionales asociados como DNI, teléfono, domicilio, fecha de nacimiento y fecha de ingreso al estudio. Los usuarios tienen roles y permisos que controlan qué acciones pueden realizar.

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

Cuando un cliente se presenta por primera vez en el estudio, el secretary registra sus datos personales en el sistema.automáticamente se crea un proceso con el servicio "Consultoría" en estado pendiente. A continuación, se agenda un turno con el coordinador, quien recibe una notificación automática.

### Admisión del Cliente

El coordinador abre el legajo electrónico del cliente, evalúa la viabilidad del caso y decide si admitirlo o rechazarlo. Si lo admite, el proceso cambia a estado "admitido" y se solicita la asignación de un profesional. Si se rechaza, se registra el motivo y el cliente es notificado.

### Asignación de Profesional

El coordinador selecciona un profesional disponible según su especialidad y el tipo de servicio requerido. El profesional asignado recibe una notificación y el secretary también es informado para coordinar los siguientes pasos.

### Solicitud de Documentación

Una vez admitido el cliente, el secretary solicita los documentos necesarios según el tipo de servicio. Estos documentos son digitalizados y cargados en el sistema, quedando disponibles para el profesional asignado.

### Agendamiento de Turnos con el Profesional

Se agenda un nuevo turno entre el cliente y el profesional designado. Ambos reciben notificaciones con los detalles del turno. Los turnos pueden ser internos (consultas en el estudio) o externos (audiencias judiciales). Los turnos externos bloquean la disponibilidad del profesional para todo el día.

### Seguimiento del Proceso

El profesional puede acceder al legajo completo del cliente, consultar su historial de procesos y toda la documentación asociada. A medida que avanza el caso, actualiza el estado del proceso (iniciado, en proceso, finalizado o en espera). Cada actualización queda registrada en el historial del proceso.

### Registro de Reportes

El profesional puede registrar actas de reuniones o acciones realizadas durante el seguimiento del caso. Estos reportes quedan visibles para el coordinador y el directivo, permitiendo un control efectivo del avance de cada proceso.

### Gestión de Pagos

El secretary descarga el comprobante de pago desde el sistema externo de ARCA y lo carga en el sistema interno, asociándolo al cliente correspondiente. Esto permite mantener un registro ordenado de todos los pagos.

### Modificación y Cancelación de Turnos

Si un turno debe modificarse, el secretary puede cambiar la fecha y hora. El sistema verifica la disponibilidad del profesional y notifica a todos los involucrados. Si se cancela un turno externo, se desbloquea la disponibilidad del profesional.

---

## Modelo de Datos: Relaciones entre Entidades

La siguiente descripción explica cómo se relacionan las entidades entre sí:

- Un **Usuario** puede tener muchos **Turnos** (como profesional), muchos **Reportes** (como autor), muchos **Procesos** como profesional asignado y muchos **Procesos** como coordinador.
- Un **Cliente** puede tener muchos **Procesos**, muchos **Turnos**, muchos **Comprobantes de Pago** y muchas **Notificaciones**.
- Un **Servicio** puede estar asociado a muchos **Procesos**.
- Un **Proceso** puede tener muchos **Turnos** (seguimientos), muchos **Documentos** y muchos **Reportes**.
- Una **Notificación** puede estar vinculada a un **Usuario** y/o a un **Cliente**.

El sistema utiliza eliminación lógica (SoftDeletes) en todas las entidades principales, lo que significa que los registros no se borran permanentemente sino que se marcan con una fecha de eliminación. Esto permite mantener trazabilidad y recuperar datos si es necesario.

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
- Cuando se asigna un profesional a un proceso, tanto el profesional como el secretary reciben la información.
- Cuando se modifica o cancela un turno, los involucrados son notificados.

Las notificaciones se envían a través de email y WhatsApp utilizando un servicio externo (brevo).

---

## Resumen de Funcionalidades por Rol

| Rol             | Funcionalidades principales                                                                                 |
| --------------- | ----------------------------------------------------------------------------------------------------------- |
| **Secretario**  | Registrar/modificar/eliminar clientes, agendar turnos, cargar documentación, gestionar comprobantes de pago |
| **Coordinador** | Evaluar casos, admitir o rechazar clientes, asignar/reasignar profesionales, consultar reportes             |
| **Profesional** | Consultar legajos, actualizar estados de procesos, generar reportes, descargar documentación                |
| **Directivo**   | Gestionar usuarios, consultar reportes, acceso completo al sistema                                          |
