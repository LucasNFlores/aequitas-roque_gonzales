# Gestionar compromisos externos del profesional (HU-13)

## Objetivo

Implementar CU5, CU6.1 y CU7 para registrar, reprogramar y cancelar compromisos externos de profesionales, conservando los datos históricos y protegiendo la disponibilidad diaria.

## Alcance

- Reutilizar `Turno` con `tipo=externo`, `es_externo=true` y `detalle_externo` obligatorio.
- Permitir escritura únicamente a Secretario y Administrador; CU8 solo concede consulta.
- Validar profesionales activos y relaciones cliente/proceso/profesional.
- Bloquear toda la fecha de un profesional con un compromiso externo programado.
- Reprogramar dentro de una transacción y excluir el propio turno al validar disponibilidad.
- Cancelar cambiando `estado` a `cancelado`, sin borrar ni usar SoftDeletes.
- Reutilizar el estado y el backfill existentes; no duplicar la migración agregada por HU-11.

## Fuera de alcance

- Turnos internos y de seguimiento, cubiertos por HU-11.
- Envío, auditoría y recordatorios de notificaciones, diferidos a una tarjeta futura de backlog por decisión del usuario.
- Cambios en la matriz Aequitas o en Trello.

## Criterio de salida

Los flujos de alta, reprogramación y cancelación funcionan desde el navegador, preservan historial y disponibilidad, y los roles sin escritura son rechazados desde interfaz, rutas y peticiones Livewire directas. Las pruebas automatizadas y la validación Docker pasan.

## Riesgos

- Una validación de fecha fuera de la transacción podría permitir reservas concurrentes; el scheduler debe conservar el bloqueo transaccional del profesional.
- Reutilizar permisos de CU8 para escritura habilitaría indebidamente a Profesional o Coordinador; las acciones externas requieren permisos específicos.
- Una cancelación física o lógica ocultaría el historial y el estado del turno.
