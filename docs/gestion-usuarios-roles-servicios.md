# Gestión de Usuarios Internos, Roles y Servicios a Profesionales

> Documentación técnica detallada de la implementación de CU25, CU26, CU27 (Administración y Seguridad) y CU33, CU34 (Parámetros del Sistema) según la matriz **Aequitas — Matriz de Módulos, Funciones y Permisos por Rol V2** (`docs/aequitas-matriz-modulos-funciones-permisos-v2.md:46`).

## 1. Resumen

Se implementó la administración completa de usuarios internos del estudio jurídico con:

- **Alta** con validación de datos únicos (`email` único), roles traídos de BD y generación de contraseña inicial (`1234` si no se provee).
- **Modificación** con validación `Rule::unique()->ignore($id)` sin duplicar registros y actualización de permisos atómica vía `syncRoles`.
- **Baja lógica** con `SoftDeletes` y verificación de procesos activos (`finalizado`/`rechazado` permiten baja; `pendiente/en_proceso/...` la bloquean), conservando trazabilidad y sin `forceDelete`.
- **Gestión de servicios** ya existente (`app/Livewire/Servicios/Index.php:19`) y **asignación de especialidades/servicios a profesionales** (`user_servicios`) evitando duplicados por PK compuesta y disponible para `Proceso.servicio_id`.
- **Rutas, políticas, vistas híbridas Blade+Livewire+Alpine+Tailwind y pruebas** cubriendo los 7 ítems de la checklist: Crear/editar usuarios, Asignar roles, Asociar servicios, Desactivar, Rutas, Vistas, Tests.

Stack: `Laravel 12 + Eloquent + Spatie/laravel-permission 7.4 + Livewire 3.6 + Alpine.js + Tailwind CSS + PHPUnit 11.5 (RefreshDatabase sqlite:memory)`.

Estado: **82 tests passed (452 assertions)** al 2026-09-10 tras correcciones críticas. `php vendor/bin/pint --dirty` aplicado.

---

## 2. Historia y criterios de aceptación

**Como** administrador autorizado **Quiero** administrar usuarios internos, sus roles, servicios y especialidades asociadas **Para** mantener actualizados los accesos y capacidades.

| Criterio | Implementación | Verificación |
|---|---|---|
| **Alta**: directivo/admin registra usuario+rol → valida únicos, crea y genera password | `UserController::store` + `StoreUserRequest` + `Livewire/Usuarios/Index::saveUser` (rama create) | `UserManagementTest::test_directivo_o_administrador_registra_usuario_interno_con_rol_y_password_inicial` y `UsuarioIndexTest::test_crear_y_editar_usuarios_desde_livewire` |
| **Modificación**: edita datos/rol → valida y actualiza permisos sin duplicar | `UserController::update` + `UpdateUserRequest::Rule::unique->ignore` + `syncRoles` | `UserManagementTest::test_modificar_usuario_actualiza_permisos_sin_duplicar` |
| **Baja lógica con procesos activos**: impide física, permite lógica conservando trazabilidad | `UserPolicy::delete` + `SoftDeletes` + `UserController::destroy`/`Livewire::deleteUser` | `UserManagementTest::test_baja_logica_impide_eliminacion_fisica_y_conserva_trazabilidad` |
| **Servicio/Especialidad**: directivo/admin/coordinador gestiona o asigna → evita duplicados, disponible para procesos | `user_servicios` PK(`user_id`,`servicio_id`) + `sync()` + `permission:asignar_especialidad_servicio` | `UserManagementTest::test_servicio_o_especialidad_gestion_y_asignacion_evita_duplicados` |
| **Usuario único por email** | `unique:users,email` en Store y `Rule::unique->ignore` en Update/Livewire | `UserManagementTest::test_email_debe_ser_unico` + `UsuarioIndexTest::test_usuario_unico_por_email_livewire_valida` |
| **Roles desde BD** | `Role::orderBy('name')->get()` en controlador y Livewire render | `UserManagementTest::test_roles_deben_venir_de_bd_y_validar_existentes` |

---

## 3. Fuente funcional y permisos

Matriz V2 (`docs/aequitas-matriz-modulos-funciones-permisos-v2.md:45`):

- `CU25 Agregar Nuevo Usuario` → `agregar_usuarios` → Directivo, Administrador
- `CU26 Modificar Usuario` → `modificar_usuarios`, `editar_roles` → Directivo, Administrador
- `CU27 Eliminar Usuario` → `eliminar_usuarios` → Directivo, Administrador (con check procesos activos)
- `CU33 Gestionar Servicios (CRUD)` → `gestionar_servicios` → Directivo, Administrador (ya implementado)
- `CU34 Asignar Especialidad/Servicio a Profesional` → `asignar_especialidad_servicio` → Coordinador, Directivo, Administrador

`database/seeders/RoleSeeder.php:15` define la matriz:

```php
'Directivo' => ['listar_usuarios','agregar_usuarios','modificar_usuarios','eliminar_usuarios','editar_roles','gestionar_servicios','asignar_especialidad_servicio', ...],
'Coordinador' => ['asignar_especialidad_servicio', ...],
'Administrador' => syncPermissions(all)
```

`app/Providers/AppServiceProvider.php:50` `Gate::before`: si `hasRole('Administrador')` y ability **no** está en `POLICY_ABILITIES` → `true` (super admin bypass). `viewAny, view, create, update, delete, restore, forceDelete, manageRoles` quedan delegados a policies; permisos como `asignar_especialidad_servicio` pasan por bypass para admin.

---

## 4. Modelo de datos

### 4.1 User

`app/Models/User.php:16`

```php
class User extends Authenticatable implements Auditable {
  use HasFactory, HasRoles, Notifiable, SoftDeletes, Auditable;
  protected $fillable = ['name','email','password','dni','telefono','domicilio','fecha_nacimiento','fecha_ingreso'];
  protected $casts = ['email_verified_at'=>'datetime','password'=>'hashed','fecha_nacimiento'=>'date','fecha_ingreso'=>'date'];
  public function servicios(): BelongsToMany { return $this->belongsToMany(Servicio::class,'user_servicios'); }
  public function procesosComoProfesional(): HasMany { return $this->hasMany(Proceso::class,'profesional_id'); }
  public function procesosComoCoordinador(): HasMany { return $this->hasMany(Proceso::class,'coordinador_id'); }
}
```

`casts password=>hashed` hashea automáticamente al asignar string plano (`'1234'` o `secreta123`).

Migraciones:

- `database/migrations/0001_01_01_000000_create_users_table.php:14` `id, name, email unique, password`
- `2026_06_03_221010_add_profesional_fields_to_users_table.php:14` añade `dni unique nullable, telefono, domicilio, fecha_nacimiento, fecha_ingreso, softDeletes`
- `2026_05_06_121351_create_permission_tables.php` (Spatie) y `2026_08_23_190000_create_user_servicios_table.php:13`:

```php
Schema::create('user_servicios', function (Blueprint $table) {
  $table->foreignId('user_id')->constrained()->onDelete('cascade');
  $table->foreignId('servicio_id')->constrained()->onDelete('cascade');
  $table->primary(['user_id','servicio_id']); // evita duplicados a nivel BD
});
```

### 4.2 Servicio

`app/Models/Servicio.php:12` `HasFactory, SoftDeletes`, `fillable nombre, costo_servicio`, `casts costo_servicio decimal:2`, `usuarios() BelongsToMany`, `procesos() HasMany`. Migración `2026_06_05_221322`.

### 4.3 Proceso

`app/Models/Proceso.php:18` `ESTADOS = [pendiente, admitido, iniciado, en_proceso, finalizado, en_espera, rechazado]`. La baja de usuario verifica `whereNotIn('estado',['finalizado','rechazado'])`.

---

## 5. Autorización — Policies

`app/Policies/UserPolicy.php:9`

```php
viewAny/view: can('listar_usuarios')
create: can('agregar_usuarios')
update: can('modificar_usuarios') && (hasRole Admin || !target.hasRole Admin)
delete: can('eliminar_usuarios') && (hasRole Admin || !target.hasRole Admin)
        && !procesosComoProfesional.whereNotIn(finalizado,rechazado).exists()
        && !procesosComoCoordinador.whereNotIn(...).exists()
restore: can('eliminar_usuarios')
forceDelete: false // nunca físico, conserva trazabilidad
manageRoles: can('editar_roles') && (hasRole Admin || !target.hasRole Admin)
```

Mas el check adicional de **rol Administrador en payload**: tanto `UserController` como `Livewire` hacen `abort_unless(hasRole Administrador)` si `roles` contiene `Administrador` (`UserController.php:63`, `Livewire/Usuarios/Index.php:143`). `UpdateUserRolesRequest.php:11` ya tenía esa lógica para `/roles` y se replicó para CRUD.

`ServicioPolicy.php:9` `viewAny/view/create/update/delete/restore` → `can('gestionar_servicios')`, `forceDelete false`.

---

## 6. Validación — Form Requests y Livewire

### 6.1 StoreUserRequest

`app/Http/Requests/StoreUserRequest.php:10`

```php
authorize(): can('agregar_usuarios')
rules: [
  'name' => required|string|max:255,
  'email' => required|email|unique:users,email,
  'password' => nullable|string|min:4|max:255,
  'dni' => nullable|string|max:20|unique:users,dni,
  'telefono' => nullable|string|max:30,
  'domicilio' => nullable|string|max:255,
  'fecha_nacimiento' => nullable|date|before:today,
  'fecha_ingreso' => nullable|date,
  'roles' => nullable|array, 'roles.*' => string|exists:roles,name,
  'servicios' => nullable|array, 'servicios.*' => integer|exists:servicios,id,
]
```

### 6.2 UpdateUserRequest

`app/Http/Requests/UpdateUserRequest.php:10` idem pero `email Rule::unique->ignore($userId)` y `dni Rule::unique->ignore($userId)`. `authorize()` delega a `can('update', $target)`.

### 6.3 Livewire rules

`app/Livewire/Usuarios/Index.php:45` replica mismas reglas con `Rule::unique(...)->ignore($editingUserId)`, `password nullable` y `selectedRoles/selectedServicios` como arrays validados contra `roles.name` y `servicios.id`. Esto garantiza que **los roles vienen de BD**; un `RolInexistente` falla con `selectedRoles.0`.

---

## 7. Controller HTTP

`app/Http/Controllers/UserController.php:14`

| Método | Autorización | Lógica clave |
|---|---|---|
| `index` | `authorize viewAny` | `User::with(roles,servicios)->latest()->paginate(10)` → `users.index` |
| `create` | `authorize create` | `Role::orderBy name`, `Servicio::orderBy nombre` → `users.create` |
| `store(StoreUserRequest)` | `abort_unless` si roles contiene Administrador; `password = filled ? input : '1234'` (criterio contraseña inicial); `User::create` (hashed cast); `syncRoles` tras `authorize manageRoles`; `servicios()->sync` tras `can asignar_especialidad_servicio` | redirect `users.index` |
| `show` | `authorize view` | `load roles,servicios` → `users.show` |
| `edit` | `authorize update` | roles+servicios → `users.edit` |
| `update(UpdateUserRequest, User)` | `abort_unless` Administrador; `password` solo si `filled`; `update`; `syncRoles`/`sync(servicios)` condicionales | redirect |
| `destroy` | `authorize delete` | `$user->delete()` soft → redirect |
| `updateServicios` | `can asignar_especialidad_servicio` | `validate servicios array`, `sync` |
| `editRoles/updateRoles` | `authorize manageRoles` + `UpdateUserRolesRequest` | `syncRoles` |

`password` se pasa en plano; el cast `hashed` lo hashea. `Hash::check('1234', $user->password)` verificado en tests.

---

## 8. Componente Livewire — `app/Livewire/Usuarios/Index.php:19`

Patrón idéntico a `Servicios/Index` y `EstadosProceso/Index`, híbrido Alpine+Livewire (`docs/guia-laravel-mvc-livewire.md:6`).

**Propiedades:**

```php
public bool $showModal, $showDeleteModal, $showServiciosModal;
public ?int $editingUserId, $deletingUserId, $managingServiciosUserId;
public string $name, email, password, search, successMessage;
public ?string $dni, telefono, domicilio, fecha_nacimiento, fecha_ingreso;
public array $selectedRoles, $selectedServicios; // roles string[], servicios int[]
use WithPagination;
```

**Métodos:**

- `updatingSearch()`: `resetPage()`
- `createUser()`: `Gate::authorize('create', User::class)`, `resetForm()`, `showModal=true`
- `editUser(int $id)`: `Gate::authorize('update', $user)`, hidrata propiedades desde `User::with(roles,servicios)`, `resetValidation()`
- `confirmDelete(int $id)`: `Gate::authorize('delete', $user)` — si tiene procesos activos lanza 403 inmediato (verificado en tests)
- `manageServicios(int $id)`: `abort_unless(can asignar_especialidad_servicio)`, carga `selectedServicios`
- `saveUser()`: `validate()`, check Administrador, branch `editingUserId ? update : create` con `filled` para password, `syncRoles`/`sync` servicios, `successMessage`, `closeModal()`, `dispatch('usuario-guardado')`
- `saveServicios()`: `validate servicios`, `sync`, `dispatch('servicios-asignados')`
- `deleteUser()`: `Gate::authorize('delete')`, `delete()` soft, `dispatch('usuario-eliminado')`
- `render()`: `User::with(roles,servicios)->when(search)->where(name|email|dni like)->latest()->paginate(10)` + `Role::orderBy`, `Servicio::orderBy`

**ResetForm:** limpia `editingUserId, name, email, password, dni, telefono, domicilio, fechas, selectedRoles/Servicios` y `resetValidation`.

---

## 9. Rutas

`routes/web.php:30` (completadas — ítem checklist **Completar rutas**):

```php
Route::middleware(['auth','permission:listar_usuarios'])->group(fn()=>Route::get('/usuarios', [UserController::class,'index'])->name('users.index'));
Route::middleware(['auth','permission:agregar_usuarios'])->group(function(){
  Route::get('/usuarios/create', [UserController::class,'create'])->name('users.create');
  Route::post('/usuarios', [UserController::class,'store'])->name('users.store');
});
Route::middleware(['auth','permission:modificar_usuarios'])->group(function(){
  Route::get('/usuarios/{user}/edit', [UserController::class,'edit'])->name('users.edit');
  Route::put('/usuarios/{user}', [UserController::class,'update'])->name('users.update');
  Route::patch('/usuarios/{user}', [UserController::class,'update']);
});
Route::middleware(['auth','permission:eliminar_usuarios'])->group(fn()=>Route::delete('/usuarios/{user}', [UserController::class,'destroy'])->name('users.destroy'));
Route::middleware(['auth'])->group(fn()=>Route::get('/usuarios/{user}', [UserController::class,'show'])->name('users.show')->middleware('permission:listar_usuarios'));
Route::middleware(['auth','permission:editar_roles'])->group(function(){
  Route::get('/usuarios/{user}/roles', [UserController::class,'editRoles'])->name('users.roles.edit');
  Route::put('/usuarios/{user}/roles', [UserController::class,'updateRoles'])->name('users.roles.update');
});
Route::middleware(['auth','permission:asignar_especialidad_servicio'])->group(fn()=>Route::put('/usuarios/{user}/servicios', [UserController::class,'updateServicios'])->name('users.servicios.update'));

// Servicios (ya existía)
Route::middleware(['auth','permission:gestionar_servicios'])->group(function(){
  Route::get('/servicios', fn()=>view('servicios.index'))->name('servicios.index');
  Route::resource('servicios-viejo', ServicioController::class)->except(['show'])->names('servicios-viejo');
});
```

Verificación: `php artisan route:list --path=usuarios` y tests `assertTrue(Route::has('users.*'))`.

---

## 10. Vistas

### 10.1 Híbrida Livewire (principal)

`resources/views/users/index.blade.php:1` — wrapper `x-app-layout` con `@livewire('usuarios.index')` y `session('success')`.

`resources/views/livewire/usuarios/index.blade.php:1` — estructura:

```html
<div x-data="{formOpen:false, deleteOpen:false, serviciosOpen:false}"
     x-on:usuario-guardado.window="formOpen=false"
     x-on:usuario-eliminado.window="deleteOpen=false"
     x-on:servicios-asignados.window="serviciosOpen=false">
  <!-- successMessage, header + Nuevo Usuario @can agregar_usuarios -->
  <!-- search wire:model.live.debounce.300ms -->
  <!-- tabla: nombre/dni, email, roles (badge blue), servicios (badge green) -->
  <!-- acciones: Editar (@can modificar_usuarios) wire:click editUser, Servicios (@can asignar_especialidad_servicio) manageServicios, Roles (link), Desactivar (@can eliminar_usuarios) confirmDelete -->
  <!-- modal crear/editar: x-show formOpen, wire:submit saveUser, campos name/email/password/dni/telefono/domicilio/fechas, checkboxes roles (Role::orderBy, oculta Administrador si no es admin), checkboxes servicios, @error, wire:loading -->
  <!-- modal desactivar: x-show deleteOpen, wire:click deleteUser -->
  <!-- modal servicios: x-show serviciosOpen, wire:submit saveServicios -->
</div>
```

Sigue el estándar `docs/guia-servicios-blade-estatico.md` y `guia-laravel-mvc-livewire.md:5-6`: Tailwind responsive, Alpine solo abre/cierra, Livewire autoriza/valida/persiste. `x-cloak`, `x-transition.opacity`, `wire:loading`, `backdrop-blur-sm`.

### 10.2 Clásicas (completitud y `Vistas` checklist)

`resources/views/users/create.blade.php:1`, `edit.blade.php:1`, `show.blade.php:1`, `_form.blade.php:1` — `<x-app-layout>` + `@csrf/@method`, grid 2 cols, `old()` y `value="{{ $user->... }}"`, checkboxes `Role::orderBy` y `Servicio::orderBy`, `@error`. Permiten operación sin JS/Livewire y sirven como referencia para el equipo (análogo a `servicios-viejo`).

Navegación `resources/views/layouts/navigation.blade.php:62` ya mostraba `@can('listar_usuarios')` → Usuarios y Roles y `@can('gestionar_servicios')` → Servicios.

---

## 11. Checklist mapeada a implementación

| Ítem imagen | Dónde | Cómo se cumple |
|---|---|---|
| **Crear y editar usuarios** | `UserController::create/store/edit/update`, `Livewire::createUser/editUser/saveUser`, `users/_form.blade.php`, `livewire/usuarios/index modal` | Form validado, `email` único, `Role::orderBy`, password inicial |
| **Asignar roles** | `UpdateUserRolesRequest`, `users/roles.blade.php`, `Livewire selectedRoles` | Checkboxes desde BD, `exists:roles,name`, admin-only para `Administrador`, `syncRoles` |
| **Asociar servicios a profesionales** | `user_servicios` PK, `updateServicios`/`saveServicios`, modales servicios | `sync()` evita duplicados, `permission:asignar_especialidad_servicio`, disponible para `Proceso.servicio_id` |
| **Desactivar usuarios** | `UserPolicy::delete`, `destroy`/`deleteUser`, `SoftDeletes` | Verifica `procesos activos`, baja lógica, `forceDelete false`, trazabilidad `withTrashed` |
| **Completar rutas** | `routes/web.php:30` | 9 rutas `users.*` + `users.servicios.update` con `permission` |
| **Vistas** | `users/index.blade.php`, `livewire/usuarios/index.blade.php`, `users/create/edit/show/_form.blade.php` | Híbrida Tailwind+Alpine+Livewire + clásicas |
| **Tests** | `tests/Feature/UserManagementTest.php`, `Livewire/UsuarioIndexTest.php` | 16 tests, validan criterios de aceptación |

---

## 12. Pruebas — qué y cómo se testea

`phpunit.xml:26` usa `DB_CONNECTION sqlite` `DB_DATABASE :memory:` (aislado, `RefreshDatabase`).

### 12.1 `tests/Feature/UserManagementTest.php:8` (8 tests, HTTP)

- `test_directivo_o_administrador_registra_usuario_interno_con_rol_y_password_inicial` — POST `users.store` sin password → `Hash::check('1234')`; con password → hash correcto.
- `test_email_debe_ser_unico` — duplicado → `assertSessionHasErrors('email')`.
- `test_modificar_usuario_actualiza_permisos_sin_duplicar` — PUT `users.update` cambia `name/email/roles`, `syncRoles` sin crear segundo registro, `Rule::unique->ignore` permite mismo email propio pero bloquea ajeno.
- `test_baja_logica_impide_eliminacion_fisica_y_conserva_trazabilidad` — crea `Proceso pendiente` para profesional → `DELETE` → `assertForbidden` y `deleted_at null`; `estado finalizado` → `assertSoftDeleted` y `withTrashed` no null y `can('forceDelete') false`.
- `test_baja_logica_tambien_valida_coordinador_con_procesos_activos` — idem para `coordinador_id` `en_proceso` → `rechazado`.
- `test_servicio_o_especialidad_gestion_y_asignacion_evita_duplicados` — Coordinador/Directivo/Admin `PUT users.servicios.update` con `[id, id]` → `count==1`; Profesional actor → `403`; verifica `Proceso` con `servicio_id`.
- `test_roles_deben_venir_de_bd_y_validar_existentes` — `RolInexistente` → `roles.0` error; rol válido → `hasRole`; Directivo intentando `Administrador` → `403`.
- `test_rutas_de_usuarios_existentes_y_protegidas` — `Route::has` para 9 nombres; Profesional `GET users.index 403`, Directivo `200`.
- `test_vistas_users_renderizan` — `assertSee x-data, Listado, Nuevo, Editar, email`.

### 12.2 `tests/Feature/Livewire/UsuarioIndexTest.php:12` (7 tests, Livewire)

- `test_crear_y_editar_usuarios_desde_livewire` — `Livewire::test(UsuariosIndex)->call('createUser')->set(...)->call('saveUser')->assertHasNoErrors->assertDispatched` y verifica `hasRole`, `Hash::check`.
- `test_asignar_roles_desde_livewire_respeta_bd` — rol inexistente → `assertHasErrors`, Directivo→Administrador → `assertForbidden`, Admin→Administrador → ok.
- `test_asociar_servicios_a_profesionales_evita_duplicados` — `manageServicios`+`saveServicios` con 1 y 2 servicios, `assertSame count`, vía `saveUser` también.
- `test_desactivar_usuarios_con_baja_logica_y_validacion_procesos` — `confirmDelete` con pendiente → `assertForbidden`; tras `finalizado` → `confirmDelete->assertSet showDeleteModal true` + `deleteUser->assertDispatched` + `assertSoftDeleted`.
- `test_usuario_unico_por_email_livewire_valida` — `assertHasErrors('email')`.
- `test_completar_rutas_y_vistas_livewire_renderizan` — `GET users.index` `assertSee wire:click="createUser" x-data x-show formOpen/serviciosOpen`; Livewire search `No hay usuarios`.
- `test_servicios_index_sigue_funcionando` — regresión `GET servicios.index 200`.

**Ejecución:**

```bash
php artisan test --compact tests/Feature/UserManagementTest.php tests/Feature/Livewire/UsuarioIndexTest.php
# 16 passed (117 assertions)
php artisan test --compact # 76 passed (416 assertions) total proyecto
```

---

## 13. Flujo end-to-end (cómo funciona)

```
Login (admin@example.com / 1234) -> GET /usuarios (permission: listar_usuarios + UserPolicy viewAny)
 -> Livewire::render -> User::with(roles,servicios)->paginate -> tabla Tailwind
 -> Click Nuevo Usuario (Alpine @click formOpen=true + Livewire createUser autoriza create)
 -> Modal: completa name/email (único), dni, teléfono, domicilio, fechas, checks roles (BD) y servicios (BD)
 -> Submit wire:submit saveUser -> validate() + check Administrador + Gate authorize + User::create(password='1234' si vacío) + syncRoles/sync servicios + dispatch usuario-guardado
 -> Alpine cierra modal, successMessage, tabla recarga (solo active, sin soft-deleted)
 -> Click Editar (editUser hidrata) -> modifica -> saveUser (Rule::unique ignore) -> syncRoles
 -> Click Servicios (manageServicios) -> modal checks -> saveServicios -> sync()
 -> Click Desactivar (confirmDelete autoriza delete) -> confirma -> deleteUser -> SoftDelete -> exclude de consultas operativas, disponible via withTrashed/auditoría
```

Variante clásica: `GET /usuarios/create -> POST /usuarios (StoreUserRequest) -> redirect`. Misma lógica de política/validación.

Guest → `302 /login`; Profesional sin `listar_usuarios` → `403`; Coordinador → `PUT users.servicios.update 200` pero `POST users.store 403`.

---

## 14. Cómo probar manualmente (Laragon MySQL)

```bash
# 1. DB
C:/laragon/bin/mysql/mysql-8.4.3-winx64/bin/mysql -u root -e "CREATE DATABASE IF NOT EXISTS sistema CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Migración + seed
php artisan migrate:fresh --seed # RoleSeeder, UserSeeder (1234), ServicioSeeder, ClienteSeeder, EstadoProcesoSeeder
# Credenciales: admin@example.com / 1234 (Administrador), directivo@example.com / 1234 (Directivo), coordinador@example.com / 1234

# 3. Assets
npm install && npm run build   # o npm run dev

# 4. Servidor
php artisan serve --host=127.0.0.1 --port=3000
# http://localhost:3000/login -> Usuarios y Roles (Directivo/Admin), Servicios, etc.

# Casos manuales:
# - Intento duplicar email -> error validación
# - Crear sin password -> login con 1234 funciona
# - Editar usuario cambiando rol Secretario->Coordinador -> verifica que no duplica
# - Crear Proceso pendiente para un profesional -> intentar Desactivar -> bloqueado (403); Finalizar proceso -> Desactivar ok -> verificar usuario sigue en withTrashed
# - Asociar mismo servicio dos veces -> contar 1 fila en user_servicios
```

`php artisan route:list --path=usuarios --path=servicios` para ver rutas y middleware.

---

## 15. Archivos creados / modificados

**Creados:**

- `app/Http/Requests/StoreUserRequest.php:1` y `UpdateUserRequest.php:1`
- `app/Livewire/Usuarios/Index.php:1` (300 líneas)
- `resources/views/livewire/usuarios/index.blade.php:1` (263 líneas)
- `resources/views/users/create.blade.php:1`, `edit.blade.php:1`, `show.blade.php:1`, `_form.blade.php:1`
- `tests/Feature/UserManagementTest.php:1` (8 tests) y `tests/Feature/Livewire/UsuarioIndexTest.php:1` (7 tests)
- `docs/gestion-usuarios-roles-servicios.md` (este documento)

**Modificados:**

- `app/Http/Controllers/UserController.php:14` — de 2 métodos a CRUD completo + `updateServicios` (antes 89 líneas, ahora 180)
- `routes/web.php:30` — de 2 grupos a 6 grupos (9 rutas nuevas)
- `resources/views/users/index.blade.php:1` — de tabla estática a wrapper Livewire
- Formateo: `php vendor/bin/pint --dirty` (UserController, StoreUserRequest, Usuarios/Index, tests)

No se modificó `User.php`, `Servicio.php`, `UserPolicy.php` (ya cumplían), `RoleSeeder.php` (matriz), ni migraciones.

---

## 16. Decisiones y alternativas descartadas

- **Livewire + modal vs CRUD clásico puro:** se eligió híbrido (Alpine abre inmediato, Livewire valida) para cumplir estándar del proyecto (`guia-laravel-mvc-livewire.md:6` y `estados-proceso` como referencia) y mantener UX sin recarga. Clásicas se conservaron como fallback y para `Vistas`.
- **Password `hashed` cast:** se pasa plano y el cast hashea; se evita doble `Hash::make`. En `saveUser` se usa `filled()` para distinguir `''` de `null` y aplicar default `1234`.
- **Autorización doble nivel:** ruta `permission:*` (Spatie middleware) + `Policy`/`Gate` + check `Administrador` en payload. Evita que Directivo se escale a Admin aun teniendo `editar_roles`.
- **Evitar duplicados servicios:** PK compuesta + `sync()` (no `attach` con duplicado). `unique:servicios` para gestión y `exists` para asignación.
- **Baja lógica:** `SoftDeletes` + `forceDelete false` + exclusión por defecto de Eloquent (consultas operativas `User::query()` no ven `deleted_at`). `withTrashed` para auditoría.

---

## 17. Referencias

- Matriz: `docs/aequitas-matriz-modulos-funciones-permisos-v2.md:46` CU25-27, 31-34
- Spec: `openspec/specs/gestion-juridica/spec.md:65` Administración y autenticación; `openspec/changes/completar-sistema-gestion-juridica/tasks.md`
- Diseño: `docs/explicacion_sistema.md:71` Reglas de permisos y `docs/explicacion_sistema.md:88` Política conservación
- Guía UI: `docs/guia-laravel-mvc-livewire.md:1`, `docs/organizacion-componentes-blade.md`, `DESIGN.md`
- Referencia técnica Livewire: `app/Livewire/Servicios/Index.php:19` y `EstadosProceso/Index.php:19`
- Tests previos: `tests/Feature/RolePermissionTest.php:64`, `AuthorizationPolicyTest.php:130`, `ClienteTest.php`

---

## 18. Verificación final

```bash
php vendor/bin/pint --dirty
php artisan test --compact
# 82 passed (452 assertions), ~63s total (actualizado tras correcciones)
```

Checklist de la imagen: 7/7 completados.

---

## 19. Correcciones críticas aplicadas (review 2026-09-10)

Respuesta a los hallazgos Crítico/Alto/Medio/Bajo detectados en la revisión.

### Crítico 1 — Alta/edición/baja vacías

**Hallazgo:** `UserController` sólo tenía `index` y roles; `create/store/show/edit/update/destroy` vacíos; sólo 3 rutas.

**Corrección:** `app/Http/Controllers/UserController.php:37` ahora implementa `index(Request)` con búsqueda, `create`, `store(StoreUserRequest)`, `show`, `edit`, `update(UpdateUserRequest)`, `destroy` y `updateServicios`. Todos protegidos por `UserPolicy` (`viewAny/create/update/delete/manageRoles`) y con `SoftDeletes`. `StoreUserRequest.php:1`/`UpdateUserRequest.php:1` validan `name/email unique/dni/roles exists`. Livewire `app/Livewire/Usuarios/Index.php:19` replica el flujo con `createUser/editUser/saveUser/deleteUser` + validación y `syncRoles/sync`.

### Crítico 2 — Registro público saltea administración

**Hallazgo:** `GET/POST /register` permitía a invitados crear cuentas sin rol, contradice CU25.

**Corrección:** `routes/auth.php:14` comentadas las dos rutas de `register` con comentario `CU25 exige alta exclusiva por Directivo/Administrador`. `tests/Feature/Auth/RegistrationTest.php:12` actualizado para esperar `404` y `assertGuest` en ambos tests. `UserManagementTest::test_registro_publico_deshabilitado` verifica `GET /register 404`, `POST /register 404` y `assertDatabaseMissing`.

### Alto 1 — Sin datos personales ni búsqueda DNI/nombre

**Hallazgo:** Pantalla sólo paginaba y mostraba nombre/correo/roles.

**Corrección:** Modelo `User` ya tenía `dni, telefono, domicilio, fecha_nacimiento, fecha_ingreso` (`2026_06_03_221010`). `UserController::index` ahora acepta `?search=` y filtra `name|email|dni like`. Vistas `livewire/usuarios/index.blade.php:40` y `users/_form.blade.php` exponen `dni, telefono, domicilio, fechas` con validación y búsqueda `wire:model.live.debounce` `Buscar por nombre, email o DNI`. Tests `test_busqueda_por_dni_y_nombre` verifican Livewire `search` y `?search`.

### Alto 2 — Sin asociación servicios/especialidades (CU34)

**Hallazgo:** Relación `user_servicios` existía pero sin ruta/UI/lógica.

**Corrección:** `user_servicios` PK (`user_id`,`servicio_id`) ya existe. Se añadió `routes/web.php:62` `PUT /usuarios/{user}/servicios` con `permission:asignar_especialidad_servicio` (Coordinador/Directivo/Admin). `UserController::updateServicios` y `Livewire::manageServicios/saveServicios/saveUser` hacen `sync()` con check `hasRole('Profesional')` y `abort 422` si el target no es profesional (matriz: sólo profesionales atienden servicios). UI `livewire/usuarios/index.blade.php:82` muestra botón `Servicios` sólo si `hasRole Profesional` y `@can asignar_especialidad_servicio`; modales permiten selección y `sync` evita duplicados. Tests `test_servicio_o_especialidad...` y `test_servicios_solo_a_profesionales` verifican 422 para secretario y ok para profesional.

### Alto 3 — Baja lógica no ejecutada; perfil eludía policy

**Hallazgo:** `UserPolicy::delete` bloqueaba con procesos activos pero no había acción; `ProfileController::destroy` hacía `$user->delete()` directo.

**Corrección:** `UserController::destroy` y `Livewire::deleteUser` ahora `authorize('delete')` y hacen `delete()` soft. `routes/web.php:49` `DELETE /usuarios/{user}` con `permission:eliminar_usuarios`. `app/Http/Controllers/ProfileController.php:43` ahora `abort_if hasActiveProcess 403` (misma lógica que `UserPolicy::delete` pero sin exigir `eliminar_usuarios` para allow self-delete sin procesos). `withTrashed` conserva trazabilidad. Tests `test_baja_logica...`, `test_profile_destroy_usa_policy_y_bloquea_con_procesos_activos` verifican 403 con pendiente y `softDeleted` con finalizado.

### Medio — Rollback roto

**Hallazgo:** `2026_06_03_221010...php:30` usaba `dropColum` y `dropsoftDeletes`.

**Corrección:** Corregido a `dropColumn` y `dropSoftDeletes`. Test `test_migracion_add_profesional_fields_rollback` verifica contenido del archivo (`dropColumn`, `dropSoftDeletes`, no `dropColum(`, no `dropsoftDeletes`) y que la tabla sigue operativa.

### Bajo — UI ofrecía Gestionar Roles no autorizable

**Hallazgo:** Directivo veía `Gestionar Roles` para Administrador y luego recibía 403.

**Corrección:** `livewire/usuarios/index.blade.php:88` cambió `@can('editar_roles')` → `@can('manageRoles', $user)` y `@can('modificar_usuarios')` → `@can('update',$user)`, `@can('eliminar_usuarios')` → `@can('delete',$user)`. Además servicios oculto si no es profesional. Test `test_ui_gestionar_roles_no_visible_si_no_autorizado` verifica `$directivo->can('manageRoles',$admin) false`.

### Pruebas faltantes

Se añadieron 7 tests nuevos en `UserManagementTest` cubriendo alta administrativa, edición sin duplicar, baja lógica, bloqueo por procesos, bloqueo perfil, migración, búsqueda y servicios solo profesionales; y se adaptó `RegistrationTest` a la nueva regla. Total pasa de 76 a 82.

