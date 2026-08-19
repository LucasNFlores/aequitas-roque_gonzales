# Explicación del modelo Proceso.php

> **Archivo:** `app/Models/Proceso.php`
> **Tabla asociada:** `procesos`

---

## ¿Por qué Proceso es especial?

Proceso es el modelo más completo del sistema para entender relaciones. A diferencia de User (que solo tiene `hasMany`), Proceso tiene **las dos caras de la moneda**: `belongsTo` (pertenece a) y `hasMany` (tiene muchos). Es el que conecta casi todas las tablas entre sí.

---

## Desglose línea por línea

### 1. Namespace e imports

```php
namespace App\Models;

use Database\Factories\ProcesoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
```

| Import | ¿Qué hace? |
|--------|-----------|
| `ProcesoFactory` | Permite generar procesos falsos para pruebas |
| `HasFactory` | Trait que agrega el método `.factory()` |
| `Model` | Clase base de Eloquent (todos los modelos la extienden, salvo User) |
| `BelongsTo` | Tipo de retorno para relaciones "pertenece a uno" (muchos → uno) |
| `HasMany` | Tipo de retorno para relaciones "tiene muchos" (uno → muchos) |
| `SoftDeletes` | Trait de borrado lógico |

### 2. Declaración de la clase

```php
class Proceso extends Model
{
    use HasFactory, SoftDeletes;
```

A diferencia de User, Proceso extiende `Model` a secas. No maneja login ni sesiones, solo representa una tabla. Usa los traits `HasFactory` y `SoftDeletes`.

### 3. `$fillable` — Campos que se pueden crear masivamente

```php
protected $fillable = [
    'cliente_id',
    'profesional_id',
    'servicio_id',
    'coordinador_id',
    'nombre',
    'descripcion',
    'fecha_inicio',
    'tipo',
    'estado',
    'motivo_rechazo',
];
```

**Lo interesante acá**: incluye varias claves foráneas (FK). `cliente_id`, `profesional_id`, `servicio_id` y `coordinador_id` son columnas que apuntan a otras tablas. Están en `$fillable` porque al crear un proceso necesitás decir qué cliente, qué profesional, etc.

```
Proceso::create([
    'cliente_id' => 5,        // FK → clientes.id
    'servicio_id' => 2,       // FK → servicios.id
    'profesional_id' => 3,    // FK → users.id
    'nombre' => 'Sucesión Pérez',
    'tipo' => 'Familia',
    'estado' => 'pendiente',
]);
```

### 4. `casts()` — Conversión de tipos

```php
protected function casts(): array
{
    return [
        'fecha_inicio' => 'date',
    ];
}
```

Un solo cast en Proceso: `fecha_inicio` como fecha. El resto de los campos son strings, números o enums que Eloquent ya sabe manejar.

### 5. Relaciones — La parte más importante

Proceso tiene **7 relaciones**: 4 `belongsTo` (el proceso pertenece a alguien) + 3 `hasMany` (el proceso tiene cosas).

---

## `belongsTo` — "Un proceso pertenece a..."

Cuando la FK está en **esta** tabla (procesos tiene `cliente_id`), usamos `belongsTo`. Es el lado "muchos → uno".

### Caso 1: belongsTo estándar

```php
public function cliente(): BelongsTo
{
    return $this->belongsTo(Cliente::class);
}

public function servicio(): BelongsTo
{
    return $this->belongsTo(Servicio::class);
}
```

**¿Cómo funciona?** Laravel mira `Cliente::class` → deduce tabla `clientes` → busca la columna `cliente_id` en `procesos`.

**Uso:**

```php
$proceso = Proceso::find(1);

// Acceder al cliente dueño del proceso
echo $proceso->cliente->nombre;        // "Juan"
echo $proceso->cliente->apellido;      // "Pérez"

// Acceder al servicio contratado
echo $proceso->servicio->nombre;        // "Asesoría legal"
echo $proceso->servicio->costo_servicio; // 15000.00
```

### Caso 2: belongsTo con FK personalizada

```php
public function profesional(): BelongsTo
{
    return $this->belongsTo(User::class, 'profesional_id');
}

public function coordinador(): BelongsTo
{
    return $this->belongsTo(User::class, 'coordinador_id');
}
```

**¿Por qué el segundo parámetro?** Ambas relaciones apuntan a la misma tabla (`users`), pero por columnas distintas. Si no especificás la FK, Laravel buscaría `user_id` y no la encontraría (no existe esa columna en `procesos`).

**Visualización:**

```
┌──────────────────────┐
│       procesos        │
├──────────────────────┤
│ id                   │
│ cliente_id  ─────────┼──→ clientes.id
│ profesional_id ──────┼──→ users.id  (rol: profesional)
│ coordinador_id ──────┼──→ users.id  (rol: coordinador)
│ servicio_id ─────────┼──→ servicios.id
│ nombre               │
│ ...                  │
└──────────────────────┘
```

**Uso:**

```php
$proceso = Proceso::find(1);

// El profesional asignado
$proceso->profesional->name;     // "Dr. García"
$proceso->profesional->email;    // "garcia@estudio.com"

// El coordinador asignado
$proceso->coordinador->name;     // "Lic. Martínez"

// Son dos personas distintas desde la misma tabla users
```

> **Analogía:** Un proceso es como un expediente judicial. Tiene un cliente (el dueño del caso), un profesional (el abogado), un coordinador (el que organiza), y un servicio (lo que se contrató). Todos son roles distintos, pero el profesional y el coordinador salen de la misma lista de personas (users).

### Resumen de belongsTo

| Relación | Tabla destino | FK usada | Método |
|----------|-------------|----------|--------|
| `cliente()` | `clientes` | `cliente_id` | estándar |
| `servicio()` | `servicios` | `servicio_id` | estándar |
| `profesional()` | `users` | `profesional_id` | personalizada |
| `coordinador()` | `users` | `coordinador_id` | personalizada |

---

## `hasMany` — "Un proceso tiene muchos..."

Cuando la FK está en **otra** tabla (turnos tiene `proceso_id`), usamos `hasMany`. Es el lado "uno → muchos".

```php
public function turnos(): HasMany
{
    return $this->hasMany(Turno::class);
}

public function documentos(): HasMany
{
    return $this->hasMany(Documento::class);
}

public function reportes(): HasMany
{
    return $this->hasMany(Reporte::class);
}
```

**¿Cómo funciona?** Laravel busca `proceso_id` en la tabla del modelo que le pasás:

- `Turno::class` → tabla `turnos` → busca columna `proceso_id`
- `Documento::class` → tabla `documentos` → busca columna `proceso_id`
- `Reporte::class` → tabla `reportes` → busca columna `proceso_id`

**Uso:**

```php
$proceso = Proceso::find(1);

// Todos los turnos de este proceso
foreach ($proceso->turnos as $turno) {
    echo $turno->fecha_hora;  // "2026-06-15 10:00:00"
}

// Todos los documentos
foreach ($proceso->documentos as $doc) {
    echo $doc->nombre;        // "demanda.pdf"
    echo $doc->archivo_path;  // "documentos/demanda.pdf"
}

// Todos los reportes del profesional
foreach ($proceso->reportes as $reporte) {
    echo $reporte->contenido;
    echo $reporte->fecha;
}

// Cantidad de documentos
$proceso->documentos()->count();  // 3
```

---

## Las dos caras de la misma relación

La relación entre Proceso y Turno se ve desde ambos lados:

```
LADO hasMany (Proceso)              LADO belongsTo (Turno)
┌──────────────┐                   ┌──────────────┐
│   Proceso    │  1 ───────── N   │    Turno     │
│              │                   │              │
│  turnos()    │                   │  proceso()   │
│  hasMany     │                   │  belongsTo   │
└──────────────┘                   └──────────────┘
```

```php
// Desde el proceso (tiene muchos turnos)
$proceso->turnos;           // Colección de turnos

// Desde el turno (pertenece a un proceso)
$turno->proceso;            // Un solo proceso
$turno->proceso->nombre;    // "Sucesión Pérez"
```

---

## El modelo completo en acción

```php
// Crear un proceso con todo
$proceso = Proceso::create([
    'cliente_id' => 5,
    'profesional_id' => 3,
    'servicio_id' => 2,
    'coordinador_id' => 4,
    'nombre' => 'Divorcio Gómez',
    'descripcion' => 'Trámite de divorcio por mutuo acuerdo',
    'fecha_inicio' => '2026-06-08',
    'tipo' => 'Familia',
    'estado' => 'pendiente',
]);

// Cargar un proceso con TODAS sus relaciones en una sola consulta
$proceso = Proceso::with([
    'cliente',
    'profesional',
    'coordinador',
    'servicio',
    'turnos',
    'documentos',
    'reportes',
])->find(1);

// Navegar las relaciones encadenadas
echo $proceso->cliente->nombre;                       // "Juan"
echo $proceso->profesional->name;                     // "Dr. García"
echo $proceso->turnos->first()->fecha_hora;           // "2026-06-15 10:00:00"
echo $proceso->reportes->last()->contenido;           // "..."

// Filtrar turnos de un proceso
$turnosPendientes = $proceso->turnos()
    ->where('fecha_hora', '>', now())
    ->orderBy('fecha_hora')
    ->get();

// Agregar un turno al proceso
$proceso->turnos()->create([
    'cliente_id' => $proceso->cliente_id,
    'profesional_id' => $proceso->profesional_id,
    'fecha_hora' => '2026-07-01 09:00:00',
    'tipo' => 'seguimiento',
]);

// Borrado lógico (SoftDeletes)
$proceso->delete();       // deleted_at = NOW()
$proceso->restore();      // recuperado
$proceso->forceDelete();  // borrado real, sin vuelta atrás
```

---

## Resumen visual

```
┌──────────────────────────────────────────────────┐
│                    Proceso                        │
│              extends Model                        │
├──────────────────────────────────────────────────┤
│  TRAITS                                           │
│  ├─ HasFactory     → Proceso::factory()          │
│  └─ SoftDeletes    → borrado lógico              │
├──────────────────────────────────────────────────┤
│  PROPIEDADES                                      │
│  $fillable → cliente_id, profesional_id,          │
│              servicio_id, coordinador_id,         │
│              nombre, descripcion, fecha_inicio,    │
│              tipo, estado, motivo_rechazo         │
├──────────────────────────────────────────────────┤
│  MÉTODOS                                          │
│  casts() → fecha_inicio: date                     │
│                                                    │
│  BELONGS TO (el proceso pertenece a...)            │
│  ├─ cliente()      → belongsTo(Cliente)           │
│  ├─ servicio()     → belongsTo(Servicio)          │
│  ├─ profesional()  → belongsTo(User, profesional) │
│  └─ coordinador()  → belongsTo(User, coordinador) │
│                                                    │
│  HAS MANY (el proceso tiene...)                    │
│  ├─ turnos()       → hasMany(Turno)               │
│  ├─ documentos()   → hasMany(Documento)           │
│  └─ reportes()     → hasMany(Reporte)             │
├──────────────────────────────────────────────────┤
│  TABLA ASOCIADA                                   │
│  procesos (id, cliente_id, profesional_id,        │
│  servicio_id, coordinador_id, nombre,             │
│  descripcion, fecha_inicio, tipo, estado,         │
│  motivo_rechazo, timestamps, deleted_at)           │
└──────────────────────────────────────────────────┘
```

---

## Resumen de conceptos vistos

| Concepto | Dónde se ve en Proceso |
|----------|----------------------|
| `$fillable` | Incluye 4 FK + campos propios |
| `casts()` | `fecha_inicio => date` |
| `SoftDeletes` | Trait, borrado lógico |
| `belongsTo` estándar | `cliente()`, `servicio()` |
| `belongsTo` con FK custom | `profesional()`, `coordinador()` |
| `hasMany` estándar | `turnos()`, `documentos()`, `reportes()` |
| `BelongsTo` vs `HasMany` | Mismo modelo, ambas caras de las relaciones |
| Carga ansiosa (eager loading) | `with(['cliente', 'turnos'])` |
