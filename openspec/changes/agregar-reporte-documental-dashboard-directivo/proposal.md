# Propuesta: informe PDF documental y dashboard del Directivo

## Problema

La gestión de categorías y documentos ya forma parte del sistema, pero la rama dev no ofrece un resumen PDF de documentos agrupados por categoría ni un dashboard propio para el rol Directivo. La matriz debe registrar esas capacidades y separar con claridad el alcance de cada rol.

## Alcance

- **CU 39 — Generar Informe PDF de Documentos por Categoría:** generar un resumen por categoría para un período, con filtros por estado y categoría. El informe incluye cantidades de documentos y última fecha de carga; no adjunta archivos individuales. Se autoriza al Coordinador, conforme al alcance de CU 37, y al Administrador por su condición de superadministrador.
- **CU 40 — Consultar Dashboard del Directivo:** disponer de una vista propia para el rol Directivo. Los indicadores están pendientes de definición. El Administrador también puede acceder por su condición de superadministrador.
- Registrar ambos casos de uso en la matriz de Drive, su copia Markdown y la documentación funcional.

## Estado actual

La rama de referencia feature/reportes-documentos contiene una vista web de reporte y salidas PDF y CSV, pero no está integrada en la rama dev actual. Esta propuesta formaliza el PDF solicitado; la inclusión de CSV queda pendiente de decisión. La rama dev sí contiene la gestión de categorías de CU 37. No hay un dashboard específico del Directivo.

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

Formalizar primero CU 39 y CU 40 en la matriz y en esta propuesta. Integrar después el informe con filtros validados y autorización basada en CU 37. Definir con el usuario los indicadores y su alcance antes de implementar el dashboard. Mantener la autorización del Administrador como superadministrador sin duplicar ni restringir sus permisos.

## Criterios de aceptación

- La matriz y su copia local contienen CU 39 y CU 40 con roles consistentes y Administrador autorizado en ambos.
- Coordinador y Administrador pueden generar el PDF agregado de CU 39 con los filtros aplicados; el Directivo no obtiene acceso al informe ni a los archivos por CU 18.
- El dashboard de CU 40 es propio del Directivo, mantiene a Administrador autorizado y muestra únicamente indicadores aprobados.
- Los indicadores del dashboard y el período predeterminado del informe quedan definidos antes de cerrar la implementación.
- Las rutas directas respetan las mismas autorizaciones que la interfaz.

## Riesgos y preguntas abiertas

- **Indicadores:** falta definir métricas, períodos, filtros y nivel de detalle del dashboard. No se debe asumir un catálogo antes de acordarlo.
- **Exposición de datos:** métricas agregadas pueden revelar información fuera del alcance de un rol si las consultas no conservan el filtrado de autorización.
- **Período predeterminado:** la rama de referencia usa un período fijo temporal; debe sustituirse por una regla acordada.
- **CSV:** la rama de referencia ofrece CSV además de PDF; el alcance solicitado solo confirma PDF.
- **Estado de integración:** el reporte y el dashboard aún no forman parte de dev.
