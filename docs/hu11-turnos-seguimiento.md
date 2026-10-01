# HU-11 — Gestión de Turnos Internos y de Seguimiento (CU4, CU4.1, CU6, CU7)

## Objetivo

Permitir a **Secretario** y **Administrador** crear y reprogramar turnos internos (`consulta_inicial`) y de seguimiento, y cancelarlos sin borrar el registro. Cumple HU-11 / CU4, CU4.1, CU6 y CU7 de la Matriz Aequitas V2: alta vinculada a cliente y proceso, validación de proceso activo, bloqueo de conflictos de agenda por rango, cancelación lógica que conserva historial y libera disponibilidad, autorización en 5 capas y notificaciones auditables.

Fuente funcional: `C:/Users/Owner/Desktop/ESTUDIO/analisis y diseño/hu11/CasodeUso11.pdf` y Matriz V2 (`docs/aequitas-matriz-modulos-funciones-permisos-v2.md`). Reglas: `docs/transcripcion_de_docs.md §Turnos` y `openspec/specs/gestion-juridica/spec.md §Turnos`. Los externos (CU5/CU6.1) pertenecen a HU-13 y aquí solo se contemplan como bloqueo.

## Alcance implementado

- Alta de `consulta_inicial` (proceso opcional) y `seguimiento` (proceso activo obligatorio).
- Reprogramación CU6: cambia `fecha_hora` y libera la disponibilidad anterior.
- Cancelación CU7: `estado=cancelado`, sin `delete()` ni `SoftDeletes`; el registro queda en historial y el horario se libera.
- Conflicto por rango de 60 min (`Turno::DURACION_MINUTOS`) + bloqueo por externo: un externo activo bloquea todo el día del profesional, y un nuevo externo choca con cualquier turno activo ese día.
- Permisos Spatie existentes (`RoleSeeder.php`): Secretario/Admin escriben (`agendar_turnos_internos/seguimiento`, `modificar_turnos`, `eliminar_turnos`); Profesional/Coordinador solo `ver_agenda_profesional` (CU8); Directivo sin acceso a turnos. Validado en ruta (`permission`), policy, request (`authorize`), controlador (`authorize`) y UI (`@can`).
- Notificación auditable DB + intento de envío: `notificaciones` (`canal=email`, `user_id=profesional`, `cliente_id`, `mensaje`, `fecha_envio`, `estado=enviado/fallido`) + `Mail::raw` a profesional/cliente (log en local, Brevo/SMTP según `MAIL_*`). Si el envío falla queda `fallido` sin revertir el turno.
- UI Blade resource (decisión: no Livewire en esta iteración) + agenda CU8 filtrable.

## Cómo se hizo (paso a paso)

1. **Relevamiento read-only** del proyecto real `C:/laragon/www/aequitas-roque_gonzales` (Laravel 12, Spatie, Breeze): `Turno` sin `estado`, `TurnoController` scaffold vacío, `Store/UpdateTurnoRequest` sin validación de proceso activo ni conflicto, `routes/web.php` sin rutas `turnos`, `resources/views` sin `turnos/`, `tests` sin `TurnoTest`.
2. **Migración reversible** `2026_10_01_000007_add_estado_to_turnos_table.php`: `estado string(20) default programado` + índice `(profesional_id,fecha_hora,estado)`; backfill existentes→`programado` y `deleted_at NOT NULL`→`cancelado`; `down()` elimina índice y columna.
3. **Modelo** `app/Models/Turno.php`: `ESTADOS=[programado,cancelado]`, `DURACION_MINUTOS=60`, `PROCESO_ESTADOS_INACTIVOS=[finalizado,rechazado]`; `fillable+=estado`; `scopeActivos/scopeCancelados`, `isCancelado/isProgramado`, `cancelar()` (update, nunca delete); `existeConflicto(profesionalId, fechaHora, excluirId, esExternoNuevo)` con ventana `[inicio-60min, fin]` + chequeo `whereDate` para externos.
4. **Requests**: `Store` exige `fecha_hora after:now`, `seguimiento→proceso required + estado no inactivo`, coherencia `proceso.cliente_id/profesional_id`, conflicto `existeConflicto()`; `Update` replica con fallback a valores del modelo (`input(..., turno->...)`), excluye `id` actual y valida fecha pasada solo si se envía `fecha_hora`.
5. **Controller** `TurnoController.php`: `index` (`visibleTo` + filtros estado/tipo/profesional + `with`), `agenda` CU8 (profesional forzado a sí mismo si tiene rol Profesional), `create/store` (fuerza `estado=programado`, transacción + `registrarNotificacion(creado)`), `edit/update` (bloquea edit de cancelado, transacción + notificación `reprogramado de X a Y`), `destroy` (`cancelar()` + notificación `cancelado`); `registrarNotificacion()` privada crea `Notificacion` y prueba `Mail::raw` con `try/catch`→`fallido` + `Log::warning`.
6. **Rutas** `routes/web.php`: `/agenda` + resource manual `/turnos` con `permission:ver_agenda_profesional | agendar_internos|seguimiento | modificar_turnos | eliminar_turnos` (externos no se exponen en UI HU-11).
7. **Vistas** `resources/views/turnos/`: `_form` (selects cliente/profesional/proceso, tipo solo internos, `datetime-local`), `index` (tabla + badges + filtros + confirm cancelación), `create/edit/show/agenda`; link `Turnos` en `navigation.blade.php` con `@can('ver_agenda_profesional')`.
8. **Factory/Tests**: `TurnoFactory` (`estado=programado`, fecha futura); `tests/Feature/TurnoTest.php` 8 casos con `Mail::fake()` y `RefreshDatabase` (sqlite `:memory:` según `phpunit.xml`).
9. **DB local**: `.env` pasado de `mysql (host=mysql/Sail)` a `sqlite` + `database/database.sqlite` + `migrate --force` (27 tablas). `.env` y `*.sqlite` están gitignorados, por lo que es solo entorno local.
10. **Verificación**: `php artisan test --filter=TurnoTest` 8 passed; suite completa 113 passed.

## Componentes implementados

### 1. Modelo y migración

**Modelo** `app/Models/Turno.php`
* `const TIPOS`, `ESTADOS`, `DURACION_MINUTOS=60`, `PROCESO_ESTADOS_INACTIVOS`
* `fillable += estado`, `casts fecha_hora=>datetime, es_externo=>boolean`
* `scopeActivos/Cancelados`, `cancelar()`, `existeConflicto()` (rango + externo por día)

**Migración** `database/migrations/2026_10_01_000007_add_estado_to_turnos_table.php`
```php
Schema::table('turnos', function (Blueprint $table) {
    $table->string('estado', 20)->default('programado')->after('tipo');
    $table->index(['profesional_id', 'fecha_hora', 'estado'], 'turnos_prof_fecha_estado_idx');
});
DB::table('turnos')->whereNull('estado')->orWhere('estado', '')->update(['estado' => 'programado']);
DB::table('turnos')->whereNotNull('deleted_at')->update(['estado' => 'cancelado']);
// down(): dropIndex + dropColumn
```

### 2. Validación y autorización

**`StoreTurnoRequest`**: `authorize=can(create)`, `rules` con `exists...whereNull(deleted_at)` + `fecha_hora after:now`; `after()` valida rol Profesional, coherencia `tipo/es_externo/detalle`, `proceso.cliente/profesional`, `seguimiento→proceso activo`, conflicto.

**`UpdateTurnoRequest`**: `authorize=can(update, turno)`, `sometimes`; `after()` con fallback al modelo y `existeConflicto(..., turno->id, ...)`.

**Policy** `app/Policies/TurnoPolicy.php` (existente, sin cambios): `viewAny/view=ver_agenda_profesional` (+ filtro propio para Profesional), `create=canAny(agendar_*)`, `update=modificar_turnos(_externos)`, `delete=eliminar_turnos`.

### 3. Controlador + notificaciones

`app/Http/Controllers/TurnoController.php`: transacciones `DB::transaction`, `authorize()` en cada acción, `Mail::raw` a `[profesional.email, cliente.correo]` con asunto `Aequitas — turno {creado/reprogramado/cancelado}`.

### 4. Vistas, rutas y menú

Rutas y Blade según §Cómo se hizo 6–7. Sin Livewire. Cancelación con `onsubmit confirm()`.

### 5. Pruebas

**Tests** `tests/Feature/TurnoTest.php`
* Secretario crea `consulta_inicial` sin proceso + genera `notificaciones`
* `seguimiento` sin proceso 422, con proceso `admitido` 201, con proceso `finalizado` 422
* Proceso de otro cliente 422
* Misma `fecha_hora` 422, solapado +30min 422, +2h 201
* Externo mismo día bloquea interno 422
* Reprogramación mueve fecha y libera anterior (re-agendar anterior 201)
* Cancelación deja `estado=cancelado`, `deleted_at=null`, libera horario
* Profesional/Coordinador `index` 200 + `store` 403; Directivo `index` 403

Ejecución:
```
php artisan test --filter=TurnoTest   # 8 passed, 32 assertions
php artisan test                      # 113 passed, 561 assertions
```

## Verificación de migraciones

`DB_CONNECTION=sqlite php artisan migrate --force` ejecuta `000007` con `DONE`. `migrate:status` 23 `Ran`, `db:show` sqlite 27 tablas. En MySQL/Sail original fallaba por `DB_HOST=mysql` inexistente en Laragon; se migró a sqlite local (ver `.env`, ignorado por git).

## Trazabilidad HU-11

| Requerimiento PDF | Dónde quedó |
|---|---|
| CU4 alta vinculada a cliente+proceso | `store()` + `StoreTurnoRequest` + test creación |
| CU4.1 seguimiento exige proceso activo | validación `tipo=seguimiento→proceso required + no finalizado/rechazado` + tests |
| CU6 reprograma y libera anterior | `update()` + notificación con fecha anterior + test re-agenda |
| CU7 cancela sin borrar, conserva historial y libera | `cancelar()` + `destroy()` sin `delete()` + test `estado=cancelado` + re-agenda |
| Conflicto mismo profesional + externo bloquea día | `existeConflicto()` rango 60min + `whereDate` externo + tests |
| Solo Sec/Admin escriben, Prof/Coord consultan, Directivo nada | `permission` en rutas + policy + `authorize` + `@can` + test roles |
| UI cliente/profesional/proceso/tipo/fecha/estado + confirmar cancelación | `turnos/index/show/_form` + confirm |
| Notificación auditable crear/reprogramar/cancelar | `registrarNotificacion()` DB + `Mail::raw` + `Notificacion estado` |
| DoD rutas/UI/autorización/validaciones/notificaciones/pruebas | Ver secciones 1–5 |
