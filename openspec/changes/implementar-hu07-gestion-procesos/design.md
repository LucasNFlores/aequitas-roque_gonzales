# Diseño: gestión de procesos HU-07

## Estado actual y decisión

Se extiende la página Livewire `/procesos` existente y se conservan `Proceso::visibleTo()`, las policies, el catálogo `EstadoProceso` y el historial creado desde `Proceso::transitionTo()`. No se crea una API ni una segunda bandeja.

## Reglas de dominio

- La admisión y el rechazo solo se ejecutan sobre procesos en estado `pendiente` y requieren las policies CU15/CU15.1.
- La admisión transiciona al estado activo `admitido` y limpia cualquier causal anterior.
- El rechazo valida una causal no vacía, guarda `motivo_rechazo` y registra esa misma causal en la transición a `rechazado`.
- El cambio general de estado no permitirá usar `admitido` o `rechazado` para evitar omitir sus reglas específicas.
- La asignación requiere un usuario no eliminado, con rol `Profesional` y relación vigente con el servicio del proceso. La reasignación repite esas validaciones.
- Al asignar o reasignar, `honorarios` toma el `costo_servicio` vigente del servicio. No se recalculan registros históricos en la migración.
- Cada mutación se ejecuta en transacción. La UI vuelve a cargar el proceso visible y reautoriza al confirmar la acción.

## Persistencia

- Agregar mediante migración reversible `procesos.honorarios` como decimal nullable de precisión equivalente a `servicios.costo_servicio`.
- Agregar el atributo fillable y cast decimal al modelo `Proceso`.
- Mantener el campo `motivo_rechazo` existente y el listener del modelo que escribe el historial.

## Autorización y alcance

| Acción | Permiso/policy | Alcance |
|---|---|---|
| Listar y filtrar | `listar_filtrar_procesos` | Profesional limitado por `visibleTo()` a sus procesos |
| Ver historial | `consultar_historial_estados` | Profesional limitado a procesos asignados |
| Admitir / rechazar | `admit` / `reject` | Coordinador y Administrador; solo estado pendiente |
| Asignar / reasignar | `assignProfessional` / `reassignProfessional` | Secretario, Coordinador y Administrador |
| Cambiar otro estado | `updateState` | Profesional asignado, Coordinador y Administrador |

La UI oculta acciones sin permiso, pero cada método Livewire obtiene el proceso mediante `visibleTo()` y vuelve a ejecutar `Gate::authorize()`.

## Bandeja

- Extender la consulta paginada con filtros de cliente, estado, servicio, coordinador, profesional y `fecha_inicio` desde/hasta.
- Buscar por nombre del proceso, cliente/DNI, servicio, estado, coordinador y profesional.
- Cargar relaciones del cliente, servicio, coordinador y profesional con eager loading.
- Mostrar servicio, coordinador, profesional y honorarios junto con los datos actuales.
- Para un Profesional, limitar opciones de profesional a su propio usuario y filas/procesos al alcance ya definido por el modelo.

## Interacción

- Acciones separadas para admitir, abrir rechazo, abrir asignación/reasignación, cambiar otros estados y consultar historial.
- Rechazo mediante modal con motivo obligatorio.
- Asignación mediante modal con profesionales elegibles para el servicio actual.
- La validación de elegibilidad se repite en servidor; los valores de los selectores nunca se consideran confiables.

## Verificación

Probar PHPUnit con `RefreshDatabase` siguiendo la suite existente. Ejecutar la prueba de procesos en el contenedor `laravel.test`. Para aceptación UI, usar el navegador conectado al sitio Docker con registros temporales únicos y eliminar únicamente los IDs creados durante esa sesión.
