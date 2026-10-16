# Diseño: selección de estados en la vista histórica

## Opciones y validación

Las opciones se forman con estados del catálogo (incluidos inactivos o dados de baja que tengan valor histórico) y slugs presentes en `HistorialEstadoProceso`. Se muestran con el nombre del catálogo cuando existe y con una etiqueta derivada del slug para estados que solo aparecen en el historial.

La visita inicial selecciona todos los estados disponibles. El formulario GET envía `states_filter_present=1` para que el servidor distinga una selección vacía del valor por defecto. Al enviarse, `states` debe incluir al menos un slug permitido y no repetido. Los controles “Seleccionar todos” y “Quitar todos” operan sobre el grupo de casillas; una selección vacía se explica junto al grupo y no ejecuta el agregado.

## Consulta y presentación

La consulta filtra `estado_nuevo` con los slugs seleccionados antes de agrupar, conserva `fecha_cambio`, `COUNT(DISTINCT proceso_id)` y `Proceso::visibleTo($user)`. El gráfico y la lista accesible se generan desde el mismo resultado. Las métricas y el gráfico actual quedan intactos.

El filtro usa casillas accesibles y controles compactos en una cuadrícula adaptable, antes del rango de fechas para evitar superposición en pantallas de 375 px. La selección se restaura después de errores. El estado inicial y el enlace Restablecer incluyen todos los estados.

## Verificación

Probar el estado por defecto, un estado no terminal, varios estados, estado catalogado/histórico adicional, selección vacía, slug desconocido, roles autorizados y no autorizados, y scope `visibleTo`. Revisar el formulario en IAB a 375 px y escritorio.
