---
name: aequitas-matrix-sync
description: Mantiene alineadas la matriz Aequitas de roles y permisos en Google Drive, su copia Markdown en docs/ y la implementación Laravel/OpenSpec.
compatibility: Codex y OpenCode; requiere una integración de Google Drive o un MCP equivalente para modificar la hoja.
metadata:
  source-of-truth: google-drive
  local-output: docs/aequitas-matriz-modulos-funciones-permisos-v2.md
---

# Sincronización de la matriz Aequitas

Usa esta skill cuando el usuario pida revisar, actualizar, sincronizar o validar la matriz **Aequitas - Matriz de Módulos, Funciones y Permisos por Rol V2**, su documentación Markdown o los permisos Laravel asociados.

## Artefactos canónicos

- **Google Drive:** [Aequitas - Matriz de Módulos, Funciones y Permisos por Rol V2](https://docs.google.com/spreadsheets/d/13L_GwiJ1DvFIU2CaaXYfjaDDXHQEhSmcOST4z9bl1Oc/edit).
- **Pestaña:** `Aequitas - Matriz de Módulos, Funciones y Permisos por Rol V2`.
- **Copia local:** `docs/aequitas-matriz-modulos-funciones-permisos-v2.md`.
- **Documentación ampliada:** `docs/transcripcion_de_docs.md` y `docs/explicacion_sistema.md`.
- **Especificación:** `openspec/specs/gestion-juridica/spec.md`.
- **Implementación de permisos:** `database/seeders/RoleSeeder.php`, rutas, policies y pruebas relacionadas.

Drive es la fuente de verdad funcional. La copia Markdown debe reflejar la pestaña completa, con sus módulos, códigos CU, descripciones, cinco roles y notas.

## Flujo

1. Revisar el estado del repositorio y conservar cambios no relacionados.
2. Buscar el archivo por título si no se proporcionó su URL o ID; leer sus metadatos antes de leer o escribir celdas.
3. Usar el nombre exacto de la pestaña confirmado por los metadatos. No asumir `Sheet1` ni rangos o columnas sin verificar.
4. Leer valores y metadatos de las celdas relevantes. Antes de modificar una celda existente, comprobar su valor actual, validación, formato y rango.
5. Comparar Drive contra la copia Markdown y contra `RoleSeeder`, OpenSpec, rutas, policies y pruebas. No inventar permisos técnicos a partir de nombres parecidos.
6. Si el usuario pidió refrescar la documentación desde Drive, actualizar el Markdown mediante un cambio reproducible y mantener el enlace desde `docs/transcripcion_de_docs.md`.
7. Si el usuario pidió modificar Drive, aplicar únicamente cambios explícitos y fundamentados en una matriz acordada. Usar una actualización por rangos precisa, conservar validaciones, casillas, formato y filas no afectadas, y leer la hoja de nuevo después de escribir.
8. Verificar que la copia local contiene todos los casos de uso de la pestaña, que no faltan códigos CU y que las columnas de roles coinciden.
9. Validar con `git diff --check` y ejecutar las pruebas PHP relacionadas cuando PHP esté disponible. Si no está disponible, informar el bloqueo y conservar las validaciones de lectura realizadas.

## Reglas de alineación

- La matriz actual contiene 40 casos de uso y los roles Secretario, Profesional, Coordinador, Directivo y Administrador.
- `Sí`/`No` en Markdown representa `TRUE`/`FALSE` de las casillas de Drive.
- El Administrador es superadministrador y debe quedar autorizado en todos los casos de uso, salvo que el usuario apruebe explícitamente una excepción en la matriz y en la implementación.
- `CU 31` y `CU 32` son capacidades transversales de autenticación y perfil; no obligan a crear un permiso técnico independiente si la aplicación ya las protege mediante autenticación y perfil.
- Los permisos técnicos de Spatie pueden incluir controles auxiliares de rutas o administración, como listar usuarios o editar roles; no exigir una correspondencia uno-a-uno entre permiso técnico y CU.
- Si Drive y el código contradicen la regla de superadministrador, detener la actualización bidireccional, mostrar la diferencia y pedir confirmación de la fuente que debe prevalecer.
- No sobrescribir la hoja completa para corregir una sola celda. No alterar orden, formato, validaciones, comentarios, columnas ni filas no solicitadas.

## Portabilidad entre agentes

- Mantener el frontmatter limitado a `name`, `description`, `compatibility` y `metadata`, campos compatibles con Codex y OpenCode.
- Esta skill vive en `.opencode/skills/aequitas-matrix-sync/SKILL.md`, formato nativo de OpenCode. No requiere modificar `opencode.json` para ser descubierta.
- No depender de nombres concretos de herramientas de Codex. Usar la integración de Google Drive/MCP disponible en el entorno; si no existe, hacer solo el análisis local y explicar qué conexión falta.
- No asumir que OpenCode dispone de las automatizaciones de Codex. Si se pide una sincronización recurrente, confirmar la cadencia y usar el mecanismo de automatización disponible en ese entorno.

## Resultado esperado

Informar siempre: archivo local actualizado, URL de Drive, rangos o casos modificados, diferencias encontradas, validaciones ejecutadas y bloqueos restantes. No afirmar que hubo sincronización si solo se generó una copia local.
