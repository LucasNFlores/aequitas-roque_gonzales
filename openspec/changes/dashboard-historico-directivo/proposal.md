# Propuesta: vista histórica del dashboard del Directivo

## Problema

CU40 presenta las métricas vigentes y los procesos activos actuales por servicio, pero no permite revisar los cambios de estado ocurridos durante un período. La fecha de referencia ya está definida: se usa la fecha del evento de cambio de estado.

## Alcance

- Añadir al dashboard de Directivo una distribución histórica de procesos que entraron en cada estado durante los últimos 3 meses, los últimos 6 meses o un rango personalizado.
- Permitir incluir finalizados y rechazados; la vista predeterminada muestra los estados no terminales.
- Contar procesos distintos por estado, respetar `Proceso::visibleTo($user)` y explicar el período, la fecha usada y la falta de aditividad entre estados.
- Conservar sin cambios las cuatro métricas actuales y el gráfico de procesos activos por servicio.
- Mantener el acceso de CU40 para Directivo y Administrador por superadministrador, conforme a la autorización existente; no crear permisos ni ampliar scopes.

## Restricciones

- Los límites de fecha son inclusivos y no pueden ser futuros; el rango personalizado requiere ambas fechas y `desde` no puede ser posterior a `hasta`.
- Los datos históricos salen de `historial_estados_proceso.fecha_cambio` y `estado_nuevo`, no de la fecha de inicio ni del estado actual del proceso.
- Una repetición del mismo estado por el mismo proceso cuenta una vez por estado; un proceso puede aparecer en más de un estado.
- Procesos dados de baja lógicamente quedan fuera por la relación y scopes vigentes.
- No presentar la distribución histórica bajo el título “Procesos activos”.

## Criterios de salida

- PHPUnit cubre agregación, scope/permisos, períodos, estados terminales, entradas inválidas y resultados vacíos.
- Pint y build Vite pasan; OpenSpec valida.
- La interfaz se revisa en IAB a escritorio y 375 px, incluidos presets, fechas inválidas, filtros, gráfico, vacío y rutas prohibidas.
- Cambios integrados localmente en `dev`; sin push.

## Riesgos

- Sumar barras sobrecontaría casos que transitaron a varios estados; la interfaz debe indicarlo junto a la gráfica.
- Los parámetros de filtro no deben activar la consulta histórica para roles fuera de CU40.
