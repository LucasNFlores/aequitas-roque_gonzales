# Tareas de alineación e implementación

> Las tareas de CU-35/HU-18–HU-20 siguen pendientes y pertenecen al módulo independiente de última fase; revisar costos de Brevo y decidir proveedor antes de comenzar. El alcance actual conserva canales internos, correo y WhatsApp. No marcarlas como parte de las entregas del núcleo.

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
- [x] Implementar el CRUD de servicios con Livewire, Alpine.js y Tailwind CSS.
- [ ] Implementar asignación de especialidades y servicios a profesionales.
- [ ] En la última fase, implementar el registro y consulta autorizada de notificaciones (CU-35 / HU-18).
- [ ] En la última fase, implementar HU-19 y los canales aprobados de HU-20; revisar costos vigentes y decidir proveedor antes de cualquier integración externa.

## Frontend

- [x] Definir Tailwind CSS como estándar visual y Alpine.js + Livewire para vistas interactivas.
- [ ] Definir navegación y acciones visibles por rol.
- [ ] Crear listados paginados y filtros de clientes y procesos.
- [ ] Crear historial visual de estados.
- [ ] Crear agenda y vista de disponibilidad.
- [ ] Crear pantallas de documentos, reportes y comprobantes del núcleo.
- [ ] En la última fase, crear la bandeja de Notificaciones después de validar el módulo de forma aislada.
- [ ] Crear administración de usuarios, estados y categorías.
- [x] Mantener la versión clásica de servicios en `/servicios-viejo` como referencia.
- [ ] Definir dashboards por rol.

## Calidad y entrega

- [x] Incorporar Laravel Pint al flujo de desarrollo para formatear los archivos PHP modificados antes de cada commit.
- [ ] Verificar los 41 casos de uso contra la matriz online.
- [ ] Probar restricciones de archivos y accesos no autorizados.
- [ ] Probar conservación de historial, bajas lógicas y cancelaciones.
- [ ] Probar el flujo completo desde el alta de cliente hasta el seguimiento profesional.
- [ ] Preparar datos iniciales de roles, permisos, estados, categorías y servicios.
- [ ] Actualizar la documentación operativa y de despliegue cuando se implemente cada módulo.
