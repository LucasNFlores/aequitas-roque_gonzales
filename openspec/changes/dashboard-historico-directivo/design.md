# Diseño: vista histórica del dashboard del Directivo

## Consulta

La consulta se deriva de `HistorialEstadoProceso`, filtra por `fecha_cambio` y agrupa por `estado_nuevo`. Primero limita los eventos a los procesos entregados por `Proceso::query()->visibleTo($user)` y, por la relación Eloquent, omite procesos con baja lógica. Cada grupo usa `COUNT(DISTINCT proceso_id)` para que un reingreso al mismo estado no duplique el caso. Un caso puede contar en estados diferentes.

El selector ofrece `3 meses`, `6 meses` y `rango personalizado`. Los presets comienzan en la misma fecha calendario, tres o seis meses antes del día actual, y terminan hoy; ambos extremos son inclusivos. El rango personalizado también es inclusivo. En el primer render se aplica 3 meses. Se excluyen finalizado y rechazado por defecto; dos casillas independientes los incluyen al seleccionarse. No se exponen entradas de estado libre.

La consulta histórica se construye para Directivo y Administrador, que ya tienen CU40 por matriz/superadministración. En ambos casos, `Proceso::visibleTo($user)` aplica el scope efectivo; no se agrega un permiso.

## Validación y presentación

- Validar `period`, fechas calendario válidas, fechas requeridas para rango personalizado, orden `desde <= hasta` y límite `hasta <= hoy`.
- Mantener la validación de servidor. Las fechas usan inputs nativos con etiquetas visibles, `max` de hoy, ancho completo y controles apilados en móviles; un aviso asociado explica límites y errores.
- El formulario GET muestra las fechas efectivas y el período elegido. Cambiar a un preset oculta y desactiva las fechas personalizadas para no combinar filtros.
- “Restablecer filtros” vuelve a 3 meses y excluye finalizado/rechazado.
- La sección se titula “Cambios de estado en procesos” y describe “Fecha de cambio de estado”. Informa que cada barra cuenta procesos distintos en ese estado y que los valores no se suman como total único.
- Reutiliza el componente de barras y su lista accesible. Un resultado vacío conserva el período y filtros, muestra una explicación y no dibuja un gráfico vacío.
- La sección se renderiza únicamente en los dashboards autorizados por CU40 (Directivo y Administrador); no modifica las métricas ni el gráfico actual de procesos activos por servicio.

## Verificación

PHPUnit verifica límites inclusivos y exclusiones, cambio de estado distinto a fecha de inicio, deduplicación dentro del mismo estado, conteo en varios estados, aislamiento por `visibleTo`, omisión para otros roles, inclusión/exclusión de terminales, validación y vacío. La revisión UI cubre escritorio y 375 px, controles nativos, errores, presets, filtros, lista/gráfico y accesos prohibidos.
