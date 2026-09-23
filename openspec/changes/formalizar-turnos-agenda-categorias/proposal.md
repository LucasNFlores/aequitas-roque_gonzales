# Formalizar turnos, agenda y categorías documentales

## Objetivo

Definir un plan de implementación verificable para los flujos de turnos, disponibilidad y categorías documentales que permanecen pendientes. El plan convierte las reglas ya aprobadas en la matriz Aequitas V2 y en la especificación base en decisiones técnicas, criterios de aceptación y tareas aplicables.

## Autoridad funcional

- **Fuente de verdad:** [Aequitas - Matriz de Módulos, Funciones y Permisos por Rol V2](https://docs.google.com/spreadsheets/d/13L_GwiJ1DvFIU2CaaXYfjaDDXHQEhSmcOST4z9bl1Oc/edit), pestaña homónima.
- **Especificación vigente:** `openspec/specs/gestion-juridica/spec.md`.
- **Documentación ampliada:** `docs/transcripcion_de_docs.md` y `docs/explicacion_sistema.md`.

La matriz contiene 40 casos de uso y cinco roles. El Administrador es superadministrador.

## Problema

La especificación base ya describe las reglas funcionales, pero el cambio global `completar-sistema-gestion-juridica` no permite implementar estos módulos de forma segura: mezcla trabajo histórico y pendiente, y no detalla cómo resolver cancelaciones, conflictos de agenda ni la transición desde `documentos.tipo_documento`.

## Alcance

- Planificar CU4, CU4.1, CU5, CU6, CU6.1, CU7 y CU8: turnos internos, seguimiento, externos, reprogramación, cancelación y consulta de agenda.
- Planificar CU37: gestión de categorías documentales.
- Precisar autorización, persistencia, consultas, interfaz, auditoría de notificaciones, pruebas y migraciones reversibles.
- Mantener la información histórica de turnos y documentos.

## Fuera de alcance

- Modificar la matriz de Google Drive, la especificación base o los permisos funcionales ya aprobados.
- Implementar código, migraciones, rutas o vistas como parte de este cambio de planificación.
- Implementar directamente proveedores externos como Brevo; el módulo debe emitir el registro/evento auditable que consume el flujo de notificaciones.
- Reemplazar o reescribir el cambio histórico `completar-sistema-gestion-juridica`.

## Decisiones confirmadas

| Capacidad | Roles autorizados además de Administrador |
| --- | --- |
| CU4, CU4.1, CU5, CU6, CU6.1, CU7 | Secretario |
| CU8 | Secretario, Profesional, Coordinador |
| CU37 | Coordinador |

- Los turnos externos bloquean la jornada completa del profesional y requieren detalle.
- La reprogramación libera la disponibilidad anterior solo después de validar la nueva.
- La cancelación conserva el turno con estado `cancelado`; no equivale a eliminarlo física ni lógicamente.
- La agenda del Profesional se limita a sus propios turnos; no puede ampliarse por parámetros de la petición.
- Las categorías se administran mediante baja lógica, sin borrar documentos ni archivos asociados.

## Áreas afectadas al aplicar

- `app/Models/Turno.php`, `app/Policies/TurnoPolicy.php`, `app/Http/Requests/*TurnoRequest.php` y `TurnoController`.
- Rutas, interfaz de agenda y pruebas de turnos.
- `Documento`, su controlador y el nuevo modelo/relación de categoría documental.
- Migraciones, seeders, policies, Form Requests, notificaciones y pruebas relacionadas.

## Criterio de salida

El cambio estará listo para aplicar cuando el diseño y las tareas permitan implementar cada CU indicado sin decisiones abiertas sobre permisos, conservación de datos, conflictos de agenda, alcance del Profesional, migración de documentos o pruebas exigidas.

## Riesgos

- Un uso de `SoftDeletes` para cancelar turnos ocultaría el historial operativo: se requiere estado explícito.
- La ausencia de duración no permite inferir solapamientos horarios: solo se valida coincidencia exacta y bloqueo diario externo.
- `tipo_documento` contiene datos históricos sin relación normalizada: una migración automática no debe asignar categorías ambiguas.
- Las comprobaciones de disponibilidad pueden competir entre solicitudes simultáneas: el diseño exige validación transaccional al persistir.
