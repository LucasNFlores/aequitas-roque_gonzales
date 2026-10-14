# Diseño: informe PDF documental y dashboard del Directivo

## CU 39 — Informe por categoría

La consulta agrupa documentos por categoría y cuenta las cargas realizadas en el período seleccionado. Admite filtro de período, estado de categoría y categoría específica. El resultado PDF resume cantidades y última fecha de carga por categoría. No lista nombres de archivo ni incorpora los PDF originales.

La autorización sigue CU 37: Coordinador y Administrador. El Directivo no obtiene el informe por tener acceso a consultar legajos (CU 18). La navegación, el controlador y la descarga directa del PDF deben aplicar la misma autorización. El Administrador conserva acceso universal.

El rango predeterminado y la decisión sobre exportación CSV quedan abiertos. No debe reutilizarse el período fijo que aparece en la rama de referencia.

## CU 40 — Dashboard del Directivo

El dashboard será una vista propia del Directivo y no un dashboard administrativo compartido con otros roles. El Administrador podrá acceder por superadministrador. La lista de indicadores y el período de consulta quedan pendientes de definición, por lo que no se fijan tarjetas, totales, gráficos ni fuentes de datos en esta propuesta.

Cada indicador deberá usar consultas limitadas al alcance del Directivo y no conceder acceso implícito a datos de documentos, archivos, legajos o reportes. La selección de indicadores puede requerir ajustar la matriz si necesita un permiso que todavía no posee el rol.

## Verificación prevista

- Probar el acceso al informe por interfaz y URL directa para Coordinador, Administrador y Directivo.
- Verificar que los filtros coincidan en la vista y en el PDF, que se incluyan las cantidades y fechas previstas y que no se adjunten archivos originales.
- Una vez definidos los indicadores, probar acceso al dashboard por Directivo y Administrador y denegación por roles no autorizados.
- Confirmar que las métricas no exponen información fuera de los permisos del Directivo.
