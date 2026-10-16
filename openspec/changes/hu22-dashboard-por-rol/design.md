# Diseño del dashboard operativo por rol

## Decisiones de datos

- La ruta existente `dashboard` conserva `auth` y `verified`. Un controlador arma la vista usando el usuario autenticado y `can()` para capacidades, sin ramificar por nombres de rol.
- La consulta base de procesos usa `Proceso::visibleTo($user)`. El indicador común cuenta procesos operativos, excluyendo los estados terminales `finalizado` y `rechazado`.
- La cola **Para admitir** existe solo para quienes pueden `admitir_procesos`; contiene procesos visibles en `pendiente` y enlaza a la bandeja CU 29.
- La cola **Para asignar profesional** existe solo para quienes pueden `asignar_profesionales`; contiene procesos visibles en `admitido` sin profesional asignado y enlaza a la bandeja CU 29.
- Las próximas citas (siete días, inclusive) se consultan con `Turno::visibleTo($user)`, estado `programado` y CU 8. El Profesional permanece limitado a su propia agenda por el scope existente.
- Los clientes se cuentan con `Cliente::visibleTo($user)` y CU 28 o legajos visibles. Los reportes se cuentan solo con `Reporte::visibleTo($user)` y CU 24. Los usuarios se cuentan solo si CU 25–27 está disponible.
- Las métricas se enlazan a la pantalla existente autorizada: procesos, clientes, agenda, reportes por la bandeja de procesos o usuarios. No se crean rutas de datos detallados nuevas.
- Un Profesional ve su carga activa asignada como seguimiento, no como tarea vencida: no hay vencimientos definidos en el dominio. Directivo ve sus métricas autorizadas y accesos de administración; no se inventa una cola jurídica que no pueda resolver ni se muestra agenda, documentos o archivos.
- Si la consulta no produce elementos, la vista muestra un estado vacío con explicación y enlace solo cuando existe un destino autorizado.

## Interfaz

- Mantener `x-app-layout`, el sistema visual, la tipografía y los componentes Tailwind del proyecto.
- Priorizar el saludo/rol, después indicadores numéricos y luego colas con tareas accionables. Usar tarjetas simples y una lista legible en móvil; no depender del color para comunicar estado.
- Usar enlaces con nombre de ruta, foco visible, texto descriptivo y áreas táctiles cómodas. Sin gráficos o iconos decorativos dependientes de paquetes.
- Reflow móvil de una columna, dos columnas desde `md` y más densidad en escritorio. Los estados vacíos no dejan paneles en blanco.

## Riesgos y mitigaciones

- Fuga por conteos agregados: aplicar los scopes antes de `count`, `groupBy`, `limit` y `with` y comprobar con fixtures ajenos.
- Enlaces no autorizados: generar cada tarjeta y enlace desde capacidades verificadas; mantener middleware y policies existentes como autoridad.
- Cambios de layout entre roles: pruebas de render por los cinco roles y recorrido real en el navegador.

## Ampliación aprobada: métricas del Secretario

- En el dashboard del Secretario, la tarjeta de clientes representa las altas dentro de los últimos 30 días calendario: desde `startOfDay(now()->subDays(29))` hasta el momento actual. La consulta usa `Cliente::visibleTo($user)` antes del conteo y el scope SoftDeletes excluye bajas lógicas.
- El conteo de procesos pendientes de asignación sigue siendo la cola existente (`admitido` sin `profesional_id`); no se duplica como otra tarjeta.
- La distribución de procesos activos del Secretario se representa con barras horizontales cuando hay al menos dos estados con casos. La lista de valores permanece visible como alternativa accesible y fallback si Chart.js no carga; con cero o un estado se omite el canvas.
- La mejora no modifica la vista de los demás roles ni resuelve la definición aún pendiente de CU 40 para Directivo.

## Ampliación aprobada: dashboard del Coordinador

- El bloque principal del Coordinador contiene el conteo de procesos activos y los turnos programados de los próximos siete días. El total de procesos y su distribución parten de `Proceso::visibleTo($user)`; la agenda parte de `Turno::visibleTo($user)`.
- Las colas accionables existentes permanecen: pendientes de admisión (`pendiente`), admitidos sin Profesional y procesos en seguimiento (`iniciado`, `en_proceso`, `en_espera`). Cada consulta aplica primero el scope visible y luego el estado; no se agregan vencimientos ni productividad.
- La distribución reutiliza `x-dashboard.process-state-chart` y la importación diferida ya existente. El gráfico se representa horizontalmente solo con dos o más estados con casos; la lista numérica permanece siempre como alternativa accesible y fallback si Chart.js no carga.
- Clientes y reportes recientes salen de las tarjetas principales del Coordinador. Se conservan los accesos de navegación protegidos por sus permisos actuales; no se alteran rutas ni autorizaciones.

## Ampliación aprobada: panel operativo del Administrador

- El total de procesos activos y su agrupación por estado se construyen desde `Proceso::visibleTo($user)` antes de contar o agrupar. Excluyen estados `finalizado` y `rechazado` y bajas lógicas. El gráfico horizontal reutiliza `x-dashboard.process-state-chart` y Chart.js diferido; la lista numérica se mantiene como alternativa accesible. Con cero estados con casos se muestra un mensaje vacío; con un estado se muestra también el gráfico.
- El Administrador ve únicamente las colas operativas aprobadas: procesos visibles `pendiente` para admisión y `admitido` sin `profesional_id` para asignación. Cada cola mantiene su conteo total y una vista previa de hasta cinco casos. No se presenta la cola de seguimiento.
- Los turnos próximos usan `Turno::visibleTo($user)`, estado `programado` y un rango inclusivo desde `now()` hasta `now()->addDays(7)`. Se muestra el conteo y hasta cinco turnos con enlace a la agenda.
- Las cuentas internas vigentes se cuentan mediante `User::query()`, que excluye las bajas lógicas, y enlazan a la gestión de usuarios.
- Las tarjetas principales son procesos activos globales, turnos próximos y cuentas vigentes; las colas presentan los conteos de admisión y asignación. No se agregan tarjetas de clientes o reportes ni se usa el gráfico por servicio reservado a la variante estratégica del Directivo.
- El contenido se adapta a móvil y escritorio. Los estados vacíos conservan el contexto y los destinos autorizados; la gráfica incluye texto equivalente y no comunica valores solo por color.
