# Diseño: informe PDF documental y dashboard del Directivo

## CU 39 — Informe por categoría

La consulta agrupa documentos por categoría y cuenta las cargas realizadas en el período seleccionado. Admite filtro de período, estado de categoría y categoría específica. Si no se indica período, usa desde hoy menos 29 días hasta hoy, inclusive. El rango personalizado exige ambas fechas válidas, no futuras, ordenadas y con un máximo de 366 días inclusive. El resultado PDF resume cantidades y última fecha de carga por categoría. No lista nombres de archivo ni incorpora los PDF originales. La exportación CSV queda fuera del alcance de CU 39.

La autorización sigue CU 37: Coordinador y Administrador. El Directivo no obtiene el informe por tener acceso a consultar legajos (CU 18). La navegación, el controlador y la descarga directa del PDF deben aplicar la misma autorización. El Administrador conserva acceso universal.

El período predeterminado queda definido como los últimos 30 días calendario. No se integra exportación CSV, que no forma parte del requisito del informe PDF.

## CU 40 — Dashboard del Directivo

CU 40 se presenta como una variante estratégica por rol dentro de la ruta compartida `/dashboard` implementada por HU-22. El Directivo y el Administrador pueden consultar estas métricas; los demás roles conservan el dashboard autorizado de HU-22.

Las métricas aprobadas son:

- Procesos activos vigentes: procesos visibles por CU 29, excluyendo estados `finalizado` y `rechazado`, al corte actual.
- Clientes vigentes: clientes visibles por CU 28, excluyendo bajas lógicas, al corte actual.
- Reportes de los últimos 30 días: reportes visibles por CU 24, por `fecha`, desde hoy menos 29 días hasta hoy, inclusive.
- Cuentas vigentes: usuarios internos no dados de baja lógica, accesibles mediante CU 25–27, al corte actual.

El gráfico horizontal agrupa los procesos activos vigentes por `servicio_id` y muestra el nombre del servicio. La consulta aplica `Proceso::visibleTo($user)` antes de agrupar; excluye procesos finalizados, rechazados y dados de baja. Si un servicio tuvo baja lógica, se conserva en la distribución con una etiqueta aclaratoria. La lista de cantidades permanece visible como alternativa al gráfico.

Esta variante no muestra agenda, documentos, archivos ni colas operativas. Los enlaces usan destinos existentes autorizados: procesos (CU 29), clientes (CU 28), consulta de reportes (CU 24) y usuarios (CU 25–27). Los reportes conservan su destino actual a la bandeja de procesos; no se crea una ruta de detalle nueva.

Los conteos no incluyen nombres de clientes, profesionales ni procesos. No se infieren vencimientos ni productividad. Los períodos se presentan en el texto de cada indicador; no se incorpora un filtro global que mezcle métricas de corte actual con una ventana de fechas.

## Verificación prevista

- Probar el acceso al informe por interfaz y URL directa para Coordinador, Administrador y Directivo.
- Verificar que los filtros coincidan en la vista y en el PDF, que se incluyan las cantidades y fechas previstas y que no se adjunten archivos originales.
- Probar la variante CU 40 con Directivo y Administrador y verificar que Secretario, Profesional y Coordinador no reciban el gráfico estratégico.
- Confirmar que las métricas no exponen información fuera de los permisos del Directivo.

### Interfaz del gráfico

Se reutiliza la carga diferida de Chart.js y el componente horizontal existente. El gráfico se muestra con al menos un servicio con procesos; ante cero resultados se informa el estado vacío. Los nombres y valores se presentan también en una lista semántica, el gráfico no transmite información solo por color y la vista conserva reflujo móvil.
