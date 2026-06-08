# Explicación del modelo User.php

> **Archivo:** `app/Models/User.php`
> **Tabla asociada:** `users`

---

## ¿Qué es un modelo Eloquent?

Eloquent es el ORM de Laravel. En criollo: **cada modelo es una clase PHP que representa una tabla de la base de datos**. En vez de escribir `SELECT * FROM users`, hacés `User::all()` y te devuelve todos los registros como objetos. Es la capa que traduce filas de SQL a objetos PHP y viceversa.

---

## Desglose línea por línea

### 1. Namespace

```php
namespace App\Models;
```

Define en qué carpeta lógica vive la clase. Todo lo que esté en `app/Models/` pertenece a este namespace. Sirve para que PHP sepa encontrar la clase sin conflictos de nombres.

### 2. Imports (use)

```php
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Traits\HasRoles;
```

Cada `use` importa una clase o trait de otro lado para poder usarla acá sin escribir el nombre completo. Explicación de cada una:

| Import | ¿Qué hace? |
|--------|-----------|
| `UserFactory` | Permite generar usuarios falsos para pruebas con `User::factory()->create()` |
| `HasFactory` | Trait que agrega el método `.factory()` al modelo |
| `HasMany` | Tipo de retorno para relaciones uno-a-muchos (un usuario tiene muchos turnos, por ejemplo) |
| `SoftDeletes` | Trait que implementa el borrado lógico (ver sección más abajo) |
| `Authenticatable` | La clase base de Laravel para usuarios con login, en vez de `Model` normal |
| `Notifiable` | Permite que al usuario se le puedan enviar notificaciones (mail, etc.) |
| `Auditable` | Interfaz del paquete de auditoría (registra quién modificó qué) |
| `HasRoles` | Trait de Spatie que agrega roles y permisos al usuario |

### 3. Declaración de la clase

```php
class User extends Authenticatable implements Auditable
```

- **`extends Authenticatable`**: User extiende la clase para autenticación de Laravel (login, sesiones, etc.). No es un `Model` común, es uno especial que ya tiene métodos como `Auth::login()`.
- **`implements Auditable`**: Promete implementar los métodos que pide la interfaz `Auditable`. El trait `\OwenIt\Auditing\Auditable` (usado más abajo) se encarga de cumplir ese contrato.

### 4. Traits

#### ¿Qué es un trait?

Un **trait** es un paquete de métodos que se "copian" dentro de la clase. Piensenlo como un copiar-y-pegar automático de código reutilizable. En vez de heredar de una sola clase padre, podés usar múltiples traits.

```php
use HasFactory, HasRoles, Notifiable, SoftDeletes;
use \OwenIt\Auditing\Auditable;
```

| Trait | ¿Qué aporta a User? |
|-------|-------------------|
| `HasFactory` | Método `factory()` para crear usuarios de prueba |
| `HasRoles` | Métodos `assignRole()`, `hasRole()`, `givePermissionTo()`, etc. (roles y permisos) |
| `Notifiable` | Método `notify()` para enviar mails, notificaciones en base de datos, etc. |
| `SoftDeletes` | Borrado lógico: cuando "borrás" un usuario, solo pone fecha en `deleted_at`, no lo elimina de verdad |
| `Auditable` | Cada vez que se crea, modifica o borra un usuario, queda registrado en la tabla `audits` |

#### SoftDeletes en detalle

```php
// Borrado normal (sin soft deletes)
$user->delete();  // DELETE FROM users WHERE id = 1 → chau fila

// Borrado lógico (con soft deletes)
$user->delete();  // UPDATE users SET deleted_at = NOW() WHERE id = 1
```

**Ventaja**: Podés recuperar lo "borrado" y tener historial. Laravel automáticamente ignora los registros con `deleted_at` no nulo en todas las consultas, salvo que uses `withTrashed()`.

### 5. `$fillable` — Asignación masiva protegida

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

**¿Qué problema resuelve?** Imaginate que alguien manda un formulario con `is_admin=1` en un campo oculto. Si hacés `User::create($request->all())` sin protección, ese campo se guardaría y el atacante se vuelve admin.

`$fillable` es una **lista blanca**: solo estos campos se pueden asignar masivamente con `create()` o `fill()`. Cualquier otro campo que venga en el array es ignorado.

```php
// Esto funciona porque 'name' está en $fillable
User::create(['name' => 'Juan', 'email' => 'juan@mail.com']);

// Esto ignora 'is_admin' porque NO está en $fillable
User::create(['name' => 'Hacker', 'is_admin' => true]);
```

Es `protected` porque es una propiedad interna de la clase, no accesible desde afuera.

### 6. `$hidden` — Ocultar campos sensibles

```php
protected $hidden = [
    'password',
    'remember_token',
];
```

Cuando convertís un User a JSON o array (por ejemplo al devolverlo en una API), estos campos **no aparecen**. Así evitás filtrar el hash de la contraseña.

```php
return User::first(); // En JSON: muestra name, email, etc pero NO password ni remember_token
```

### 7. `casts()` — Conversión automática de tipos

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

**¿Por qué hace falta?** La base de datos guarda todo como texto, números o timestamps crudos. Los casts le dicen a Eloquent cómo interpretar cada columna al leerla y cómo guardarla al escribirla.

| Cast | La columna en BD | Lo que recibís en PHP | Ejemplo de uso |
|------|-----------------|----------------------|----------------|
| `datetime` | `2024-03-15 10:30:00` | Objeto `Carbon` (fecha con hora) | `$user->email_verified_at->format('d/m/Y')` |
| `date` | `1990-05-20` | Objeto `Carbon` (solo fecha) | `$user->fecha_nacimiento->age` (edad) |
| `hashed` | — | Al asignar, automáticamente hashea con bcrypt | `$user->password = '123456'` → guarda `$2y$10...` |

#### ¿Por qué `casts()` es un método y no una propiedad `$casts`?

En versiones viejas de Laravel se usaba:

```php
protected $casts = [
    'fecha_inicio' => 'date',
];
```

Desde Laravel 11+, se recomienda usar el **método** `casts()` porque permite lógica dinámica (por ejemplo, casts condicionales según el entorno). En este proyecto se usa el método.

### 8. Relaciones (Relationships)

Las relaciones son métodos que le dicen a Eloquent **cómo se conectan las tablas entre sí**. No ejecutan consultas; devuelven un "query builder" que podés encadenar o ejecutar cuando quieras.

#### `hasMany` — "Un usuario tiene muchos X"

```php
public function turnos(): HasMany
{
    return $this->hasMany(Turno::class);
}

public function reportes(): HasMany
{
    return $this->hasMany(Reporte::class);
}
```

**¿Cómo funciona?** Laravel busca una columna con el nombre de esta tabla en singular + `_id` en la otra tabla:

- `User::class` → tabla `users` → busca `user_id` en la tabla `turnos`
- `User::class` → tabla `users` → busca `user_id` en la tabla `reportes`

**Ejemplo de uso:**

```php
$user = User::find(1);
$turnos = $user->turnos;           // Todos los turnos del usuario
$turnosHoy = $user->turnos()->whereDate('fecha_hora', today())->get();

// Cargar turnos junto con el usuario en una sola consulta (evita N+1)
$user = User::with('turnos')->find(1);

// Crear un turno asociado al usuario
$user->turnos()->create([
    'fecha_hora' => now(),
    'tipo' => 'consulta_inicial',
]);
```

#### `hasMany` con clave foránea personalizada

```php
public function procesosComoProfesional(): HasMany
{
    return $this->hasMany(Proceso::class, 'profesional_id');
}

public function procesosComoCoordinador(): HasMany
{
    return $this->hasMany(Proceso::class, 'coordinador_id');
}
```

**¿Por qué se especifica la FK?** La tabla `procesos` tiene dos columnas que apuntan a `users`: `profesional_id` y `coordinador_id`. Como ninguna se llama `user_id`, hay que decirle explícitamente a Eloquent cuál usar.

```php
$user = User::find(1);

// Procesos donde el usuario es el profesional asignado
$user->procesosComoProfesional;  // WHERE profesional_id = 1

// Procesos donde el usuario es el coordinador asignado
$user->procesosComoCoordinador;  // WHERE coordinador_id = 1
```

Esto permite que un mismo usuario tenga dos roles distintos en dos conjuntos de procesos diferentes.

```php
public function notificaciones(): HasMany
{
    return $this->hasMany(Notificacion::class);
}
```

Relación estándar: busca `user_id` en la tabla `notificaciones`.

---

## Tipos de retorno en las relaciones

Notar que cada método tiene un tipo de retorno explícito:

```php
public function turnos(): HasMany
```

Esto no es obligatorio para que funcione, pero ayuda al IDE a sugerir métodos y detecta errores. `HasMany` y `BelongsTo` son clases que extienden `Relation` y proveen métodos como `where()`, `create()`, `save()`, etc.

---

## El modelo completo en acción

```php
// Buscar un usuario con sus relaciones cargadas
$user = User::with([
    'turnos',
    'procesosComoProfesional.servicio',
    'roles',
])->find(1);

// Verificar rol
if ($user->hasRole('profesional')) {
    // Procesos asignados como profesional, ordenados por fecha
    $procesos = $user->procesosComoProfesional()
        ->where('estado', 'en_proceso')
        ->orderBy('fecha_inicio')
        ->get();
}

// Asignar rol
$user->assignRole('coordinador');

// Enviar notificación
$user->notify(new NuevaTareaAsignada($tarea));

// Crear con datos validados
$nuevo = User::create([
    'name' => 'María Gómez',
    'email' => 'maria@estudio.com',
    'password' => 'secreto123',   // Se hashea automáticamente por el cast
    'fecha_ingreso' => '2026-06-08', // Se convierte a Carbon por el cast date
]);

// Borrado lógico
$nuevo->delete();        // deleted_at = NOW(), no se borra de verdad
$nuevo->restore();       // deleted_at = NULL, vuelve a aparecer

// Borrado real (sin vuelta atrás)
$nuevo->forceDelete();   // DELETE FROM users WHERE id = ?
```

---

## Resumen visual

```
┌─────────────────────────────────────────────┐
│                  User                        │
│  extends Authenticatable implements Auditable│
├─────────────────────────────────────────────┤
│  TRAITS                                       │
│  ├─ HasFactory    → User::factory()          │
│  ├─ HasRoles      → roles y permisos         │
│  ├─ Notifiable    → enviar notificaciones    │
│  ├─ SoftDeletes   → borrado lógico           │
│  └─ Auditable     → registro de cambios      │
├─────────────────────────────────────────────┤
│  PROPIEDADES                                  │
│  $fillable → qué campos se pueden crear       │
│  $hidden   → qué campos son secretos (JSON)   │
├─────────────────────────────────────────────┤
│  MÉTODOS                                      │
│  casts()   → cómo interpretar cada columna    │
│  turnos()         → hasMany(Turno)            │
│  reportes()       → hasMany(Reporte)          │
│  procesosProfesional() → hasMany(Proceso)     │
│  procesosCoordinador() → hasMany(Proceso)     │
│  notificaciones() → hasMany(Notificacion)     │
├─────────────────────────────────────────────┤
│  TABLA ASOCIADA                               │
│  users (id, name, email, password,            │
│  dni, telefono, domicilio,                    │
│  fecha_nacimiento, fecha_ingreso,             │
│  timestamps, deleted_at)                      │
└─────────────────────────────────────────────┘
```
