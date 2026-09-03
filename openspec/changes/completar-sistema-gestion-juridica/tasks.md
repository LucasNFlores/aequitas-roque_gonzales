# Tareas de alineación e implementación

## Alineación documental

- [x] Confirmar la matriz online como fuente definitiva.
- [x] Incorporar el rol Administrador como superadministrador.
- [x] Definir permisos de Directivo para usuarios y servicios.
- [x] Registrar la política mixta de conservación y baja.
- [x] Mantener CRUD de estados y categorías documentales.
- [x] Actualizar la matriz online con CU 28 a CU 37 y los permisos corregidos.
- [x] Revisar que OpenSpec y `docs/` no conserven permisos contradictorios.

## Base y seguridad

- [ ] Definir la matriz de permisos en Spatie y aplicarla a rutas, acciones, políticas y consultas.
- [ ] Confirmar que el cliente no tenga autenticación, credenciales ni permisos.
- [ ] Implementar baja lógica y exclusión de eliminados en consultas operativas.
- [ ] Implementar estados de cancelación para turnos y conservación de auditoría.
- [ ] Completar pruebas de permisos por rol y alcance del Profesional.

## Backend

- [ ] Implementar `ClienteController` y sus validaciones.
- [ ] Implementar la creación automática de proceso de Consultoría y turno inicial.
- [ ] Implementar `ProcesoController`, admisión, rechazo, estados, historial y asignaciones.
- [ ] Implementar CRUD de estados para Coordinador y Administrador.
- [ ] Implementar `TurnoController`, disponibilidad, externos, reprogramación y cancelación.
- [ ] Implementar carga, reemplazo, visualización, descarga y baja de documentos PDF de hasta 20 MB.
- [ ] Implementar CRUD de categorías de documentos.
- [ ] Implementar reportes con autoría, consulta propia y administración del Administrador.
- [ ] Implementar comprobantes PDF descargados desde ARCA.
- [ ] Implementar administración de usuarios para Directivo y Administrador.
- [ ] Implementar CRUD de servicios y asignación de especialidades.
- [ ] Implementar registro y consulta autorizada de notificaciones.
- [ ] Implementar canales internos, correo y WhatsApp mediante Brevo.

## Frontend

- [ ] Definir navegación y acciones visibles por rol.
- [ ] Crear listados paginados y filtros de clientes y procesos.
- [ ] Crear historial visual de estados.
- [ ] Crear agenda y vista de disponibilidad.
- [ ] Crear pantallas de documentos, reportes, comprobantes y notificaciones.
- [ ] Crear administración de usuarios, servicios, estados y categorías.
- [ ] Definir dashboards por rol.

## Calidad y entrega

- [ ] Verificar los 40 casos de uso contra la matriz online.
- [ ] Probar restricciones de archivos y accesos no autorizados.
- [ ] Probar conservación de historial, bajas lógicas y cancelaciones.
- [ ] Probar el flujo completo desde el alta de cliente hasta el seguimiento profesional.
- [ ] Preparar datos iniciales de roles, permisos, estados, categorías y servicios.
- [ ] Actualizar la documentación operativa y de despliegue cuando se implemente cada módulo.
