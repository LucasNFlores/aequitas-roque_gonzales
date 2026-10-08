# Corregir la precedencia de Administrador en roles múltiples

## Objetivo

Garantizar que una cuenta con los roles Administrador y Profesional ejerza las autorizaciones completas de Administrador en los 41 CU, sin quedar restringida por filtros de asignación propios del Profesional.

## Autoridad funcional

- **Fuente de verdad:** [Aequitas - Matriz de Módulos, Funciones y Permisos por Rol V2](https://docs.google.com/spreadsheets/d/13L_GwiJ1DvFIU2CaaXYfjaDDXHQEhSmcOST4z9bl1Oc/edit), pestaña homónima. Metadatos verificados; 41 CU vigentes; Administrador autorizado en los 41.
- **Especificación vigente:** `openspec/specs/gestion-juridica/spec.md`.
- **Documentación revisada:** `docs/aequitas-matriz-modulos-funciones-permisos-v2.md`, `docs/transcripcion_de_docs.md` y `docs/explicacion_sistema.md`.

## Problema

La administración de usuarios permite varios roles mediante `syncRoles`, pero algunas policies, scopes y consultas limitan por asignación al detectar `Profesional` antes de considerar Administrador. La cuenta combinada puede perder resultados y recibir 403 al consultar clientes, procesos, agenda, turnos, documentos, legajos, historial o reportes que Administrador tiene autorizados.

## Alcance

- Definir una precedencia explícita de Administrador sobre las restricciones de alcance del Profesional.
- Aplicarla de forma consistente en scopes, policies, controladores y componentes que filtran por usuario asignado.
- Mantener los permisos técnicos otorgados por Spatie y las autorizaciones de cada CU.
- Mantener Profesional sin Administrador limitado a sus procesos asignados, reportes propios y agenda propia.
- Cubrir los perfiles solo Administrador, solo Profesional y Administrador + Profesional con pruebas PHPUnit, incluyendo rutas directas y listados filtrados.

## Fuera de alcance

- Cambiar permisos de Secretario, Profesional, Coordinador o Directivo.
- Cambiar la matriz online, `RoleSeeder` o el alcance funcional de notificaciones.
- Integrar Brevo o alterar la secuencia de última fase del módulo de notificaciones.

## Criterio de salida

La cuenta combinada obtiene los resultados y las operaciones autorizadas a Administrador en listados, detalles, archivos, historiales, agenda y reportes; Profesional sin Administrador conserva sus límites; otras combinaciones y permisos permanecen sin ampliación. Las pruebas PHPUnit focalizadas, OpenSpec estricto, Pint y `git diff --check` terminan correctamente.

## Riesgos

- Un filtro de asignación omitido en una consulta secundaria puede seguir ocultando datos en legajos, agenda o reportes.
- Quitar restricciones al Profesional sin exigir explícitamente ausencia del rol Administrador podría ampliar permisos de otros perfiles múltiples.
- Resolver todo con un bypass de Gate podría ignorar las reglas por CU de las policies; la precedencia debe limitarse al alcance por asignación y mantener la comprobación de permisos.
- Varias asignaciones Spatie deben conservarse; no se reemplazarán ni se normalizarán roles como parte de este arreglo.
