# Completar la búsqueda y los filtros de clientes (CU 28)

## Motivo

El listado autoriza CU 28, pero solo pagina los clientes y no ofrece búsqueda ni filtro por el estado de admisión. La matriz Aequitas V2 asigna CU 28 a Secretario, Coordinador, Directivo y Administrador, y excluye a Profesional.

Fuente funcional prioritaria: [Matriz Aequitas V2, pestaña principal](https://docs.google.com/spreadsheets/d/13L_GwiJ1DvFIU2CaaXYfjaDDXHQEhSmcOST4z9bl1Oc/edit#gid=708841836). La especificación relacionada es `openspec/specs/gestion-juridica/spec.md`; la copia local de la matriz y `docs/` se consultaron como fuentes complementarias.

## Alcance

- Buscar sin distinguir mayúsculas por nombre, apellido o DNI.
- Filtrar por estado de proceso y combinarlo con la búsqueda.
- Conservar filtros y orden estable al paginar.
- Mostrar un estado vacío reconocible y una acción clara para limpiar filtros.
- Asegurar acceso al listado mediante CU 28, también por URL directa; el legajo asignado de Profesional permanece bajo CU 18.
- Añadir pruebas backend focalizadas y validar el recorrido real de UI.

## Fuera de alcance

Cambios a permisos que no sean necesarios para alinear CU 28 con la matriz.

## Criterios de salida

La búsqueda, filtro, combinación, paginación, vacío, limpieza y matriz de roles quedan cubiertos por pruebas; la UI se recorre manualmente en navegador para cada flujo requerido. Pint y build frontend finalizan sin errores. El diff se revisa y se integra en `dev` solo después de las validaciones y un árbol limpio.

## Riesgos

- Un cliente con procesos en distintos estados aparecerá en cada estado que coincida con al menos uno de sus procesos, según decisión funcional confirmada.
- `dev` local contiene 18 commits que todavía no están en `origin/dev`; el cambio se integrará solo en `dev` local y no se hará push.
