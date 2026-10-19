# Corregir la asignación profesional en CU 38

## Objetivo

Evitar que el alta de un proceso adicional persista un profesional sin aplicar las validaciones y efectos de CU 16.

## Autoridad funcional

- **Fuente prioritaria:** [Matriz Aequitas V2](https://docs.google.com/spreadsheets/d/13L_GwiJ1DvFIU2CaaXYfjaDDXHQEhSmcOST4z9bl1Oc/edit#gid=708841836). CU 38 autoriza Secretario y Administrador; CU 16 autoriza Secretario, Coordinador y Administrador.
- **Especificación:** `openspec/specs/gestion-juridica/spec.md`.
- **Documentación de referencia:** `docs/aequitas-matriz-modulos-funciones-permisos-v2.md` y documentación del proceso.

## Alcance

- Crear procesos adicionales sin profesional asignado.
- Rechazar cualquier `profesional_id` enviado al endpoint CU 38.
- Mantener la asignación posterior por CU 16, validando usuario activo, rol Profesional y habilitación para el servicio, y fijando honorarios al costo vigente.
- Verificar el rechazo de peticiones manipuladas, la compatibilidad, los honorarios y el alta sin profesional.

## Fuera de alcance

- Cambios de roles o permisos de la matriz.
- Modificaciones a turnos, clientes u otros casos de uso.

## Criterio de salida

CU 38 no persiste asignaciones profesionales; CU 16 conserva validaciones y actualiza profesional y honorarios atómicamente. Las pruebas automatizadas y la verificación real de interfaz confirman ambos flujos.

## Riesgos

- Una petición manipulada podría eludir controles visuales; por eso el servidor prohíbe el campo, y la acción de creación no persiste el atributo.
- El costo del servicio podría cambiar entre el alta y la asignación; CU 16 toma el costo vigente al ejecutar la asignación.
