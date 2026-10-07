# Diseño de la formalización documental

## Jerarquía de fuentes

1. La hoja online Aequitas V2, pestaña Aequitas - Matriz de Módulos, Funciones y Permisos por Rol V2, define los casos de uso y permisos.
2. La especificación base expresa esos comportamientos mediante Requirements y Scenarios.
3. Las decisiones complementarias existentes en OpenSpec y docs/ se conservan solo cuando no contradicen la matriz.
4. Las rutas, policies y código sirven para detectar pérdidas de comportamiento, pero no prevalecen sobre la matriz.

La transcripción vigente está en [docs/aequitas-matriz-modulos-funciones-permisos-v2.md](../../../docs/aequitas-matriz-modulos-funciones-permisos-v2.md), ampliada por [docs/transcripcion_de_docs.md](../../../docs/transcripcion_de_docs.md) y [docs/explicacion_sistema.md](../../../docs/explicacion_sistema.md).

## Decisiones de modelado

- La hoja contiene 41 casos de uso y cinco roles: Secretario, Profesional, Coordinador, Directivo y Administrador. Los tres códigos fraccionarios y CU 38 cuentan como casos separados.
- El Administrador es superadministrador y está autorizado en los 41 CU. Los otros permisos se toman literalmente de las casillas de la hoja.
- La especificación usa un escenario rotulado con su CU por cada caso. Las restricciones transversales del Profesional se repiten donde afectan listados, legajos, historial, agenda y reportes.
- Para CU 29, la hoja confirma filtros por estado, cliente y profesional asignado. Se conservan también los filtros adicionales por servicio, coordinador y rango de fecha de inicio, ya aprobados en el cambio de HU-07.
- CU 13 permite registrar comprobantes y CU 14 consultarlos. No se incorpora baja, ocultación ni permiso asociado.
- CU 35 conserva el registro de notificaciones y el alcance de HU-18 a HU-20; el módulo queda para la última fase, después de completar y aceptar el núcleo. Se mantienen canales internos, correo y WhatsApp. Brevo es un candidato sujeto a revisión de costos, no una selección vigente.
- La restauración de categorías se mantiene como decisión complementaria de HU-14 bajo la autorización existente de CU 37; no crea un rol ni un permiso nuevo.
- El alta de cliente mantiene el proceso de Consultoría y turno inicial con el Coordinador. El registro interno mínimo asociado a HU-09 no representa la entrega completa de CU 35 ni del módulo final.

## Cambios OpenSpec relacionados

La especificación base es el catálogo vigente, no una segunda matriz. Los cambios especializados expresan únicamente su propia evolución:

- implementar-hu09-alta-cliente formaliza la creación inicial de cliente, proceso, turno y registro interno mínimo.
- implementar-hu07-gestion-procesos formaliza admisión, rechazo, asignaciones, cambio de estado y filtros aprobados.
- gestionar-estados-historial-procesos formaliza CU 30 y CU 36.
- completar-hu12-consulta-agenda formaliza los límites de la consulta de agenda CU 8.
- completar-hu10-legajo-juridico separa la consulta de legajo de la visualización y descarga documental.
- gestionar-compromisos-externos formaliza CU 5 y CU 6.1 y sus conflictos de disponibilidad.
- implementar-hu14-baja-restauracion formaliza baja lógica, restauración y conservación de categorías.
- habilitar-procesos-adicionales especifica CU 38 para un cliente existente.
- El módulo de Notificaciones conserva su requisito independiente de última fase.
- formalizar-turnos-agenda-categorias es una propuesta de planificación de CU 37 y CU 4–8; no modifica por sí sola el comportamiento del producto.

La tabla completa de permisos se mantiene en la matriz y su copia Markdown, no se duplica aquí. La especificación base contiene escenarios para CU 1, 2, 3, 4, 4.1, 5, 6, 6.1, 7, 8, 9, 10, 11, 12, 13, 14, 15, 15.1, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37 y 38.

## Seguimiento funcional conservado fuera de este cambio

El checklist anterior mezclaba alineación documental con implementación de producto. Su trabajo funcional pendiente se conserva aquí como referencia, sin marcarlo como completado ni incluirlo en esta tarea documental:

### Base y seguridad

- [ ] Definir la matriz de permisos en Spatie y aplicarla a rutas, acciones, políticas y consultas.
- [ ] Confirmar que el cliente no tenga autenticación, credenciales ni permisos.
- [ ] Implementar baja lógica y exclusión de eliminados en consultas operativas.
- [ ] Implementar estados de cancelación para turnos y conservación de auditoría.
- [ ] Completar pruebas de permisos por rol y alcance del Profesional.

### Backend

- [ ] Implementar ClienteController y sus validaciones.
- [ ] Implementar la creación automática de proceso de Consultoría y turno inicial.
- [ ] Implementar ProcesoController, admisión, rechazo, estados, historial y asignaciones.
- [ ] Implementar CRUD de estados para Coordinador y Administrador.
- [ ] Implementar TurnoController, disponibilidad, externos, reprogramación y cancelación.
- [ ] Implementar carga, reemplazo, visualización, descarga y baja de documentos PDF de hasta 20 MB.
- [ ] Implementar CRUD de categorías de documentos.
- [ ] Implementar reportes con autoría, consulta propia y administración del Administrador.
- [ ] Implementar comprobantes PDF descargados desde ARCA.
- [ ] Implementar administración de usuarios para Directivo y Administrador.
- [x] Implementar el CRUD de servicios con Livewire, Alpine.js y Tailwind CSS.
- [ ] Implementar asignación de especialidades y servicios a profesionales.
- [ ] En la última fase, implementar el registro y consulta autorizada de notificaciones (CU 35 / HU-18).
- [ ] En la última fase, implementar HU-19 y los canales aprobados de HU-20; revisar costos vigentes y decidir proveedor antes de cualquier integración externa.

### Frontend

- [x] Definir Tailwind CSS como estándar visual y Alpine.js + Livewire para vistas interactivas.
- [ ] Definir navegación y acciones visibles por rol.
- [ ] Crear listados paginados y filtros de clientes y procesos.
- [ ] Crear historial visual de estados.
- [ ] Crear agenda y vista de disponibilidad.
- [ ] Crear pantallas de documentos, reportes y comprobantes del núcleo.
- [ ] En la última fase, crear la bandeja de Notificaciones después de validar el módulo de forma aislada.
- [ ] Crear administración de usuarios, estados y categorías.
- [x] Mantener la versión clásica de servicios en /servicios-viejo como referencia.

El elemento anterior de dashboards por rol no se convierte en requisito: no está respaldado por un caso de uso de la matriz.

### Calidad y entrega

- [x] Incorporar Laravel Pint al flujo de desarrollo para formatear archivos PHP antes de cada commit.
- [ ] Verificar los 41 casos de uso contra la matriz online.
- [ ] Probar restricciones de archivos y accesos no autorizados.
- [ ] Probar conservación de historial, bajas lógicas y cancelaciones.
- [ ] Probar el flujo completo desde el alta de cliente hasta el seguimiento profesional.
- [ ] Preparar datos iniciales de roles, permisos, estados, categorías y servicios.
- [ ] Actualizar la documentación operativa y de despliegue cuando se implemente cada módulo.

## Clasificación documental

Este cambio solo corrige estructura, trazabilidad y conteos de la documentación. El comportamiento funcional ya estaba documentado en la especificación base. Por eso usa skip_specs: true en el metadato del cambio y no contiene una carpeta specs/. Los cambios que agregan o refinan comportamiento conservan sus propias deltas.

## Criterios de verificación

- Comparar cada escenario CU y sus roles contra los valores de la pestaña online.
- Confirmar que la copia local y los documentos ampliados no contradigan esos casos.
- Validar por separado la especificación, este cambio y el repositorio completo con OpenSpec estricto.
- Revisar el diff y ejecutar git diff --check.
