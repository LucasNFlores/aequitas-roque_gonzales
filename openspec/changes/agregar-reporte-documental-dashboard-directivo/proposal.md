# Propuesta: informe PDF documental y dashboard del Directivo

## Problema

La gestión de categorías y documentos ya forma parte del sistema, pero la rama dev no ofrece un resumen PDF de documentos agrupados por categoría. CU 40 requiere precisar el alcance del Directivo y separar sus métricas estratégicas de las colas operativas de otros roles.

## Alcance

- **CU 39 — Generar Informe PDF de Documentos por Categoría:** generar un resumen por categoría para un período, con filtros por estado y categoría. El informe incluye cantidades de documentos y última fecha de carga; no adjunta archivos individuales. Se autoriza al Coordinador, conforme al alcance de CU 37, y al Administrador por su condición de superadministrador.
- **CU 40 — Consultar Dashboard del Directivo:** presentar una variante estratégica por rol dentro del dashboard operativo común de HU-22. El Directivo y el Administrador pueden consultar las métricas aprobadas de procesos, clientes, reportes y cuentas, más la distribución de procesos activos por servicio.
- Registrar ambos casos de uso en la matriz de Drive, su copia Markdown y la documentación funcional.

## Estado actual

La rama de referencia feature/reportes-documentos contiene una vista web de reporte y salidas PDF y CSV, pero no está integrada en la rama dev actual. Esta propuesta formaliza el PDF solicitado; la inclusión de CSV queda pendiente de decisión. La rama dev contiene la gestión de categorías CU 37 y el dashboard HU-22. La matriz online mantiene su nota de métricas CU 40 pendientes; el usuario aprobó para esta implementación las cuatro métricas y el gráfico documentados en el diseño, sin modificar la matriz externa.

## Restricciones

- La matriz online de Aequitas es la fuente funcional de verdad. El Administrador debe permanecer autorizado en todos los casos de uso.
- El informe por categoría no concede al Directivo los permisos CU 12 o CU 19 ni incluye el contenido de archivos.
- El dashboard no debe ampliar permisos de acceso a legajos, documentos o reportes. Sus indicadores deben limitarse a datos autorizados para el Directivo.
- No copiar el valor por defecto de período fijado temporalmente en la rama de referencia. El período predeterminado debe acordarse antes de implementar.

## Áreas afectadas

- OpenSpec de gestión jurídica y matriz de permisos.
- Documentación de requisitos y explicación del sistema.
- Rutas, controlador, vista web y plantilla PDF del informe documental.
- Vista y consultas del dashboard del Directivo.
- RoleSeeder, autorización, pruebas PHPUnit y verificación visual del PDF.

## Enfoque de implementación

Mantener la matriz online como fuente de permisos. Integrar el informe CU 39 con filtros validados y autorización basada en CU 37. Para CU 40, implementar la variante estratégica aprobada sin ampliar permisos; mantener el acceso del Administrador por superadministrador.

## Criterios de aceptación

- La matriz y su copia local contienen CU 39 y CU 40 con roles consistentes y Administrador autorizado en ambos.
- Coordinador y Administrador pueden generar el PDF agregado de CU 39 con los filtros aplicados; el Directivo no obtiene acceso al informe ni a los archivos por CU 18.
- El dashboard de CU 40 es propio del Directivo, mantiene a Administrador autorizado y muestra únicamente indicadores aprobados.
- Los indicadores del dashboard y el período predeterminado del informe quedan definidos antes de cerrar la implementación.
- Las rutas directas respetan las mismas autorizaciones que la interfaz.

## Riesgos y preguntas abiertas

- **Indicadores:** alcance aprobado para esta entrega y documentado en el diseño; la matriz online conserva su nota original hasta que el usuario solicite modificarla.
- **Exposición de datos:** métricas agregadas pueden revelar información fuera del alcance de un rol si las consultas no conservan el filtrado de autorización.
- **Período predeterminado:** la rama de referencia usa un período fijo temporal; debe sustituirse por una regla acordada.
- **CSV:** la rama de referencia ofrece CSV además de PDF; el alcance solicitado solo confirma PDF.
- **Estado de integración:** CU 39 continúa pendiente de integración; el dashboard operativo HU-22 ya está en dev y esta entrega completa la variante CU 40.
