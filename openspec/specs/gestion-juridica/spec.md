# Especificacion del sistema de gestion juridica

## Alcance de usuarios

- El sistema debe ser utilizado unicamente por empleados del estudio juridico.
- Los roles autenticables son Secretario, Profesional, Coordinador y Directivo.
- El cliente debe existir como dato administrativo, sin credenciales, acceso, permisos ni panel propio.

## Requisitos funcionales

### Clientes y procesos

- El sistema debe permitir registrar clientes con sus datos personales y de contacto.
- Al registrar un cliente debe crear un proceso inicial de Consultoria en estado pendiente.
- El proceso debe conservar cliente, servicio, coordinador, profesional, fechas, tipo, descripcion y motivo de rechazo.
- El secretario debe poder asignar al cliente el primer servicio de Consultoria.
- El coordinador debe poder admitir o rechazar el proceso y registrar el motivo cuando corresponda.
- El coordinador debe poder asignar un profesional compatible con el servicio.
- El sistema debe permitir administrar mediante CRUD los estados disponibles de los procesos.
- El secretario debe poder consultar y modificar todos los clientes, procesos, turnos y comprobantes.
- El profesional debe poder consultar unicamente los procesos que tiene asignados.
- El directivo debe poder consultar la informacion general sin modificarla.

### Turnos

- El sistema debe permitir crear turnos para clientes con secretario, coordinador o profesional como responsable/interviniente.
- Solo secretario, coordinador y profesional pueden crear o gestionar turnos.
- Debe impedir conflictos de agenda.
- Un turno externo debe bloquear la jornada completa y guardar su detalle.
- Debe permitir reprogramar y cancelar turnos, actualizando la disponibilidad y notificando a los involucrados.

### Documentos y reportes

- El profesional debe poder solicitar documentacion asociada a un proceso.
- El sistema debe permitir subir, consultar y descargar documentos PDF vinculados al proceso.
- El sistema debe permitir administrar mediante CRUD los tipos o categorias de documentos.
- El profesional debe poder registrar reportes con contenido y fecha.
- El cliente y los roles autorizados deben poder consultar la informacion correspondiente.

### Pagos y notificaciones

- El secretario debe poder cargar un comprobante y registrar su nombre y una nota de texto libre, asociandolo al cliente.
- El sistema debe registrar canal, mensaje, destinatario, fecha y estado de cada notificacion.
- Los eventos de alta, admision, rechazo, asignacion y cambios de turno deben generar notificaciones segun el rol destinatario.
- Las notificaciones deben poder enviarse internamente, por correo y por WhatsApp mediante Brevo.

### Auditoria y seguridad

- El sistema debe restringir cada operacion segun el rol del usuario.
- Las entidades principales deben admitir baja logica.
- Los cambios sensibles deben quedar auditados.

## Criterio de completitud

La aplicacion se considerara alineada con la descripcion cuando los flujos anteriores puedan ejecutarse desde la interfaz, con autorizacion por rol, persistencia consistente, estados y categorias configurables, notificaciones y pruebas automatizadas de los casos principales. El contenido final de los dashboards queda pendiente de definicion.
