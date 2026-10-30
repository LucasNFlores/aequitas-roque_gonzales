# Guía de las tres pruebas

Documento de apoyo para ejecutar y explicar tres pruebas que ya existen en el proyecto. No requiere crear pruebas nuevas ni modificar código.

## 1. Entorno verificado

| Elemento | Valor |
| --- | --- |
| PHP | 8.3.30 |
| Laravel | 12.61.1 |
| PHPUnit | 11.5.55 |
| Base de datos de tests | SQLite en memoria (`phpunit.xml`) |
| Framework de tests | PHPUnit (no Pest) |
| Vite en tests | Deshabilitado por `tests/TestCase.php` con `withoutVite()` |

Antes de ejecutar:

```bash
php artisan config:clear
```

No hace falta levantar MySQL ni ejecutar `npm run build`. Cada prueba crea su propia base con `RefreshDatabase` y usa SQLite `:memory:`.

---

## 2. Resumen de las tres pruebas

| # | Prueba | Archivo | Comando | Resultado obtenido |
| --- | --- | --- | --- | --- |
| 1 | CRUD de cliente | `tests/Feature/ClienteTest.php` | `php vendor/bin/phpunit tests/Feature/ClienteTest.php --testdox --filter='test_administrador_can_crud_cliente'` | OK (1 test, 15 assertions) |
| 2 | Permisos por rol | `tests/Feature/RolePermissionTest.php` | `php vendor/bin/phpunit tests/Feature/RolePermissionTest.php --testdox --filter='test_permissions_control_the_existing_user_and_service_routes'` | OK (1 test, 4 assertions) |
| 3 | Reporte de documentos | `tests/Feature/ReporteDocumentoTest.php` | Comando detallado en la sección de la Prueba 3 | OK (3 tests, 29 assertions) |

Total de la demostración: **5 casos ejecutados y 48 aserciones aprobadas**.

---

## 3. Prueba 1 — CRUD de cliente

### Comando

```bash
php vendor/bin/phpunit tests/Feature/ClienteTest.php --testdox --filter='test_administrador_can_crud_cliente'
```

### Resultado obtenido

```text
Cliente (Tests\Feature\Cliente)
 ✔ Administrador can crud cliente

OK (1 test, 15 assertions)
```

### Qué hace

Comprueba que un usuario con rol **Administrador** puede realizar el ciclo completo de un cliente:

1. Listar clientes.
2. Abrir el formulario de alta.
3. Crear un cliente.
4. Ver el detalle.
5. Abrir el formulario de edición.
6. Actualizar el cliente.
7. Eliminarlo con baja lógica.

La prueba también verifica que el cliente realmente se guarda en la base y que la eliminación no borra el registro físicamente.

### Cómo lo hace

1. Usa `RefreshDatabase` para migrar una base limpia antes del test.
2. Ejecuta `RoleSeeder` para crear los roles y permisos.
3. Ejecuta `EstadoProcesoSeeder` y `ServicioSeeder` porque el alta de cliente crea registros relacionados.
4. Crea un usuario con `User::factory()->create()` y le asigna el rol con `assignRole('Administrador')`.
5. Autentica con `actingAs($admin)`, sin pasar por el formulario de login.
6. Recorre las rutas del resource:
   - `GET clientes.index` → `ClienteController@index`.
   - `GET clientes.create` → `ClienteController@create`.
   - `POST clientes.store` → `ClienteController@store`.
   - `GET clientes.show` → `ClienteController@show`.
   - `GET clientes.edit` → `ClienteController@edit`.
   - `PUT clientes.update` → `ClienteController@update`.
   - `DELETE clientes.destroy` → `ClienteController@destroy`.
7. El controller autoriza cada operación con `ClientePolicy`:
   - `viewAny` exige `listar_filtrar_clientes`.
   - `create` exige `registrar_clientes`.
   - `update` exige `modificar_clientes`.
   - `delete` exige `eliminar_clientes`.
8. La validación del alta y la edición se hace con `StoreClienteRequest` y `UpdateClienteRequest`.
9. El alta ejecuta `RegistrarClienteInicial`, que crea cliente, proceso inicial, turno y notificación dentro de una transacción.
10. Antes de eliminar, la prueba marca el proceso como `finalizado` porque la policy impide dar de baja un cliente con procesos activos.
11. Las aserciones principales son:
    - `assertOk()`: la pantalla responde 200.
    - `assertRedirect(route('clientes.index'))`: el flujo continúa correctamente.
    - `assertDatabaseHas('clientes', [...])`: el registro se persistió.
    - `assertSee('Juan')`: la vista muestra el dato esperado.
    - `assertSoftDeleted('clientes', [...])`: la baja es lógica.

### Archivos que intervienen

| Responsabilidad | Archivo |
| --- | --- |
| Definición de la prueba | `tests/Feature/ClienteTest.php:42` |
| Rutas del resource | `routes/web.php:163` |
| Controller | `app/Http/Controllers/ClienteController.php` |
| Policy | `app/Policies/ClientePolicy.php:28` |
| Validación del alta | `app/Http/Requests/StoreClienteRequest.php:9` |
| Validación de la edición | `app/Http/Requests/UpdateClienteRequest.php:10` |
| Alta transaccional | `app/Actions/RegistrarClienteInicial.php:18` |

### Con qué herramientas

- **PHPUnit 11.5** para definir y ejecutar la prueba.
- **Laravel HTTP Testing** para `actingAs`, `get`, `post`, `put` y `delete`.
- **RefreshDatabase** para aislar la base.
- **SQLite `:memory:`** como base de datos de test.
- **Faker + factories** para crear datos falsos.
- **Seeders** para roles, estados y servicios.
- **Spatie Laravel Permission** para `assignRole`.
- **Asserts de Laravel**: `assertOk`, `assertRedirect`, `assertSee`, `assertDatabaseHas`, `assertSoftDeleted`.
- **PowerShell o terminal de VS Code** para ejecutar el comando.

### Explicación oral sugerida

> “Esta prueba verifica el CRUD completo de clientes. Prepara un Administrador con su rol y recorre todas las rutas del resource: listar, crear, ver, editar, actualizar y eliminar. Comprueba que las pantallas carguen, que el cliente se guarde en la base, que la edición se persista y que la eliminación sea lógica. Antes de borrar finaliza el proceso porque la policy bloquea la baja de clientes con procesos activos. Usa PHPUnit con las utilidades de testing de Laravel, SQLite en memoria, factories, seeders y Spatie Permission.”

### Preguntas posibles

**¿Por qué finaliza el proceso antes de eliminar?**
Porque `ClientePolicy::delete` exige `eliminar_clientes` y además verifica que no existan procesos activos. Sin ese paso, la baja sería rechazada.

**¿La prueba usa un navegador real?**
No. Usa el cliente HTTP de Laravel, que ejecuta el mismo stack de rutas, middleware, controller y policy sin abrir un navegador.

---

## 4. Prueba 2 — Permisos por rol

### Comando

```bash
php vendor/bin/phpunit tests/Feature/RolePermissionTest.php --testdox --filter='test_permissions_control_the_existing_user_and_service_routes'
```

### Resultado obtenido

```text
Role Permission (Tests\Feature\RolePermission)
 ✔ Permissions control the existing user and service routes

OK (1 test, 4 assertions)
```

### Qué hace

Demuestra que el acceso a las rutas depende del rol del usuario:

- Un **Directivo** puede entrar a `/usuarios` y `/servicios` y recibe `200`.
- Un **Profesional** recibe `403` en ambas rutas.

La prueba confirma que la autorización se resuelve en el servidor y no solo ocultando enlaces en la interfaz.

### Cómo lo hace

1. Ejecuta `RoleSeeder`, que crea los cinco roles y sincroniza la matriz de permisos.
2. Crea un usuario con `User::factory()->create()` y le asigna `Directivo`.
3. Crea otro usuario y le asigna `Profesional`.
4. Realiza `GET` a `route('users.index')` y `route('servicios.index')` con cada usuario.
5. Las rutas están protegidas por middleware de Spatie:
   - `/usuarios` exige `permission:listar_usuarios`.
   - `/servicios` exige `permission:gestionar_servicios`.
6. El `RoleSeeder` asigna esos permisos al Directivo y no al Profesional.
7. Las aserciones verifican el código de estado:
   - `assertOk()` espera `200`.
   - `assertForbidden()` espera `403`.

### Permisos relevantes de la matriz

| Permiso | Directivo | Profesional |
| --- | --- | --- |
| `listar_usuarios` | Sí | No |
| `gestionar_servicios` | Sí | No |

### Archivos que intervienen

| Responsabilidad | Archivo |
| --- | --- |
| Definición de la prueba | `tests/Feature/RolePermissionTest.php:67` |
| Matriz de roles y permisos | `database/seeders/RoleSeeder.php:11` |
| Ruta de usuarios | `routes/web.php:48` |
| Ruta de servicios | `routes/web.php:81` |

### Con qué herramientas

- **PHPUnit 11.5**.
- **Laravel HTTP Testing** para `actingAs` y `get`.
- **Spatie Laravel Permission 7.4** para roles, permisos y middleware.
- **RoleSeeder** como fuente de verdad de la matriz.
- **RefreshDatabase + SQLite `:memory:`**.
- **Asserts**: `assertOk` y `assertForbidden`.

### Explicación oral sugerida

> “Esta prueba valida la seguridad por roles. Carga la matriz de permisos con el seeder, crea un Directivo y un Profesional, y comprueba que el Directivo accede a usuarios y servicios mientras el Profesional recibe 403. Lo importante es que la autorización se resuelve en el servidor mediante middleware y permisos, no ocultando botones. Usa Spatie Permission para los roles y las aserciones HTTP de Laravel para verificar los códigos de estado.”

### Preguntas posibles

**¿Dónde se define que el Directivo puede entrar?**
En `database/seeders/RoleSeeder.php`, dentro de la matriz `PERMISSIONS_BY_ROLE`. El Directivo tiene `listar_usuarios` y `gestionar_servicios`; el Profesional no.

**¿Qué diferencia hay entre 403 y 302?**
`403` significa autenticado pero sin permiso. `302` se usa normalmente para redirigir a login cuando el usuario no está autenticado.

---

## 5. Prueba 3 — Reporte de documentos

### Comando

```bash
php vendor/bin/phpunit tests/Feature/ReporteDocumentoTest.php --testdox --filter='test_manual_period_filters_documents_through_the_end_of_the_final_day|test_invalid_periods_redirect_back_with_validation_errors|test_coordinator_can_download_pdf_with_the_same_filters'
```

Si la terminal no acepta el pipe dentro de `--filter`, ejecutá cada método por separado:

```bash
php vendor/bin/phpunit tests/Feature/ReporteDocumentoTest.php --testdox --filter='test_manual_period_filters_documents_through_the_end_of_the_final_day'
php vendor/bin/phpunit tests/Feature/ReporteDocumentoTest.php --testdox --filter='test_invalid_periods_redirect_back_with_validation_errors'
php vendor/bin/phpunit tests/Feature/ReporteDocumentoTest.php --testdox --filter='test_coordinator_can_download_pdf_with_the_same_filters'
```

### Resultado obtenido

```text
Reporte Documento (Tests\Feature\ReporteDocumento)
 ✔ Manual period filters documents through the end of the final day
 ✔ Invalid periods redirect back with validation errors
 ✔ Coordinator can download pdf with the same filters

OK (3 tests, 29 assertions)
```

### Qué hace

Ejecuta tres casos del informe de documentos:

1. **Período manual:** verifica que un rango de fechas incluya los documentos hasta el último instante del día final.
2. **Períodos inválidos:** verifica que un rango incompleto, invertido, inexistente o demasiado largo vuelva al formulario con errores.
3. **Descarga PDF:** verifica que el Coordinador descargue un PDF real usando los mismos filtros.

### Cómo lo hace

### Preparación común

1. `setUp()` ejecuta `RoleSeeder`.
2. `travelTo(Carbon::parse('2026-10-20 12:00:00'))` congela la fecha para que el resultado sea determinístico.
3. Cada test crea un Coordinador con factory y le asigna el rol.

### Caso 1: período manual

1. Crea una categoría `Judicial`.
2. Crea dos documentos en los bordes del período:
   - `2026-09-01 00:00:00`.
   - `2026-09-30 23:59:59.999999`.
3. Pide `reportes.documentos.index` con `fecha_desde=2026-09-01` y `fecha_hasta=2026-09-30`.
4. `ReporteDocumentoController::filtros()` valida las fechas.
5. El controller calcula `$hastaExclusivo = fecha_hasta + 1 día` y consulta con:
   - `created_at >= desde`.
   - `created_at < hastaExclusivo`.
6. Por eso incluye todo el último día, incluso `23:59:59.999999`.
7. `assertSee('>2</td>')` comprueba que contó los dos documentos.

### Caso 2: períodos inválidos

1. Recorre varios escenarios:
   - Falta `fecha_hasta`.
   - Falta `fecha_desde`.
   - Fecha inexistente (`2026-02-30`).
   - Fechas invertidas.
   - Período mayor a 366 días.
2. Cada solicitud debe redirigir a `reportes.documentos.index`.
3. `assertSessionHasErrors($campo)` verifica que el error aparezca en el campo correcto.
4. El controller lanza `ValidationException` con `redirectTo(...)`.

### Caso 3: descarga PDF

1. Pide `reportes.documentos.pdf` con fechas y estado `activas`.
2. El controller autoriza con `viewAny` sobre `CategoriaDocumento`.
3. Reutiliza la misma validación y la misma consulta que el informe HTML.
4. Genera el PDF con DomPDF desde `reportes.documentos-pdf`.
5. Las aserciones verifican:
   - `assertOk()`: respuesta 200.
   - `Content-Type: application/pdf`.
   - El contenido empieza con `%PDF-`.

### Archivos que intervienen

| Responsabilidad | Archivo |
| --- | --- |
| Definición de las pruebas | `tests/Feature/ReporteDocumentoTest.php:57`, `:74`, `:95` |
| Rutas del informe | `routes/web.php:116` y `routes/web.php:117` |
| Controller | `app/Http/Controllers/ReporteDocumentoController.php` |
| Validación y cálculo de fechas | `app/Http/Controllers/ReporteDocumentoController.php:24` |
| Consulta con límite exclusivo | `app/Http/Controllers/ReporteDocumentoController.php:72` |
| Generación del PDF | `app/Http/Controllers/ReporteDocumentoController.php:132` |
| Vista del PDF | `resources/views/reportes/documentos-pdf.blade.php` |

### Con qué herramientas

- **PHPUnit 11.5**.
- **Laravel HTTP Testing** para `actingAs` y `get`.
- **Carbon / CarbonImmutable** y `travelTo` para congelar fechas.
- **Validator de Laravel** con reglas `date_format`, `before_or_equal`, `after_or_equal` y `required_with`.
- **DomPDF 3.1** (`Barryvdh\DomPDF\Facade\Pdf`) para generar el archivo.
- **RefreshDatabase + SQLite `:memory:`**.
- **Asserts**: `assertOk`, `assertSee`, `assertRedirect`, `assertSessionHasErrors`, `assertStringContainsString`, `assertStringStartsWith`.

### Explicación oral sugerida

> “Esta prueba cubre los bordes del informe de documentos. Primero verifica que el rango incluya el último instante del día final: el controller usa una fecha límite exclusiva que suma un día. Después comprueba que los rangos inválidos vuelvan con errores de validación. Por último, descarga el PDF y confirma que el contenido sea realmente un PDF. Usa Carbon para congelar la fecha, el validador de Laravel, DomPDF para generar el archivo y las aserciones HTTP para verificar la respuesta.”

### Preguntas posibles

**¿Por qué usa una fecha límite exclusiva?**
Porque comparar con `<= 2026-09-30` dejaría afuera los documentos creados después de la medianoche de ese día. Al sumar un día y usar `< 2026-10-01`, se incluye todo el 30 de septiembre.

**¿El PDF se genera de verdad?**
Sí. DomPDF renderiza la vista y devuelve un archivo cuyo contenido empieza con `%PDF-`. La prueba lo verifica sin abrir un navegador.

---

## 6. Cómo demostrar el alcance de la ejecución

| Nivel | Comando | Qué significa |
| --- | --- | --- |
| Un caso | `--filter='test_x'` | Solo se ejecuta la regla que querés mostrar |
| Un archivo | `php vendor/bin/phpunit tests/Feature/ClienteTest.php --testdox` | Se ejecuta toda la suite del módulo |
| Proyecto | `php artisan test --compact` | Se ejecutan todos los tests del proyecto |

Frase sugerida:

> “El alcance de esta prueba es el CRUD de cliente; por eso uso `--filter` y no ejecuto las otras 244 pruebas del proyecto.”

---

## 7. Comandos auxiliares

```bash
# Ver la versión de PHPUnit
php vendor/bin/phpunit --version

# Ver la versión de Laravel
php artisan --version

# Limpiar configuración antes de ejecutar
php artisan config:clear

# Ejecutar todo el archivo de una prueba
php vendor/bin/phpunit tests/Feature/ClienteTest.php --testdox

# Ejecutar toda la suite del proyecto
php artisan test --compact
```

---

## 8. Problemas frecuentes

| Problema | Causa o solución |
| --- | --- |
| El `--filter` no encuentra casos | El nombre debe coincidir exactamente con el método del test. |
| Error de Vite | `tests/TestCase.php` usa `withoutVite()`; si aparece igual, ejecutá `php artisan config:clear`. |
| La primera ejecución tarda | Es normal: se migra la base y se cargan los seeders. Las siguientes son más rápidas. |
| El pipe del filtro falla en PowerShell | Usá comillas simples y escapá el pipe si es necesario, o ejecutá cada método por separado. |
| El PDF no se genera | Verificá que `barryvdh/laravel-dompdf` esté instalado. |
| Se intenta usar MySQL | No hace falta: `phpunit.xml` fuerza SQLite en memoria para los tests. |

---

## 9. Alternativas detalladas

Si querés una demostración más extensa del bloque de permisos, estas pruebas ya existen y se pueden usar en lugar de la Prueba 2 o como complemento.

### 9.1 Servicios por profesional (recomendada)

```bash
php vendor/bin/phpunit tests/Feature/ServiciosProfesionalesTest.php --testdox --filter='test_pantalla_y_navegacion_cu34_respetan_los_cinco_roles'
```

Resultado real:

```text
Servicios Profesionales (Tests\Feature\ServiciosProfesionales)
 ✔ Pantalla y navegacion cu34 respetan los cinco roles

OK (1 test, 28 assertions)
```

**Qué hace:** recorre los cinco roles y comprueba:

- Coordinador, Directivo y Administrador ven la pantalla de servicios por profesional.
- Secretario y Profesional reciben `403` en esa pantalla.
- Solo Directivo y Administrador ven el enlace “Servicios por profesional” y pueden entrar a `/usuarios`.
- El Coordinador no puede listar usuarios.

**Cómo lo hace:**

1. `setUp()` ejecuta `RoleSeeder`.
2. Crea un usuario por cada rol con `User::factory()->create()` y `assignRole`.
3. Entra con `actingAs($actor)`.
4. Consulta `profile.edit` para revisar la navegación.
5. Consulta `profesionales.servicios.index` para revisar la pantalla.
6. Consulta `users.index` para comprobar el acceso a usuarios.
7. Combina `assertOk`, `assertForbidden`, `assertSee` y `assertDontSee`.

**Herramientas:** PHPUnit, HTTP Testing de Laravel, Spatie Permission, `RoleSeeder`, factories, `RefreshDatabase` y SQLite en memoria.

**Por qué usarla:** es la alternativa más parecida a la Prueba 2, pero mucho más detallada: cubre los cinco roles en un solo test y verifica tanto la navegación como la respuesta del servidor.

**Archivos que intervienen:**

| Responsabilidad | Archivo |
| --- | --- |
| Definición de la prueba | `tests/Feature/ServiciosProfesionalesTest.php:31` |
| Matriz de roles y permisos | `database/seeders/RoleSeeder.php:11` |
| Ruta de la pantalla | `routes/web.php:76` |
| Ruta de usuarios | `routes/web.php:48` |

### 9.2 Políticas de procesos por rol

```bash
php vendor/bin/phpunit tests/Feature/AuthorizationPolicyTest.php --testdox --filter='test_process_actions_follow_the_role_matrix'
```

Resultado real: `OK (1 test, 13 assertions)`.

**Qué hace:** verifica las acciones permitidas sobre un proceso según el rol:

- El Coordinador puede admitir, rechazar, asignar, reasignar y cambiar estado.
- El Secretario puede asignar y reasignar, pero no admitir.
- El Profesional puede cambiar estado.
- El Directivo puede ver, pero no cambiar estado.
- El Administrador puede admitir y eliminar.

**Cómo lo hace:** crea un usuario por rol y un proceso asignado al Profesional; después consulta directamente las habilidades de la policy con `can('admit')`, `can('reject')`, `can('updateState')`, etc.

**Herramientas:** PHPUnit, Laravel Gate/Policy, Spatie Permission, factories, `RefreshDatabase` y SQLite en memoria.

### 9.3 Políticas de documentos y reportes

```bash
php vendor/bin/phpunit tests/Feature/AuthorizationPolicyTest.php --testdox --filter='test_document_and_report_policies_enforce_role_and_ownership_scope'
```

Resultado real: `OK (1 test, 19 assertions)`.

**Qué hace:** comprueba el alcance de documentos y reportes:

- El Profesional solo ve y descarga documentos de procesos asignados.
- El Secretario ve documentos, pero no los descarga.
- El Coordinador y el Administrador pueden descargar.
- El Directivo no ve documentos.
- El Profesional solo puede ver, editar y eliminar sus propios reportes.
- Coordinador, Directivo y Administrador conservan acceso según su alcance.

**Cómo lo hace:** crea dos profesionales, dos procesos, documentos y reportes; después verifica `view`, `download`, `update`, `delete` y los scopes `visibleTo()`.

**Herramientas:** PHPUnit, Laravel Gate/Policy, Spatie Permission, factories, `RefreshDatabase` y SQLite en memoria.

### 9.4 Cómo cambia la trilogía

| Opción | Prueba de permisos | Assertions | Nivel de detalle |
| --- | --- | --- | --- |
| Actual | `RolePermissionTest::test_permissions_control_the_existing_user_and_service_routes` | 4 | Básico |
| Alternativa 1 | `ServiciosProfesionalesTest::test_pantalla_y_navegacion_cu34_respetan_los_cinco_roles` | 28 | Alto |
| Alternativa 2 | `AuthorizationPolicyTest::test_process_actions_follow_the_role_matrix` | 13 | Medio |
| Alternativa 3 | `AuthorizationPolicyTest::test_document_and_report_policies_enforce_role_and_ownership_scope` | 19 | Alto |

Recomendación: si el tiempo alcanza, usar la Alternativa 1 en lugar de la Prueba 2 y conservar las pruebas de CRUD y de reportes.

---

## 10. Evidencia de la ejecución

| Prueba | Resultado |
| --- | --- |
| CRUD de cliente | OK (1 test, 15 assertions) |
| Permisos por rol | OK (1 test, 4 assertions) |
| Reporte de documentos | OK (3 tests, 29 assertions) |
| Alternativa: servicios por profesional | OK (1 test, 28 assertions) |
| Alternativa: políticas de procesos | OK (1 test, 13 assertions) |
| Alternativa: políticas de documentos y reportes | OK (1 test, 19 assertions) |

Las pruebas se ejecutaron sobre este repositorio y finalizaron correctamente. No se modificó ningún test ni código de la aplicación para preparar esta guía.
