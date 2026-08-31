# Diseño funcional de referencia

## Usuarios internos y datos administrados

- Secretario: registra clientes, asigna el primer servicio de Consultoria, administra la operacion, turnos y comprobantes.
- Profesional: atiende procesos asignados, solicita documentacion, carga reportes y gestiona turnos.
- Coordinador: admite o rechaza procesos, registra el motivo y asigna profesionales.
- Directivo: consulta la operacion y los reportes generales, sin modificar datos.
- Cliente: dato administrativo. No inicia sesion ni recibe permisos dentro de la aplicacion.

## Flujo principal

```text
Alta de cliente
  -> proceso automatico de Consultoria en estado pendiente
  -> turno inicial con coordinador
  -> admision o rechazo
  -> asignacion de profesional
  -> solicitud y recepcion de documentos
  -> turnos con profesional
  -> reportes y cambios de estado
  -> seguimiento, pagos y notificaciones internas o externas
```

## Modulos pendientes

1. Clientes: CRUD, validaciones, busqueda, baja logica y relacion con procesos.
2. Procesos: alta automatica, detalle, estados configurables, asignacion y motivo de rechazo.
3. Estados de procesos: CRUD para agregar, editar, ordenar, activar o desactivar estados.
4. Turnos: agenda, disponibilidad, turnos externos de dia completo, reprogramacion y cancelacion.
5. Documentos: solicitud, carga, validacion de PDF, descarga y asociacion al proceso.
6. Categorias de documentos: CRUD para agregar, editar, activar o eliminar tipos/categorias.
7. Reportes: carga profesional, historial y consulta por proceso.
8. Comprobantes: carga de archivo, nombre y nota de texto libre por parte del secretario.
9. Notificaciones: destinatarios, canales internos, correo y WhatsApp mediante Brevo.
10. Paneles por rol: menus, indicadores y acciones permitidas. El contenido del dashboard queda pendiente de diseño.

## Reglas transversales

- Todas las acciones deben respetar el rol del usuario.
- Las entidades principales deben soportar baja logica.
- Los cambios relevantes de estado deben quedar auditados.
- Las operaciones relacionadas deben ejecutarse de forma consistente: por ejemplo, alta de cliente, proceso, turno y notificacion.
- Deben validarse permisos tanto en la interfaz como en el servidor.
- El cliente nunca debe aparecer como usuario autenticable ni como sujeto de permisos.
- Los estados y categorias deben ser configurables sin modificar codigo.

## Decision frontend sugerida

Mantener Blade y Tailwind, incorporar Livewire para formularios, tablas, filtros y agenda, y conservar Alpine.js para modales y pequenas interacciones.
