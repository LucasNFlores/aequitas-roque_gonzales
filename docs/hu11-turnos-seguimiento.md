# HU-11 — Turnos internos y de seguimiento

## Alcance

Implementa consulta inicial, seguimiento de proceso activo, reprogramación, cancelación histórica y consulta de agenda (CU4, CU4.1, CU6, CU7 y CU8). Secretario y Administrador gestionan turnos internos; Profesional y Coordinador consultan la agenda conforme a sus permisos. Directivo no tiene acceso. Los compromisos externos son de HU-13 y aquí solo bloquean el día del profesional.

Las notificaciones no forman parte de esta entrega. La tarjeta para retomarlas está en [HU-11 — Notificaciones auditables de turnos](https://trello.com/c/Iv2kJ7WQ/74-hu-11-notificaciones-auditables-de-turnos).

## Comportamiento

- Una consulta inicial puede no estar asociada a proceso; un seguimiento requiere proceso activo y coherencia de cliente y profesional.
- Dos turnos internos no pueden compartir la misma fecha y hora. No se asigna duración ni se bloquean horarios adyacentes sin definición funcional.
- Un compromiso externo activo bloquea el día completo del profesional.
- Alta, reprogramación y cancelación se ejecutan en transacción y serializan operaciones por profesional. Reprogramar valida la nueva disponibilidad antes de cambiar la fecha anterior.
- Cancelar marca `estado=cancelado`; no elimina ni oculta el registro y libera la disponibilidad. Un turno cancelado no se puede reprogramar.
- El alcance del Profesional se aplica en la consulta antes de filtros del usuario y no puede ampliarse con parámetros Livewire.
- La agenda abre por defecto un rango futuro de 30 días y limita cada consulta a un máximo de 90 días.
- La cancelación desde el detalle solicita confirmación explícita antes de cambiar el estado.
- Las pantallas interactivas de listado, agenda, alta, reprogramación y detalle usan componentes Livewire, con autorización y validación del lado servidor.
- HU-11 no envía correo ni persiste filas en `notificaciones`.

## Componentes

- `app/Models/Turno.php`: estados, scopes, visibilidad y reglas de conflicto exacto/externo.
- `app/Policies/TurnoPolicy.php`: autorización por operación y estado.
- `app/Services/Turnos/TurnoScheduler.php`: alta, reprogramación y cancelación transaccionales.
- `app/Livewire/Turnos/`: listado, agenda, formulario y detalle.
- `resources/views/turnos/`: páginas Livewire y `resources/views/livewire/turnos/`: interfaces interactivas.
- `routes/web.php`: rutas de lectura/página con middleware de permisos; las mutaciones se procesan mediante Livewire y se autorizan en servidor.
- `tests/Feature/TurnoTest.php`: flujos, reglas, permisos y alcance de agenda.

## Verificación

Ejecutar en Docker la prueba focalizada `php artisan test --compact tests/Feature/TurnoTest.php`, Pint para archivos PHP modificados y `git diff --check`. Además, recorrer en navegador las pantallas de agenda/listado, alta, reprogramación, detalle y cancelación con roles autorizados y verificar una operación denegada para roles de solo lectura.

La fuente funcional es la Matriz Aequitas V2, junto con `docs/transcripcion_de_docs.md` y `openspec/specs/gestion-juridica/spec.md`. El diseño de esta entrega está en `openspec/changes/formalizar-turnos-agenda-categorias/`.
