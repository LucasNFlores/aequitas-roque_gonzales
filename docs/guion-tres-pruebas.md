# Guía de las tres pruebas

Documento de apoyo para ejecutar y explicar tres pruebas del **mismo CRUD** (gestión de usuarios) usando **rutas y controladores HTTP**. No requiere crear pruebas nuevas ni modificar código.

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

Las tres pertenecen al CRUD de usuarios y recorren una operación distinta: **crear**, **editar** y **dar de baja**. Todas pasan por `routes/web.php`, `UserController`, un `FormRequest` y `UserPolicy`.

| # | Prueba | Operación | Ruta → método | Archivo | Resultado obtenido |
| --- | --- | --- | --- | --- | --- |
| 1 | `test_directivo_o_administrador_registra_usuario_interno_con_rol_y_password_inicial` | Create | `POST /usuarios` → `store` | `tests/Feature/UserManagementTest.php:45` | OK (1 test, 8 assertions) |
| 2 | `test_modificar_usuario_actualiza_permisos_sin_duplicar` | Update | `PUT /usuarios/{user}` → `update` | `tests/Feature/UserManagementTest.php:92` | OK (1 test, 9 assertions) |
| 3 | `test_baja_logica_impide_eliminacion_fisica_y_conserva_trazabilidad` | Delete | `DELETE /usuarios/{user}` → `destroy` | `tests/Feature/UserManagementTest.php:122` | OK (1 test, 7 assertions) |

Total de la demostración: **3 casos ejecutados y 24 aserciones aprobadas**.

---

## 3. Prueba 1 — Crear usuario (Create)

### Comando

```bash
php vendor/bin/phpunit tests/Feature/UserManagementTest.php --testdox --filter='test_directivo_o_administrador_registra_usuario_interno_con_rol_y_password_inicial'
```

### Resultado obtenido

```text
User Management (Tests\Feature\UserManagement)
 ✔ Directivo o administrador registra usuario interno con rol y password inicial

OK (1 test, 8 assertions)
```

### Qué hace

Comprueba que un usuario autorizado puede registrar un usuario interno, asignarle un rol y definir su contraseña inicial:

1. Un **Directivo** registra un usuario con rol `Profesional` y **sin** contraseña: el sistema debe generar `1234`.
2. Un **Administrador** registra otro usuario con contraseña explícita.
3. Verifica que ambos usuarios se guarden, que el rol se asigne y que la contraseña quede hasheada.

### Cómo lo hace

1. `setUp()` ejecuta `RoleSeeder`, que crea los roles y sincroniza la matriz de permisos (`database/seeders/RoleSeeder.php:15`).
2. `actingAsDirectivo()` crea un usuario con factory y le asigna el rol `Directivo`.
3. Autentica con `actingAs($directivo)` y hace `POST route('users.store')` con `name`, `email` y `roles`, sin `password`.
4. La ruta está protegida por el middleware `permission:agregar_usuarios` (`routes/web.php:51`).
5. `StoreUserRequest` valida los datos y autoriza con `can('agregar_usuarios')` (`app/Http/Requests/StoreUserRequest.php:9`).
6. `UserController@store` (`app/Http/Controllers/UserController.php:76`) usa `1234` cuando no se envía contraseña, crea el usuario y sincroniza el rol con `syncRoles`.
7. Repite el alta con un Administrador y contraseña explícita `secreta123`.

### Aserciones principales

- `assertRedirect(route('users.index'))`: el alta termina en el listado.
- `assertDatabaseHas('users', ['email' => ...])`: el registro se persistió.
- `hasRole('Profesional')`: el rol se asignó.
- `Hash::check('1234', ...)`: se aplicó la contraseña inicial por defecto.
- `Hash::check('secreta123', ...)`: se respetó la contraseña explícita.

### Archivos que intervienen

| Responsabilidad | Archivo |
| --- | --- |
| Definición de la prueba | `tests/Feature/UserManagementTest.php:45` |
| Ruta del alta | `routes/web.php:53` |
| Validación y autorización | `app/Http/Requests/StoreUserRequest.php:9` |
| Controller | `app/Http/Controllers/UserController.php:76` |
| Policy de creación | `app/Policies/UserPolicy.php:19` |
| Matriz de roles y permisos | `database/seeders/RoleSeeder.php:15` |

### Con qué herramientas

- **PHPUnit 11.5** para definir y ejecutar la prueba.
- **Laravel HTTP Testing** para `actingAs` y `post`.
- **RefreshDatabase** para aislar la base.
- **SQLite `:memory:`** como base de datos de test.
- **Faker + factories** para crear usuarios.
- **Seeders** para roles y permisos.
- **Spatie Laravel Permission** para `assignRole`, `hasRole` y middleware de permisos.
- **Hash** para verificar las contraseñas.
- **Asserts de Laravel**: `assertRedirect`, `assertDatabaseHas`.

### Explicación oral sugerida

> “Esta prueba cubre el alta de usuarios. Carga la matriz de roles, autentica a un Directivo y hace un POST a la ruta de usuarios con nombre, email y rol, sin contraseña. El FormRequest valida y autoriza, y el controlador crea el usuario con la contraseña inicial 1234 y le sincroniza el rol. Después repite el alta con un Administrador y una contraseña explícita. Verifica la redirección, la persistencia, el rol y que ambas contraseñas queden hasheadas.”

### Preguntas posibles

**¿Por qué el Directivo puede crear usuarios?**
Porque `RoleSeeder` le asigna `agregar_usuarios` y la ruta exige ese permiso (`routes/web.php:51`).

**¿Quién puede asignar el rol Administrador?**
Solo un Administrador: `UserController@store` aborta con 403 si otro rol intenta asignarlo (`app/Http/Controllers/UserController.php:80`).

---

## 4. Prueba 2 — Editar usuario (Update)

### Comando

```bash
php vendor/bin/phpunit tests/Feature/UserManagementTest.php --testdox --filter='test_modificar_usuario_actualiza_permisos_sin_duplicar'
```

### Resultado obtenido

```text
User Management (Tests\Feature\UserManagement)
 ✔ Modificar usuario actualiza permisos sin duplicar

OK (1 test, 9 assertions)
```

### Qué hace

Comprueba que un Directivo puede modificar los datos y el rol de un usuario existente, sin duplicarlo y sin permitir emails repetidos:

1. Crea un usuario con rol `Secretario`.
2. Envía un `PUT` con nuevo nombre, nuevo email y rol `Coordinador`.
3. Verifica que los datos y el rol se actualicen.
4. Intenta usar un email ya existente y confirma que la validación lo rechaza.

### Cómo lo hace

1. `actingAsDirectivo()` autentica a un Directivo.
2. Crea un usuario objetivo y le asigna `Secretario`.
3. Hace `PUT route('users.update', $target)` con `name`, `email` y `roles`.
4. La ruta está protegida por `permission:modificar_usuarios` (`routes/web.php:56`).
5. `UpdateUserRequest` autoriza con `can('update', $target)` y valida el email con `Rule::unique(...)->ignore($userId)` (`app/Http/Requests/UpdateUserRequest.php:21`).
6. `UserController@update` (`app/Http/Controllers/UserController.php:141`) actualiza los datos y reemplaza los roles con `syncRoles`.
7. Repite el `PUT` con un email ya usado y verifica el error de validación.

### Aserciones principales

- `assertSame('Nombre Actualizado', $target->name)`: se persistió el nombre.
- `assertSame('actualizado@example.com', $target->email)`: se persistió el email.
- `hasRole('Coordinador')` y `! hasRole('Secretario')`: el rol se reemplazó, no se acumuló.
- `assertSame(1, User::where('email', ...)->count())`: no se duplicó el usuario.
- `assertSessionHasErrors('email')`: la unicidad se respeta al editar.

### Archivos que intervienen

| Responsabilidad | Archivo |
| --- | --- |
| Definición de la prueba | `tests/Feature/UserManagementTest.php:92` |
| Ruta de edición | `routes/web.php:58` |
| Validación y autorización | `app/Http/Requests/UpdateUserRequest.php:21` |
| Controller | `app/Http/Controllers/UserController.php:141` |
| Policy de edición | `app/Policies/UserPolicy.php:24` |

### Con qué herramientas

- **PHPUnit 11.5**.
- **Laravel HTTP Testing** para `actingAs` y `put`.
- **FormRequest + Rule::unique(...)->ignore()** para la unicidad en edición.
- **Spatie Laravel Permission** para `syncRoles` y `hasRole`.
- **RefreshDatabase + SQLite `:memory:`**.
- **Asserts**: `assertRedirect`, `assertSame`, `assertSessionHasErrors`.

### Explicación oral sugerida

> “Esta prueba cubre la edición. Un Directivo modifica nombre, email y rol de un usuario existente mediante un PUT. El FormRequest valida la unicidad ignorando el propio usuario y el controlador actualiza los datos y sincroniza los roles, es decir, reemplaza el rol anterior en lugar de acumularlo. Después intenta reutilizar un email existente y confirma que la validación lo rechaza. También comprueba que no se creó un usuario duplicado.”

### Preguntas posibles

**¿Qué diferencia hay entre `syncRoles` y `assignRole`?**
`assignRole` agrega un rol; `syncRoles` deja exactamente los roles indicados. Por eso el usuario deja de ser `Secretario` al pasar a `Coordinador`.

**¿Por qué el email duplicado no falla en la primera edición?**
Porque `Rule::unique(...)->ignore($userId)` excluye al propio usuario de la comprobación de unicidad.

---

## 5. Prueba 3 — Dar de baja usuario (Delete)

### Comando

```bash
php vendor/bin/phpunit tests/Feature/UserManagementTest.php --testdox --filter='test_baja_logica_impide_eliminacion_fisica_y_conserva_trazabilidad'
```

### Resultado obtenido

```text
User Management (Tests\Feature\UserManagement)
 ✔ Baja logica impide eliminacion fisica y conserva trazabilidad

OK (1 test, 7 assertions)
```

### Qué hace

Comprueba que la baja de un usuario respeta las reglas de negocio:

1. Un Profesional con un proceso activo **no** puede darse de baja.
2. Al finalizar el proceso, la baja se permite y es **lógica**.
3. El registro sigue existiendo para conservar trazabilidad.
4. El borrado físico (`forceDelete`) nunca está permitido.

### Cómo lo hace

1. `actingAsDirectivo()` autentica a un Directivo.
2. Crea un Coordinador, un Profesional, un Cliente, un Servicio y un `Proceso` en estado `pendiente` asociado al Profesional.
3. Hace `DELETE route('users.destroy', $profesional)`.
4. La ruta está protegida por `permission:eliminar_usuarios` (`routes/web.php:62`).
5. `UserController@destroy` autoriza con `can('delete', $user)` y ejecuta `$user->delete()` (`app/Http/Controllers/UserController.php:185`).
6. `UserPolicy::delete` exige `eliminar_usuarios`, que el objetivo no sea Administrador y que no tenga procesos activos como Profesional ni como Coordinador (`app/Policies/UserPolicy.php:30`).
7. Finaliza el proceso y repite el `DELETE`: ahora la baja lógica se ejecuta.
8. Verifica que el registro siga existiendo con `withTrashed()` y que `forceDelete` sea falso.

### Aserciones principales

- `assertForbidden()` con proceso activo y `deleted_at => null`: la baja se bloquea y nada se borra.
- `assertSoftDeleted('users', ['id' => ...])`: la baja es lógica.
- `assertNotNull(User::withTrashed()->find(...))`: el registro sigue existiendo.
- `assertFalse($directivo->can('forceDelete', ...))`: el borrado físico está prohibido.

### Archivos que intervienen

| Responsabilidad | Archivo |
| --- | --- |
| Definición de la prueba | `tests/Feature/UserManagementTest.php:122` |
| Ruta de baja | `routes/web.php:63` |
| Controller | `app/Http/Controllers/UserController.php:185` |
| Policy de baja | `app/Policies/UserPolicy.php:30` |
| Regla de borrado físico | `app/Policies/UserPolicy.php:47` |

### Con qué herramientas

- **PHPUnit 11.5**.
- **Laravel HTTP Testing** para `actingAs` y `delete`.
- **SoftDeletes** en el modelo `User`.
- **Laravel Gate/Policy** para `delete` y `forceDelete`.
- **Factories** para crear usuarios, clientes, servicios y procesos.
- **RefreshDatabase + SQLite `:memory:`**.
- **Asserts**: `assertForbidden`, `assertDatabaseHas`, `assertSoftDeleted`, `assertNotNull`, `assertFalse`.

### Explicación oral sugerida

> “Esta prueba cubre la baja de usuarios. Primero crea un Profesional con un proceso activo e intenta eliminarlo: la policy lo bloquea con 403 porque no se puede dar de baja a alguien con procesos pendientes. Después finaliza el proceso y repite el DELETE: ahora la baja es lógica, el registro se conserva con deleted_at y sigue existiendo con withTrashed. Por último confirma que forceDelete siempre devuelve falso, así que nunca se borra físicamente.”

### Preguntas posibles

**¿Por qué la primera baja devuelve 403 y no un error de validación?**
Porque es una regla de autorización: `UserPolicy::delete` devuelve falso cuando hay procesos activos, y Laravel responde 403.

**¿Qué es una baja lógica?**
Es marcar el registro con `deleted_at` sin borrarlo de la tabla. Eloquent lo excluye por defecto, pero se puede recuperar con `withTrashed()` o `restore()`.

**¿Por qué se verifica también como Coordinador?**
Porque un usuario puede tener procesos activos en dos roles; la policy revisa tanto `procesosComoProfesional()` como `procesosComoCoordinador()` (`app/Policies/UserPolicy.php:34`).

---

## 6. Cómo demostrar el alcance de la ejecución

| Nivel | Comando | Qué significa |
| --- | --- | --- |
| Un caso | `--filter='test_x'` | Solo se ejecuta la regla que querés mostrar |
| Un archivo | `php vendor/bin/phpunit tests/Feature/UserManagementTest.php --testdox` | Se ejecuta toda la suite del módulo |
| Proyecto | `php artisan test --compact` | Se ejecutan todos los tests del proyecto |

Frase sugerida:

> “El alcance de esta demostración es el CRUD de usuarios; por eso uso `--filter` y ejecuto solo los tres casos que quiero explicar.”

---

## 7. Comandos auxiliares

```bash
# Ver la versión de PHPUnit
php vendor/bin/phpunit --version

# Ver la versión de Laravel
php artisan --version

# Limpiar configuración antes de ejecutar
php artisan config:clear

# Ejecutar todo el archivo de la prueba
php vendor/bin/phpunit tests/Feature/UserManagementTest.php --testdox

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
| Se intenta usar MySQL | No hace falta: `phpunit.xml` fuerza SQLite en memoria para los tests. |
| Falla `assignRole` | Verificá que `RoleSeeder` se haya ejecutado en `setUp()` y que el rol exista. |

---

## 9. Alternativas

Si necesitás otra trilogía dentro del mismo CRUD, estas pruebas ya existen.

### 9.1 CRUD de clientes (HTTP/controladores)

Archivo `tests/Feature/ClienteTest.php`:

```bash
php vendor/bin/phpunit tests/Feature/ClienteTest.php --testdox --filter='test_administrador_can_crud_cliente'
php vendor/bin/phpunit tests/Feature/ClienteTest.php --testdox --filter='test_validation_rejects_invalid_data'
php vendor/bin/phpunit tests/Feature/ClienteTest.php --testdox --filter='test_unauthorized_roles_cannot_access_clientes'
```

- El primer test recorre **todo** el CRUD de clientes en una sola prueba (index, create, store, show, edit, update y destroy).
- El segundo cubre validaciones del alta.
- El tercero cubre autorización por rol.

**Diferencia con la trilogía recomendada:** en usuarios cada test equivale a una operación (crear, editar, dar de baja); en clientes el primer test concentra todo el CRUD.

### 9.2 Lectura del CRUD de usuarios

Si querés sumar la **R** de lectura, existe:

```bash
php vendor/bin/phpunit tests/Feature/UserManagementTest.php --testdox --filter='test_vistas_users_renderizan'
```

Verifica que `index`, `create`, `edit`, `show` y `roles.edit` rendericen correctamente (`tests/Feature/UserManagementTest.php:295`).

---

## 10. Evidencia de la ejecución

| Prueba | Resultado |
| --- | --- |
| Crear usuario | OK (1 test, 8 assertions) |
| Editar usuario | OK (1 test, 9 assertions) |
| Dar de baja usuario | OK (1 test, 7 assertions) |

Las tres pruebas se ejecutaron sobre este repositorio y finalizaron correctamente. No se modificó ningún test ni código de la aplicación para preparar esta guía.
