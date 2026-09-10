# Guía: Migraciones y Modelos (Para Principiantes)

## Conceptos básicos

### ¿Qué es una migración?
Un archivo PHP que define **cómo crear o modificar una tabla** en la base de datos.
- `up()` = crea o modifica algo
- `down()` = deshace ese cambio

Se guardan en `database/migrations/`. Para ejecutarlas: `php artisan migrate`

### ¿Qué es un modelo?
Una clase PHP que **representa una tabla** y permite interacturar con ella.
Ejemplo: `Cliente::all()` devuelve todos los clientes.

Se guardan en `app/Models/`.

### ¿Qué son las FK (Foreign Keys)?
Son **referencias entre tablas**. Por ejemplo, `procesos.cliente_id` apunta a `clientes.id`.
Laravel usa `->constrained()` para definir estas relaciones automáticamente.

---

## Estado actual del proyecto

Ya existen (implementado y probado, 82 tests):
- `users` — tabla base + `add_profesional_fields_to_users` (dni unique nullable, telefono, domicilio, fecha_nacimiento, fecha_ingreso, softDeletes corregido `dropColumn/dropSoftDeletes`) + modelo `User` (`HasFactory, HasRoles, SoftDeletes, Auditable`, `servicios() BelongsToMany`, `procesosComoProfesional/Coordinador HasMany`)
- `user_servicios` — tabla intermedia N:M `user_id, servicio_id` PK compuesta, `constrained cascade` (permite múltiples servicios por profesional, evita duplicados, disponible para `Proceso.servicio_id`)
- `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions` — creados por Spatie
- `audits` — tabla de auditoría
- `clientes`, `servicios`, `procesos`, `turnos`, `documentos`, `reportes`, `comprobantes_pago`, `notificaciones`, `estados_proceso`, `historial_estados_proceso` — ver secciones Fase 1-3

> **Documentación CRUD usuarios:** ver `docs/gestion-usuarios-roles-servicios.md` para `StoreUserRequest/UpdateUserRequest`, `UserPolicy`, `UserController`, `Livewire/Usuarios/Index`, rutas `users.*`, vistas `resources/views/users/*` y `livewire/usuarios/*`, registro `/register` deshabilitado, búsqueda por `name/email/dni`.

---

## Flujo de trabajo por tabla

Para cada tabla, el orden es:

```bash
# 1. Crear archivos (el flag -a ya incluye migration, factory, seeder, controller, requests, policy, resource)
php artisan make:model Xxxx -a

# 2. Editar la migración que se creó automáticamente

# 3. Editar el modelo si hace falta

# 4. Probar que funciona
php artisan migrate

# 5. Commit + push
git add ... archivos ...
git commit -m "feat: add xxxx table and model"
git push
```

---

## Fase 1 — Modificar tabla `users`

### Paso 1: Crear la migración

```bash
php artisan make:migration add_profesional_fields_to_users_table
```

### Paso 2: Editar la migración

**Archivo:** `database/migrations/xxxx_add_profesional_fields_to_users_table.php`

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('dni')->unique()->nullable()->after('password');
        $table->string('telefono')->nullable()->after('dni');
        $table->string('domicilio')->nullable()->after('telefono');
        $table->date('fecha_nacimiento')->nullable()->after('domicilio');
        $table->date('fecha_ingreso')->nullable()->after('fecha_nacimiento');
        $table->softDeletes()->after('updated_at');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['dni', 'telefono', 'domicilio', 'fecha_nacimiento', 'fecha_ingreso']);
        $table->dropSoftDeletes(); // corregido (antes dropColum/dropsoftDeletes)
    });
}
```

### Paso 3: Editar el modelo

**Archivo:** `app/Models/User.php`

Reemplazar `$fillable` por:
```php
protected $fillable = [
    'name',
    'email',
    'password',
    'dni',
    'telefono',
    'domicilio',
    'fecha_nacimiento',
    'fecha_ingreso',
];
```

Reemplazar `casts()` por:
```php
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'fecha_nacimiento' => 'date',
        'fecha_ingreso' => 'date',
    ];
}
```

### Paso 4: Probar migración

```bash
php artisan migrate
```

### Paso 5: Commit y push

```bash
git add database/migrations/xxxx_add_profesional_fields_to_users_table.php app/Models/User.php
git commit -m "feat: add profesional fields to users"
git push
```

---

## Fase 2 — Tablas sin FK

Estas tablas no dependen de otras, así que se crean primero.

### clientes (hecho)

**1. Crear archivos:**
```bash
php artisan make:model Cliente -a
```

**2. Editar la migración** (`database/migrations/xxxx_create_clientes_table.php`):
```php
public function up(): void
{
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
}
```

**3. Probar:**
```bash
php artisan migrate
```

**4. Commit y push:**
```bash
git add app/Models/Cliente.php app/Factories/ClienteFactory.php database/seeders/ClienteSeeder.php app/Http/Controllers/ClienteController.php app/Http/Requests/StoreClienteRequest.php app/Http/Requests/UpdateClienteRequest.php app/Policies/ClientePolicy.php app/Http/Resources/ClienteResource.php database/migrations/xxxx_create_clientes_table.php
git commit -m "feat: add clientes table and model"
git push
```

### servicios (hecho)

**1. Crear archivos:**
```bash
php artisan make:model Servicio -a
```

**2. Editar la migración** (`database/migrations/xxxx_create_servicios_table.php`):
```php
public function up(): void
{
    Schema::create('servicios', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->decimal('costo_servicio', 10, 2);
        $table->timestamps();
        $table->softDeletes();
    });
}
```

**3. Probar:**
```bash
php artisan migrate
```

**4. Commit y push:**
```bash
git add app/Models/Servicio.php app/Factories/ServicioFactory.php database/seeders/ServicioSeeder.php app/Http/Controllers/ServicioController.php app/Http/Requests/StoreServicioRequest.php app/Http/Requests/UpdateServicioRequest.php app/Policies/ServicioPolicy.php app/Http/Resources/ServicioResource.php database/migrations/xxxx_create_servicios_table.php
git commit -m "feat: add servicios table and model"
git push
```

### user_servicios — intermedia N:M (hecho, CU34)

Permite que **un usuario (Profesional) tenga múltiples servicios/especialidades** y un servicio múltiples profesionales.

**Migración** `database/migrations/2026_08_23_190000_create_user_servicios_table.php:13`:

```php
Schema::create('user_servicios', function (Blueprint $table) {
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('servicio_id')->constrained()->onDelete('cascade');
    $table->primary(['user_id','servicio_id']); // evita duplicados
});
```

**Modelos:**

```php
// app/Models/User.php:89
public function servicios(): BelongsToMany { return $this->belongsToMany(Servicio::class,'user_servicios'); }
// app/Models/Servicio.php:34
public function usuarios(): BelongsToMany { return $this->belongsToMany(User::class,'user_servicios'); }
```

**Uso:** ` $user->servicios()->sync([1,2])` idempotente; `whereHas('servicios')` para asignaciones a `Proceso.servicio_id`. Autorización `asignar_especialidad_servicio` (Coordinador/Directivo/Admin) y validación `exists:servicios,id` + check `hasRole('Profesional')` → `422` si no es profesional. Detalle en `docs/gestion-usuarios-roles-servicios.md:4`.

---

## Fase 3 — Tablas con FK

Estas tablas **dependen de otras**, por eso van después.

### procesos(hecho)

**1. Crear archivos:**
```bash
php artisan make:model Proceso -a
```

**2. Editar la migración** (`database/migrations/xxxx_create_procesos_table.php`):
```php
public function up(): void
{
    Schema::create('procesos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('cliente_id')->constrained()->onDelete('cascade');
        $table->foreignId('profesional_id')->nullable()->constrained('users')->onDelete('set null');
        $table->foreignId('servicio_id')->constrained()->onDelete('cascade');
        $table->foreignId('coordinador_id')->constrained('users')->onDelete('cascade');
        $table->string('nombre');
        $table->text('descripcion');
        $table->date('fecha_inicio');
        $table->enum('tipo', ['Civil', 'Comercial', 'Familia']);
        $table->string('estado')->default('pendiente');
        $table->text('motivo_rechazo')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
}
```

La migración inicial utilizaba un enum para estado. La migración 2026_09_08_000002_change_estado_column_to_string_on_procesos_table.php lo convierte en texto para almacenar el slug del catálogo estados_proceso. Las transiciones se registran en historial_estados_proceso.

**3. Probar:**
```bash
php artisan migrate
```

**4. Commit y push:**
```bash
git add app/Models/Proceso.php app/Factories/ProcesoFactory.php database/seeders/ProcesoSeeder.php app/Http/Controllers/ProcesoController.php app/Http/Requests/StoreProcesoRequest.php app/Http/Requests/UpdateProcesoRequest.php app/Policies/ProcesoPolicy.php app/Http/Resources/ProcesoResource.php database/migrations/xxxx_create_procesos_table.php
git commit -m "feat: add procesos table and model"
git push
```

### turnos(hecho)

**1. Crear archivos:**
```bash
php artisan make:model Turno -a
```

**2. Editar la migración** (`database/migrations/xxxx_create_turnos_table.php`):
```php
public function up(): void
{
    Schema::create('turnos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('cliente_id')->constrained()->onDelete('cascade');
        $table->foreignId('profesional_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('proceso_id')->nullable()->constrained()->onDelete('set null');
        $table->dateTime('fecha_hora');
        $table->boolean('es_externo')->default(false);
        $table->text('detalle_externo')->nullable();
        $table->enum('tipo', ['consulta_inicial', 'seguimiento', 'externo']);
        $table->timestamps();
        $table->softDeletes();
    });
}
```

**3. Probar:**
```bash
php artisan migrate
```

**4. Commit y push:**
```bash
git add app/Models/Turno.php app/Factories/TurnoFactory.php database/seeders/TurnoSeeder.php app/Http/Controllers/TurnoController.php app/Http/Requests/StoreTurnoRequest.php app/Http/Requests/UpdateTurnoRequest.php app/Policies/TurnoPolicy.php app/Http/Resources/TurnoResource.php database/migrations/xxxx_create_turnos_table.php
git commit -m "feat: add turnos table and model"
git push
```

### documentos(hecho)

**1. Crear archivos:**
```bash
php artisan make:model Documento -a
```

**2. Editar la migración** (`database/migrations/xxxx_create_documentos_table.php`):
```php
public function up(): void
{
    Schema::create('documentos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('proceso_id')->constrained()->onDelete('cascade');
        $table->string('archivo_path');
        $table->string('tipo_documento');
        $table->string('nombre');
        $table->timestamps();
        $table->softDeletes();
    });
}
```

**3. Probar:**
```bash
php artisan migrate
```

**4. Commit y push:**
```bash
git add app/Models/Documento.php app/Factories/DocumentoFactory.php database/seeders/DocumentoSeeder.php app/Http/Controllers/DocumentoController.php app/Http/Requests/StoreDocumentoRequest.php app/Http/Requests/UpdateDocumentoRequest.php app/Policies/DocumentoPolicy.php app/Http/Resources/DocumentoResource.php database/migrations/xxxx_create_documentos_table.php
git commit -m "feat: add documentos table and model"
git push
```

### reportes(hecho)

**1. Crear archivos:**
```bash
php artisan make:model Reporte -a
```

**2. Editar la migración** (`database/migrations/xxxx_create_reportes_table.php`):
```php
public function up(): void
{
    Schema::create('reportes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('proceso_id')->constrained()->onDelete('cascade');
        $table->foreignId('profesional_id')->constrained('users')->onDelete('cascade');
        $table->text('contenido');
        $table->date('fecha');
        $table->timestamps();
        $table->softDeletes();
    });
}
```

**3. Probar:**
```bash
php artisan migrate
```

**4. Commit y push:**
```bash
git add app/Models/Reporte.php app/Factories/ReporteFactory.php database/seeders/ReporteSeeder.php app/Http/Controllers/ReporteController.php app/Http/Requests/StoreReporteRequest.php app/Http/Requests/UpdateReporteRequest.php app/Policies/ReportePolicy.php app/Http/Resources/ReporteResource.php database/migrations/xxxx_create_reportes_table.php
git commit -m "feat: add reportes table and model"
git push
```

### comprobantes_pago(hecho)

**1. Crear archivos:**
```bash
php artisan make:model ComprobantePago -a
```

**2. Editar la migración** (`database/migrations/xxxx_create_comprobantes_pago_table.php`):
```php
public function up(): void
{
    Schema::create('comprobantes_pago', function (Blueprint $table) {
        $table->id();
        $table->foreignId('cliente_id')->constrained()->onDelete('cascade');
        $table->string('archivo_path');
        $table->date('fecha_subida');
        $table->text('descripcion')->nullable();
        $table->timestamps();
    });
}
```

**3. Probar:**
```bash
php artisan migrate
```

**4. Commit y push:**
```bash
git add app/Models/ComprobantePago.php app/Factories/ComprobantePagoFactory.php database/seeders/ComprobantePagoSeeder.php app/Http/Controllers/ComprobantePagoController.php app/Http/Requests/StoreComprobantePagoRequest.php app/Http/Requests/UpdateComprobantePagoRequest.php app/Policies/ComprobantePagoPolicy.php app/Http/Resources/ComprobantePagoResource.php database/migrations/xxxx_create_comprobantes_pago_table.php
git commit -m "feat: add comprobantes_pago table and model"
git push
```

### notificaciones

**1. Crear archivos:**
```bash
php artisan make:model Notificacione -a
```

**2. Editar la migración** (`database/migrations/xxxx_create_notificaciones_table.php`):
```php
public function up(): void
{
    Schema::create('notificaciones', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
        $table->foreignId('cliente_id')->nullable()->constrained()->onDelete('set null');
        $table->enum('canal', ['email', 'whatsapp']);
        $table->text('mensaje');
        $table->timestamp('fecha_envio');
        $table->enum('estado', ['enviado', 'fallido'])->default('enviado');
        $table->timestamps();
    });
}
```

**3. Probar:**
```bash
php artisan migrate
```

**4. Commit y push:**
```bash
git add app/Models/Notificacione.php app/Factories/NotificacioneFactory.php database/seeders/NotificacioneSeeder.php app/Http/Controllers/NotificacioneController.php app/Http/Requests/StoreNotificacioneRequest.php app/Http/Requests/UpdateNotificacioneRequest.php app/Policies/NotificacionePolicy.php app/Http/Resources/NotificacioneResource.php database/migrations/xxxx_create_notificaciones_table.php
git commit -m "feat: add notificaciones table and model"
git push
```

---

## Resumen de directorios

| Tipo | Ruta |
|------|------|
| Migraciones | `database/migrations/` |
| Modelos | `app/Models/` |
| Factories | `app/Factories/` |
| Seeders | `database/seeders/` |
| Controllers | `app/Http/Controllers/` |
| Requests | `app/Http/Requests/` |
| Policies | `app/Policies/` |
| Resources | `app/Http/Resources/` |

---

## Flag `-a` genera

| Archivo | Descripción |
|---------|-------------|
| Migration | Archivo para crear la tabla en la BD |
| Factory | Datos falsos para testing |
| Seeder | Datos iniciales para poblar la BD |
| Controller | Maneja peticiones HTTP |
| StoreRequest | Valida datos al crear |
| UpdateRequest | Valida datos al actualizar |
| Policy | Reglas de autorización |
| Resource | Formato JSON para la API |

---

## Rollback

Si ejecutaste `php artisan migrate` y algo salió mal:

```bash
# Deshace el último batch de migraciones
php artisan migrate:rollback
```

**¿Qué pasa cuando hacés rollback?**
- La tabla creada se **elimina** de la base de datos (drop)
- Los **archivos PHP siguen existiendo** en tu proyecto (no se borran)
- Volvés a editar la migración para corregir el error
- Volvés a ejecutar `php artisan migrate`

**Ejemplo:**
1. Creaste la migración de `clientes` y ejecutaste `migrate` → se creó la tabla
2. Te-disté cuenta de que falta una columna
3. Hacés `migrate:rollback` → la tabla `clientes` desaparece de la BD
4. Editás la migración, agregás la columna
5. Ejecutás `migrate` de nuevo

**Nota:** `migrate:rollback` solo deshace el último batch. Si ejecutaste `migrate` varias veces sin rollback, tenés que hacerlo varias veces para llegar al estado anterior.

---

## Nota sobre Role y Permission

Spatie ya creó las tablas y provee sus propios modelos en `vendor/spatie/laravel-permission`. **No hace falta crear modelos para `roles` ni `permissions`.**
