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
