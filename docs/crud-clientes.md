# CRUD de Clientes

## Objetivo

Permitir registrar, consultar, editar y dar de baja lógica a clientes. El acceso completo está habilitado para los roles **Administrador** (superadministrador vía `Gate::before`) y **Secretario**. Cumple el estándar mínimo del commit `crud` (modelo, migración, factory/seeder, requests, policy, controller resource, vistas Blade, rutas protegidas y pruebas).

## Componentes implementados

### 1. Modelo y migración

**Modelo** `app/Models/Cliente.php:13`
* `HasFactory, SoftDeletes`
* `fillable = ['nombre','apellido','dni','telefono','correo','domicilio','fecha_nacimiento']`
* `casts: fecha_nacimiento => date`
* Relaciones `hasMany: procesos, comprobantesPago, notificaciones, turnos`

**Migración** `database/migrations/2026_06_03_224907_create_clientes_table.php:14`
```php
Schema::create('clientes', function (Blueprint $table) {
    $table->id();
    $table->string('nombre');
    $table->string('apellido');
    $table->string('dni')->unique();
    $table->string('telefono');
    $table->string('correo');
    $table->string('domicilio');
    $table->date('fecha_nacimiento');
    $table->timestamps();
    $table->softDeletes();
});
down(): Schema::dropIfExists('clientes');
```
Reversible: `php artisan migrate` / `php artisan migrate:rollback --step=1 && php artisan migrate`.

### 2. Factory y Seeder

**Factory** `database/factories/ClienteFactory.php:18`
* `nombre => fake()->firstName()`, `apellido => fake()->lastName()`, `dni => fake()->unique()->numerify('########')`, `telefono => fake()->phoneNumber()`, `correo => fake()->unique()->safeEmail()`, `domicilio => fake()->address()`, `fecha_nacimiento => fake()->dateTimeBetween('-80 years','-18 years')`

**Seeder** `database/seeders/ClienteSeeder.php:12`
* 5 clientes de ejemplo con `firstOrCreate(['dni' => ...])` (Juan Pérez 30123456, María González 31234567, etc.)

**Registro** `database/seeders/DatabaseSeeder.php:20` incluye `ClienteSeeder::class`. Ejecutable con `php artisan migrate:fresh --seed` o `php artisan db:seed --class=ClienteSeeder`.

### 3. Validación y autorización

**Store** `app/Http/Requests/StoreClienteRequest.php:10`
* `authorize(): $this->user()?->can('registrar_clientes')`
* `rules: nombre required|string|max:255, apellido required, dni required|string|max:20|unique:clientes,dni, telefono required, correo required|email|unique:clientes,correo, domicilio required, fecha_nacimiento required|date|before:today|after:1900-01-01`

**Update** `app/Http/Requests/UpdateClienteRequest.php:10`
* `authorize(): can('modificar_clientes')`
* `rules` igual pero `unique:clientes,dni,$id` y `unique:clientes,correo,$id` para ignorar registro actual.

Datos inválidos no se persisten (probado en `ClienteTest::test_validation_rejects_invalid_data`).

**Policy** `app/Policies/ClientePolicy.php:10`
```
viewAny / view  -> can('listar_filtrar_clientes')
create          -> can('registrar_clientes')
update          -> can('modificar_clientes')
delete/restore  -> can('eliminar_clientes')
```
`RoleSeeder.php:16` otorga esos 4 permisos a `Secretario`; `Administrador` los tiene todos y además `AppServiceProvider.php:24` `Gate::before` retorna `true` si `hasRole('Administrador')`. Profesional/Coordinador/Directivo denegados según matriz (Coordinador/Directivo solo ven listado pero no pueden crear/editar/eliminar).

### 4. Controller

`app/Http/Controllers/ClienteController.php:13` (resource, usa `AuthorizesRequests` de `app/Http/Controllers/Controller.php:6`):

* `index(): authorize viewAny, Cliente::latest()->paginate(10) -> view('clientes.index')`
* `create(): authorize create -> view('clientes.create')`
* `store(StoreClienteRequest): authorize create, Cliente::create($validated) -> redirect clientes.index with('success')`
* `show(Cliente): authorize view -> view('clientes.show')`
* `edit(Cliente): authorize update -> view('clientes.edit')`
* `update(UpdateClienteRequest, Cliente): authorize update, $cliente->update($validated)`
* `destroy(Cliente): authorize delete, $cliente->delete()` (soft delete)

### 5. Rutas

`routes/web.php:5` y `routes/web.php:52`
```php
use App\Http\Controllers\ClienteController;
Route::middleware(['auth'])->group(fn () => Route::resource('clientes', ClienteController::class));
// clientes.index, create, store, show, edit, update, destroy
```
Protegidas por `auth` (guest → `302` a `/login`) + `ClientePolicy` en controller. Verificación `php artisan route:list --path=clientes`.

### 6. Vistas Blade

Carpeta `resources/views/clientes/`:

* `_form.blade.php` - parcial compartido `action, $cliente?, $submitLabel`. Campos `nombre, apellido, dni, telefono, correo (type email), domicilio, fecha_nacimiento (type date, format Y-m-d)`, `@csrf`, `@method('PUT')` si edición, `x-input-label/text-input/error` y `old()`.
* `create.blade.php` - `<x-app-layout>` + `@include('clientes._form', ['action' => route('clientes.store'), 'submitLabel' => 'Crear Cliente'])`
* `edit.blade.php` - similar con `route('clientes.update', $cliente)`
* `show.blade.php` - detalle en grid 2 cols, `format('d/m/Y')` para fecha, botones `Editar` y `Volver`
* `index.blade.php` - header, `session('success')`, botón `Nuevo Cliente` `@can('create')`, tabla `nombre, dni, correo, telefono`, acciones `Ver (@can view)`, `Editar (@can update)`, `Eliminar (@can delete)` con `form DELETE @csrf @method('DELETE') onsubmit confirm`, `{{ $clientes->links() }}` paginación 10. Estilo Tailwind responsive.

Navegación `resources/views/layouts/navigation.blade.php:28`:
```blade
@can('listar_filtrar_clientes')
  <x-nav-link :href="route('clientes.index')" :active="request()->routeIs('clientes.*')">Clientes</x-nav-link>
@endcan
```

### 7. Pruebas

`tests/Feature/ClienteTest.php` - 6 tests, 48 assertions, usa `RefreshDatabase` + `sqlite :memory:` (`phpunit.xml:26`):

* `test_administrador_can_crud_cliente` - index, create, store, show, edit, update, destroy + `assertSoftDeleted`
* `test_secretario_can_crud_cliente` - idem con rol Secretario
* `test_validation_rejects_invalid_data` - nombre vacío, correo inválido, fecha futura, dni duplicado -> `assertSessionHasErrors` y `assertDatabaseMissing`
* `test_unauthorized_roles_cannot_access_clientes` - guest `302`, Profesional `403` en index/store, Coordinador/Directivo `index 200` pero `store 403`
* `test_factory_and_seeder_create_data` - `Cliente::factory()->create()` + `ClienteSeeder`
* `test_migration_is_reversible` - `Schema::hasTable('clientes')` + rollback/re-migrate

Ejecución:
```bash
php artisan test --filter=ClienteTest
php artisan test # 40 passed (con tests/TestCase.php:11 withoutVite)
```

Correcciones auxiliares: `tests/TestCase.php:11` agrega `withoutVite()` para evitar `Vite manifest not found`, `tests/Feature/ProfileTest.php:79` usa `assertSoftDeleted` porque `User` ahora tiene `SoftDeletes`, `composer install livewire` y `php artisan view:clear` para `ServicioIndexTest`.

## Cómo funciona el flujo

```
Usuario (Administrador/Secretario) -> GET /clientes (auth + viewAny) -> ClienteController@index -> Cliente::latest()->paginate(10) -> clientes.index
  -> Click Nuevo Cliente -> GET /clientes/create (can create) -> _form
  -> POST /clientes (StoreClienteRequest authorize + validate) -> Cliente::create -> redirect index with success
  -> Click Ver -> GET /clientes/{id} (can view) -> show
  -> Click Editar -> GET /clientes/{id}/edit (can update) -> _form con old()
  -> PUT /clientes/{id} (UpdateClienteRequest) -> update -> redirect
  -> Click Eliminar -> DELETE /clientes/{id} (can delete) -> softDelete -> redirect
Guest -> 302 a /login
Profesional sin permisos -> 403
```

## Cómo probar manualmente

**Requisitos:** PHP ^8.2, Composer, Node, MySQL Laragon 3306. `.env` `DB_HOST=127.0.0.1 DB_PORT=3306 DB_DATABASE=sistema DB_USERNAME=root DB_PASSWORD=` (vacío).

```bash
# MySQL Laragon: Iniciar todo -> crear DB sistema en HeidiSQL si no existe
/c/laragon/bin/mysql/mysql-8.4.3-winx64/bin/mysql -u root -e "CREATE DATABASE IF NOT EXISTS sistema CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

php artisan migrate:fresh --seed
npm install && npm run build # o npm run dev (watch)
php artisan serve --host=127.0.0.1 --port=3000
# -> http://localhost:3000/login
```

Credenciales seed `UserSeeder.php:14` pass `1234`:
* `admin@example.com` -> Administrador (acceso total)
* `carlos@example.com` -> Secretario (CRUD completo clientes)
* `juanperez@example.com` -> Profesional (403 en /clientes)

## Estándar de interfaz y extensiones opcionales

El CRUD mínimo usa Blade + Tailwind (responsive) sin Livewire, siguiendo el patrón `servicios-viejo`. El estándar máximo opcional del proyecto propone:

* Componente Livewire `app/Livewire/Servicios/Index.php:19` (propiedades `nombre, costoServicio, search, showModal`, reglas `Rule::unique`, métodos `createServicio, editServicio, saveServicio, confirmDelete, deleteServicio` con `authorizeManagement()` + validación + `softDelete`) + vista `resources/views/livewire/servicios/index.blade.php:15` (Tailwind + Alpine `x-data="{ formOpen, deleteOpen }"`, `wire:model.live.debounce`, `wire:click`, `wire:loading`, modales `x-show`).
* Alpine.js solo para interacciones locales (abrir/cerrar modales sin petición).
* Tailwind para estilos y responsive.

Para llevar `clientes` al máximo, replicar ese patrón con campos `nombre, apellido, dni, telefono, correo, domicilio, fecha_nacimiento`, manteniendo mismas `rules` y `ClientePolicy`. La arquitectura sigue MVC sin necesidad de API separada (`guia-laravel-mvc-livewire.md`).

## Archivos tocados

```
app/Models/Cliente.php
database/migrations/2026_06_03_224907_create_clientes_table.php
database/factories/ClienteFactory.php
database/seeders/ClienteSeeder.php, DatabaseSeeder.php
app/Http/Requests/StoreClienteRequest.php, UpdateClienteRequest.php
app/Policies/ClientePolicy.php
app/Http/Controllers/ClienteController.php, Controller.php (AuthorizesRequests)
routes/web.php
resources/views/clientes/{index,create,edit,show,_form}.blade.php
resources/views/layouts/navigation.blade.php
tests/Feature/ClienteTest.php, ProfileTest.php, TestCase.php
docs/crud-clientes.md (este documento)
```

## Referencias

* Fuente funcional: `openspec/specs/gestion-juridica/spec.md:17` y matriz de permisos `RoleSeeder.php:15`
* Guía MVC/Livewire: `docs/guia-laravel-mvc-livewire.md`
* Migraciones y modelos: `docs/migraciones_y_modelos.md`
