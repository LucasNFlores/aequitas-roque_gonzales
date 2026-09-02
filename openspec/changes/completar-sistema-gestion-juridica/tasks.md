# Tareas pendientes

## Base y seguridad

- [ ] Definir matriz de permisos por rol y aplicarla a rutas, acciones y consultas.
- [ ] Confirmar que el cliente no tenga autenticacion, credenciales ni permisos.
- [ ] Revisar validaciones, politicas, autorizacion y baja logica.
- [ ] Completar pruebas de permisos y flujos principales.
- [ ] Corregir la inconsistencia de baja logica de comprobantes de pago.

## Backend

- [ ] Implementar `ClienteController` y sus validaciones.
- [ ] Implementar la creacion automatica de proceso y turno inicial.
- [ ] Implementar `ProcesoController`, estados, admision, rechazo y asignacion.
- [ ] Implementar CRUD de estados de procesos, incluyendo activacion y desactivacion segura.
- [ ] Implementar `TurnoController`, disponibilidad, externos, cambios y cancelaciones.
- [ ] Implementar documentos, reportes, comprobantes y notificaciones.
- [ ] Implementar CRUD de tipos/categorias de documentos.
- [ ] Diseñar e integrar los canales Brevo para correo y WhatsApp.
- [ ] Crear servicios de dominio para notificaciones y transiciones de estado.

## Frontend

- [ ] Definir layout del panel y navegacion por rol.
- [ ] Crear pantallas de clientes y procesos.
- [ ] Crear agenda de turnos y vista de disponibilidad.
- [ ] Crear carga y consulta de documentos, reportes y comprobantes.
- [ ] Crear bandeja de notificaciones y estados visuales.
- [ ] Definir el contenido de los dashboards por rol.
- [ ] Incorporar Livewire y Alpine solo donde aporten interactividad.

## Calidad y entrega

- [ ] Agregar rutas faltantes y verificar nombres consistentes.
- [ ] Probar el flujo completo desde alta de cliente hasta seguimiento profesional.
- [ ] Probar archivos, limites, formatos y accesos no autorizados.
- [ ] Preparar datos iniciales de roles, permisos y servicios.
- [ ] Documentar instalacion, configuracion y despliegue.
