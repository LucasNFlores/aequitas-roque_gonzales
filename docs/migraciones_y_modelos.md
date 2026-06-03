# Guía: Migraciones y Modelos

## Estado actual del proyecto

Ya existen:
- `users` — tabla base + modelo `User` con `HasRoles` de Spatie
- `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions` — creados por Spatie
- `audits` — OwenIt Auditing

**Falta agregar a `users`:** dni, telefono, domicilio, fecha_nacimiento, fecha_ingreso, deleted_at

---

## Fase 1 — users (modificar existente)

```bash
php artisan make:migration add_profesional_fields_to_users_table
```

```php
// En la migración:
$table->string('dni')->unique()->nullable();
$table->string('telefono')->nullable();
$table->string('domicilio')->nullable();
$table->date('fecha_nacimiento')->nullable();
$table->date('fecha_ingreso')->nullable();
$table->softDeletes();
```

Luego actualizar el modelo `User` con los nuevos fillable:

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

---

## Fase 2 — Negocio base (sin FK)

```bash
# clientes
php artisan make:model Cliente -a
php artisan make:migration create_clientes_table

# servicios
php artisan make:model Servicio -a
php artisan make:migration create_servicios_table
```

---

## Fase 3 — Negocio con FK

```bash
# procesos
php artisan make:model Proceso -a
php artisan make:migration create_procesos_table

# turnos
php artisan make:model Turno -a
php artisan make:migration create_turnos_table

# documentos
php artisan make:model Documento -a
php artisan make:migration create_documentos_table

# reportes
php artisan make:model Reporte -a
php artisan make:migration create_reportes_table

# comprobantes_pago
php artisan make:model ComprobantePago -a
php artisan make:migration create_comprobantes_pago_table

# notificaciones
php artisan make:model Notificacione -a
php artisan make:migration create_notificaciones_table
```

---

## Ejecutar migraciones

```bash
php artisan migrate
```

---

## Modelo User actualizado

```php
// app/Models/User.php
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

---

## Estructura de commits

Uno por tabla:

```bash
git add database/migrations/xxxx_add_profesional_fields_to_users_table.php
git add app/Models/User.php
git commit -m "feat: add profesional fields to users table"

git add database/migrations/xxxx_create_clientes_table.php
git add app/Models/Cliente.php
git add app/Factories/ClienteFactory.php
git add database/seeders/ClienteSeeder.php
git add app/Http/Controllers/ClienteController.php
git add app/Http/Requests/StoreClienteRequest.php
git add app/Http/Requests/UpdateClienteRequest.php
git add app/Policies/ClientePolicy.php
git add app/Http/Resources/ClienteResource.php
git commit -m "feat: add clientes table and Cliente model"

# ... y así sucesivamente
```

---

## Flag `-a` genera

Factory, seeder, controller (API), form request (store + update), policy, resource, migration.

## Nota sobre Role y Permission

Spatie ya provee sus modelos. No hace falta crear nuevos.