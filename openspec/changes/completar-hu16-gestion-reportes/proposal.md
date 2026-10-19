# Completar HU-16: gestionar reportes del proceso

## Objetivo

Alinear CU21–CU24 con Aequitas V2 y completar la autoría cuando un Administrador registra un reporte en un proceso asignado.

## Alcance

- CU21: Profesional asignado y Administrador pueden registrar; el profesional asignado permanece como autor del reporte y el usuario que lo registra queda identificado por separado.
- CU22/CU23: Profesional autor y Administrador pueden editar o dar de baja lógicamente, conservando el historial y excluyendo bajas de la consulta operativa.
- CU24: Profesional consulta sus reportes solo en procesos actualmente asignados; Coordinador, Directivo y Administrador consultan el legajo permitido. Secretario no consulta.
- Corregir las filas CU21–CU24 de la copia Markdown y las notas existentes en `docs/transcripcion_de_docs.md` para reflejar la matriz online.
- Agregar verificaciones PHPUnit y recorrer en navegador UI y rutas directas por rol.

## Fuera de alcance

- Cambios a la hoja online, restauración de reportes, eliminación física y reimplementación del historial CU22.

## Criterios de salida

- Un Profesional asignado puede registrar en su proceso; otros profesionales y roles sin CU21 no pueden hacerlo.
- El Administrador puede registrar en un proceso con profesional asignado; el reporte queda atribuido a ese profesional y conserva quién lo registró.
- CU22/CU23 aceptan únicamente autor o Administrador, también por petición directa; la baja es lógica y mantiene versiones.
- CU24 respeta roles, asignación actual, autoría profesional y proceso anidado; las bajas no aparecen en legajo ni admiten historial directo.
- Pruebas PHPUnit, Pint, build de assets, flujos reales de navegador por rol y `git diff --check` pasan.
- Rama revisada y fusionada localmente en `dev`, sin push, con árbol limpio y rama de tarea conservada.

## Riesgos

- Sin separar al profesional autor del usuario que registra, los reportes creados por Administrador quedarían ocultos para el profesional asignado y mostrarían al Administrador como profesional.
- Las consultas directas pueden exponer versiones si omiten el proceso, el rol, la autoría o la asignación actual.
- La matriz online y su copia local actualmente difieren en CU21 y en las notas de auditoría; solo se actualizará la copia local.
