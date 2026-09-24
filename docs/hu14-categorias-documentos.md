# HU-14 — Gestión de Categorías de Documentos (CU37)

## Objetivo

Permitir a **Coordinador** y **Administrador** crear, modificar, activar y desactivar categorías de documentos para clasificar consistentemente la documentación de los legajos. Cumple HU-14 / CU37 de la Matriz Aequitas V2: CRUD con nombre único, estado activo/inactivo, baja lógica sin borrado físico, asociación obligatoria en nuevas cargas y denegación por rol incluso por URL/petición directa.

Fuente funcional: `C:\Users\Owner\Desktop\ESTUDIO\analisis y diseño\hu14\HU14.pdf` y Matriz V2 (`docs/aequitas-matriz-modulos-funciones-permisos-v2.md`).

## Alcance implementado

- CRUD de categorías/tipos documentales con `nombre` único case-insensitive + `descripcion` opcional + `activo`.
- `documentos.categoria_id` FK `RESTRICT` (bloquea borrado físico, conserva historial).
- Nuevas cargas (CU9/CU13 vía `Store/UpdateDocumentoRequest`) exigen `categoria_id` de categoría **activa**.
- Categoría inactiva: no se ofrece en `paraCargaNueva()`, documentos históricos la conservan y se muestran readonly.
- Permiso Spatie existente `gestionar_categorias_documentos` (Coordinador + Administrador full, ver `database/seeders/RoleSeeder.php`). Secretario, Profesional y Directivo reciben 403 en ruta y Livewire.
- Sin ruta `DELETE`: borrado físico prohibido por diseño.

## Componentes implementados

### 1. Modelo y migraciones

**Modelo** `app/Models/CategoriaDocumento.php`
* `table = 'categorias_documento'`, `fillable = ['nombre','descripcion','activo']`, `casts activo => boolean`
* Mutator `setNombreAttribute`: `trim()` + colapsar espacios múltiples (RN1)
* Relaciones `hasMany documentos`
* Scopes `activas()`, `ordenadas()` y `paraCargaNueva()` (solo activas ordenadas, usada en dropdowns CU9/CU13)
* `enUso()`: `documentos()->exists()` para aviso en UI

**Migración** `database/migrations/2026_09_21_000005_create_categorias_documento_table.php`
```php
Schema::create('categorias_documento', function (Blueprint $table) {
    $table->id();
    $table->string('nombre', 100)->unique(); // utf8mb4_unicode_ci => case-insensitive en MySQL
    $table->string('descripcion', 255)->nullable();
    $table->boolean('activo')->default(true);
    $table->timestamps();
    $table->index(['activo', 'nombre'], 'idx_cat_activo_nombre');
});
```

**Migración** `database/migrations/2026_09_21_000006_add_categoria_id_to_documentos_table.php`
```php
Schema::table('documentos', function (Blueprint $table) {
    $table->foreignId('categoria_id')->nullable()->after('proceso_id')
        ->constrained('categorias_documento')->cascadeOnUpdate()->restrictOnDelete();
});
```
Nullable para no romper históricos; nuevas cargas la exigen por validación. Reversible con `dropConstrainedForeignId`.

### 2. Factory y Seeder

**Factory** `database/factories/CategoriaDocumentoFactory.php`
* `nombre => ucfirst(fake()->unique()->words(2, true))`, `descripcion => sentence(6)`, `activo => true`, state `inactiva()`

**Seeder** `database/seeders/CategoriaDocumentoSeeder.php`
* `firstOrCreate` por nombre: Identidad, Comprobante ARCA, Informe profesional, Contrato/Honorarios, Judicial (todas activas)

**Registro** `database/seeders/DatabaseSeeder.php` incluye `CategoriaDocumentoSeeder::class` después de `EstadoProcesoSeeder`.

### 3. Validación y autorización

**Livewire** `app/Livewire/Categorias/Index.php` (patrón `EstadosProceso/Index`, sin slug/orden)
* `mount()`: `Gate::authorize('viewAny', CategoriaDocumento::class)`
* `rules`: `nombre required|min:3|max:100|unique`, `descripcion nullable|max:255`; mensajes accionables ES ("El nombre es obligatorio", "Ya existe una categoría con ese nombre. Use otro nombre o reactive la existente.")
* Normalización previa: trim + colapsar espacios; chequeo extra case-insensitive con `whereRaw('LOWER(nombre) = ?')` para que SQLite (tests) se comporte como MySQL `unicode_ci`
* `saveCategoria()`: create con `activo=true` / update sin cambiar `id` (asociaciones intactas)
* `confirmDeactivate/deactivateCategoria()`: baja lógica siempre permitida aun en uso, mensaje con conteo de docs históricos
* `activateCategoria()`: reactivación
* Filtros `search` (live) + `filtro` todas/activas/inactivas; `withCount('documentos')`

**Policy** `app/Policies/CategoriaDocumentoPolicy.php` (autodescubierta por Laravel, como `EstadoProcesoPolicy`)
```
viewAny / create / update / activate / deactivate -> can('gestionar_categorias_documentos')
```
`Gate::before` en `AppServiceProvider` ya contempla `activate/deactivate` como abilities de policy, por lo que Administrador pasa por policy (tiene todos los permisos por `RoleSeeder`).

**Documento (integración CU9/CU13)**
* `app/Models/Documento.php`: `fillable += categoria_id`, `belongsTo categoria()`
* `StoreDocumentoRequest`: `categoria_id required|exists:categorias_documento,id,activo=true` + mensajes ("La categoría es obligatoria", "no está disponible para nuevas cargas")
* `UpdateDocumentoRequest`: `categoria_id sometimes|required|exists:...activo=true`
* `database/factories/DocumentoFactory.php`: `categoria_id => CategoriaDocumento::factory()` (evita romper `AuthorizationPolicyTest`)

### 4. Vistas, rutas y menú

**Livewire Blade** `resources/views/livewire/categorias/index.blade.php`
* Tabla Nombre | Descripción | Situación (badge) | Docs en uso | Acciones (Editar, Desactivar/Reactivar)
* Modal crear/editar (nombre + descripción, errores inline) y modal desactivar con conteo `docsEnUso`
* Alpine `formOpen/deactivateOpen` + eventos `categoria-guardada/desactivada`, igual que estados-proceso

**Wrapper** `resources/views/categorias/index.blade.php` con `<x-app-layout>` + `@livewire('categorias.index')`

**Rutas** `routes/web.php`
```php
Route::middleware(['auth', 'permission:gestionar_categorias_documentos'])->group(function () {
    Route::get('/categorias', fn () => view('categorias.index'))->name('categorias.index');
});
```
Sin `DELETE`. El middleware Spatie deniega URL directa con 403.

**Menú** `resources/views/layouts/navigation.blade.php`
* Ítem `Categorías` con `@can('gestionar_categorias_documentos')`, activo en `categorias.*`, solo visible para Coordinador/Administrador.

### 5. Pruebas

**Tests** `tests/Feature/CategoriaDocumentoTest.php` (SQLite `:memory:`, como `phpunit.xml`)
* Coordinador y Administrador ven `/categorias` 200; Secretario/Profesional/Directivo 403
* Crear válida queda disponible en `paraCargaNueva()`
* Duplicado case-insensitive (`DNI` vs `  dni  `) y vacío → error `nombre`
* Editar no rompe `documento.categoria_id`
* Desactivar en uso conserva FK y oculta de cargas; reactivar vuelve a ofrecerse
* Solo activas en carga nueva
* No autorizado denegado por ruta (patrón del proyecto: Livewire no autorizado se verifica vía ruta, ver `EstadoProcesoIndexTest`)

Ejecución:
```
php artisan test --filter=CategoriaDocumentoTest   # 9 passed, 27 assertions
php artisan test --filter="RolePermissionTest|EstadoProcesoIndexTest|ComprobantePagoUploadTest"  # 14 passed
php artisan test --filter=AuthorizationPolicyTest  # 7 passed
```

## Verificación de migraciones

`DB_CONNECTION=sqlite DB_DATABASE=...test_hu14.sqlite php artisan migrate --force` ejecuta `000005` y `000006` con `DONE` (sintaxis válida). En MySQL real falta ejecutar porque el servidor Laragon estaba apagado y `.env` apunta a `DB_HOST=mysql` (Sail). Pasos pendientes:
1. Iniciar MySQL en Laragon.
2. Usar `DB_HOST=127.0.0.1 DB_DATABASE=sistema DB_USERNAME=root DB_PASSWORD=` (ver `.env.bak-laragon`).
3. `php artisan migrate --seed` (incluye `CategoriaDocumentoSeeder`).

## Trazabilidad HU-14

| Requerimiento PDF | Dónde quedó |
|---|---|
| CRUD nombre único + activo/inactivo | Modelo + migraciones + Livewire `saveCategoria` |
| Asociar categoría en nuevas cargas | `categoria_id` FK + Requests exigen activa |
| Baja lógica, inactiva oculta, conserva historial | `activo=false`, `paraCargaNueva()`, modal con conteo, `RESTRICT` |
| Sin borrado físico | Sin ruta delete, sin `destroy`, FK restrict |
| Validar vacíos/duplicados/edición/reactivación/en uso | Rules + mensajes + chequeo `LOWER()` + tests |
| Campos mínimos nombre/estado (+descripcion acordada) | `nombre, descripcion, activo, timestamps` |
| CU37 solo Coordinador/Admin, 403 por URL directa | Permiso Spatie + middleware + Policy + Gate + menú `@can` + tests 403 |
| DoD migración/modelo/validación/autorización/UI/baja/tests | Ver secciones 1–5 |
