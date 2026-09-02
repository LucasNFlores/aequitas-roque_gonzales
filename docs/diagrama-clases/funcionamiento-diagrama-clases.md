# Funcionamiento del Diagrama de Clases por Modulos

Este documento explica como interpretar el diagrama de clases del sistema de gestion juridica. El diagrama esta separado por modulos para que sea mas facil entender que responsabilidad tiene cada parte del dominio y como se conectan las entidades principales.

Archivos relacionados:

- `diagrama-clases.mermaid`: version Mermaid del diagrama.
- `diagrama-clases.md`: documentacion del diagrama con el bloque Mermaid incrustado.

## Como leer el diagrama

El diagrama usa una estructura de clases UML hecha con Mermaid. Cada caja representa un modelo del sistema, normalmente ubicado en `app/Models/`.

Dentro de cada clase aparecen dos tipos de elementos:

- Atributos: son campos de la tabla, por ejemplo `nombre`, `dni`, `fecha_hora` o `estado`.
- Metodos de relacion: son funciones Eloquent que conectan modelos, por ejemplo `cliente()`, `turnos()` o `procesosComoProfesional()`.

Las lineas entre clases indican relaciones:

- `1 --> N`: una entidad puede tener muchas entidades relacionadas.
- `N --> 1`: muchas entidades pertenecen a una sola entidad.
- `N --> N`: relacion muchos-a-muchos, normalmente mediante una tabla intermedia.

Los textos entre `<< >>`, como `<<SoftDeletes>>` o `<<Authenticatable>>`, son estereotipos UML. En este diagrama se usan para indicar capacidades tecnicas del modelo, no campos de la tabla.

## Modulos del sistema

### Acceso y Personas

Este modulo agrupa las personas principales del sistema:

- `User`: representa a los usuarios internos que pueden acceder a la aplicacion, como secretarios, profesionales, coordinadores o directivos.
- `Cliente`: representa a la persona que solicita servicios juridicos al estudio.

`User` es una clase especial porque participa en autenticacion, roles, permisos, auditoria, notificaciones y borrado logico. Por eso en el diagrama aparecen estereotipos como:

- `<<Authenticatable>>`: permite iniciar sesion.
- `<<Auditable>>`: registra cambios relevantes.
- `<<HasRoles>>`: administra roles y permisos.
- `<<Notifiable>>`: puede recibir notificaciones.
- `<<SoftDeletes>>`: permite borrado logico mediante `deleted_at`.

`Cliente` no inicia sesion en el sistema. Es una entidad de dominio usada para registrar datos personales, procesos, turnos, comprobantes y notificaciones.

### Gestion Juridica

Este modulo contiene el nucleo funcional del sistema:

- `Proceso`: representa un caso o expediente juridico abierto para un cliente.
- `Servicio`: representa el tipo de servicio ofrecido por el estudio, con su costo asociado.

`Proceso` es la clase central del diagrama porque conecta clientes, profesionales, coordinadores, servicios, turnos, documentos y reportes. Cada proceso pertenece a un cliente y a un servicio. Tambien puede tener un profesional asignado y un coordinador responsable.

`Servicio` define lo que el estudio ofrece. Un servicio puede estar vinculado a muchos procesos y tambien a muchos usuarios profesionales mediante la relacion muchos-a-muchos `user_servicios`.

### Agenda

Este modulo administra la planificacion de citas:

- `Turno`: representa una cita entre un cliente y un profesional.

Un turno pertenece a un cliente y a un profesional. Tambien puede pertenecer a un proceso cuando se trata de un seguimiento de caso. Si el turno es externo, como una audiencia judicial, el campo `es_externo` permite distinguirlo y `detalle_externo` permite guardar informacion adicional.

### Documentacion y Seguimiento

Este modulo registra el material asociado al avance de un proceso:

- `Documento`: archivo digital asociado a un proceso.
- `Reporte`: acta, informe o registro generado por un profesional.

Los documentos permiten mantener el expediente digital ordenado. Los reportes permiten dejar constancia de reuniones, avances o acciones realizadas sobre el proceso.

### Pagos

Este modulo registra comprobantes economicos asociados a clientes:

- `ComprobantePago`: archivo de comprobante de pago vinculado a un cliente.

El comprobante guarda la ruta del archivo, la fecha de subida y una descripcion opcional. A diferencia de la mayoria de los modelos del diagrama, no aparece con `SoftDeletes`.

### Comunicaciones

Este modulo representa los mensajes enviados por el sistema:

- `Notificacion`: mensaje dirigido a un usuario interno o a un cliente.

Una notificacion puede estar asociada a `User`, a `Cliente`, o a ambos segun el evento. Guarda el canal de envio, el mensaje, la fecha de envio y el estado.

## Relaciones principales

### User con Proceso

`User` se relaciona con `Proceso` de dos maneras distintas:

- `procesosComoProfesional()`: procesos en los que el usuario trabaja como profesional asignado.
- `procesosComoCoordinador()`: procesos en los que el usuario supervisa como coordinador.

Ambas relaciones apuntan a la misma tabla `users`, pero usan claves foraneas distintas en `procesos`: `profesional_id` y `coordinador_id`.

### User con Servicio

`User` y `Servicio` tienen una relacion muchos-a-muchos:

- `User.servicios()`
- `Servicio.usuarios()`

Esta relacion se implementa mediante la tabla intermedia `user_servicios`. Sirve para indicar que profesionales pueden estar asociados a determinados servicios.

### Cliente con Proceso

Un cliente puede tener muchos procesos. Cada proceso pertenece a un solo cliente.

Esta es una de las relaciones principales del sistema porque el historial juridico de una persona se construye a partir de sus procesos.

### Proceso con Turno, Documento y Reporte

Un proceso puede tener:

- Muchos turnos de seguimiento.
- Muchos documentos cargados.
- Muchos reportes generados por profesionales.

Estas relaciones convierten a `Proceso` en el punto de entrada natural para consultar el expediente completo de un cliente.

### Cliente con ComprobantePago

Un cliente puede tener muchos comprobantes de pago. Cada comprobante pertenece a un cliente.

Esto permite mantener los pagos asociados a la persona, independientemente de que tenga uno o varios procesos.

### User y Cliente con Notificacion

Las notificaciones pueden estar dirigidas a usuarios internos o clientes. Por eso `Notificacion` tiene dos posibles claves foraneas:

- `user_id`
- `cliente_id`

Esto permite registrar mensajes para eventos como confirmacion de turnos, asignacion de profesionales, admision o rechazo de procesos.

## Flujo general representado por el diagrama

1. Se registra un `Cliente` con sus datos personales.
2. Se crea un `Proceso` asociado a ese cliente y a un `Servicio`.
3. Un `User` con rol de coordinador evalua el proceso.
4. Si el proceso es admitido, se asigna un `User` profesional.
5. Se agendan `Turno` para consulta inicial, seguimiento o eventos externos.
6. Se cargan `Documento` vinculados al proceso.
7. El profesional registra `Reporte` con avances o reuniones.
8. Se cargan `ComprobantePago` asociados al cliente.
9. El sistema genera `Notificacion` para usuarios internos o clientes segun corresponda.

## Estados y tipos importantes

`Proceso.estado` representa el ciclo de vida del caso:

- `pendiente`
- `admitido`
- `iniciado`
- `en_proceso`
- `finalizado`
- `en_espera`
- `rechazado`

`Proceso.tipo` clasifica el area juridica:

- `Civil`
- `Comercial`
- `Familia`

`Turno.tipo` clasifica la cita:

- `consulta_inicial`
- `seguimiento`
- `externo`

`Notificacion.canal` indica el medio:

- `email`
- `whatsapp`

## Capacidades tecnicas representadas

### SoftDeletes

Los modelos con `<<SoftDeletes>>` usan borrado logico. Cuando se eliminan, no se borran fisicamente de la base de datos; se marca la columna `deleted_at`.

Esto ayuda a conservar trazabilidad y permite recuperar informacion si fue eliminada por error.

### HasFactory

`<<HasFactory>>` indica que el modelo puede generar datos de prueba mediante factories de Laravel. Aunque todos los modelos pueden tener factories, en el diagrama se destaca especialmente en `ComprobantePago` porque no usa `SoftDeletes`.

### Authenticatable

`<<Authenticatable>>` aparece en `User` porque es el modelo que Laravel usa para login, sesiones y autenticacion.

### HasRoles

`<<HasRoles>>` aparece en `User` porque el sistema usa roles y permisos. Esto permite diferenciar acciones de secretario, profesional, coordinador y directivo.

### Auditable

`<<Auditable>>` aparece en `User` porque se registran cambios relevantes para trazabilidad.

### Notifiable

`<<Notifiable>>` aparece en `User` porque puede recibir notificaciones del sistema.

## Resumen

El diagrama muestra un sistema centrado en `Proceso`. Alrededor de esa clase se organizan los clientes, usuarios internos, servicios, turnos, documentos, reportes, pagos y notificaciones.

La separacion por modulos permite leer el sistema por responsabilidades:

- Personas y acceso al sistema.
- Gestion del caso juridico.
- Agenda de turnos.
- Expediente digital y seguimiento.
- Pagos.
- Comunicaciones.

Esta organizacion ayuda a entender no solo que clases existen, sino tambien que parte del negocio representa cada una.
