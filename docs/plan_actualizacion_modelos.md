# Plan: Actualización de Modelos Eloquent

## Objetivo

Completar los modelos en `app/Models/` según el schema definido en `docs/db/modelo_db_io.md`.

---

## Pasos de implementación (commits atómicos)

### Paso 1: Modelo Cliente

**Commit:** `feat(models): add fillable, SoftDeletes and casts to Cliente`

**Archivo:** `app/Models/Cliente.php`

**Cambios:**
- Importar `Illuminate\Database\Eloquent\SoftDeletes`
- Agregar trait `SoftDeletes` a la clase
- Agregar `$fillable`: `nombre`, `apellido`, `dni`, `telefono`, `correo`, `domicilio`, `fecha_nacimiento`
- Agregar `$casts`: `fecha_nacimiento => date`

**Test:** `php artisan tinker --execute="App\Models\Cliente::factory()->make()->toArray();"`

---

### Paso 2: Relationships de Cliente

**Commit:** `feat(models): add relationships to Cliente`

**Archivo:** `app/Models/Cliente.php`

**Agregar métodos:**
```php
public function procesos(): BelongsToMany
{
    return $this->hasMany(Proceso::class);
}

public function comprobantesPago(): HasMany
{
    return $this->hasMany(ComprobantePago::class);
}

public function notificaciones(): HasMany
{
    return $this->hasMany(Notificacione::class);
}

public function turnos(): HasMany
{
    return $this->hasMany(Turno::class);
}
```

**Test:** `php artisan tinker --execute="App\Models\Cliente::first()->procesos;"`

---

### Paso 3: Modelo Servicio

**Commit:** `feat(models): add fillable, SoftDeletes and casts to Servicio`

**Archivo:** `app/Models/Servicio.php`

**Cambios:**
- Importar `Illuminate\Database\Eloquent\SoftDeletes`
- Agregar trait `SoftDeletes`
- Agregar `$fillable`: `nombre`, `costo_servicio`
- Agregar `$casts`: `costo_servicio => decimal:2`

**Test:** `php artisan tinker --execute="App\Models\Servicio::factory()->make()->toArray();"`

---

### Paso 4: Relationships de Servicio

**Commit:** `feat(models): add relationships to Servicio`

**Agregar método:**
```php
public function procesos(): HasMany
{
    return $this->hasMany(Proceso::class);
}
```

**Test:** `php artisan tinker --execute="App\Models\Servicio::first()->procesos;"`

---

### Paso 5: Modelo Proceso

**Commit:** `feat(models): add fillable, SoftDeletes and casts to Proceso`

**Archivo:** `app/Models/Proceso.php`

**Cambios:**
- Importar `SoftDeletes`
- Agregar trait `SoftDeletes`
- Agregar `$fillable`: `cliente_id`, `profesional_id`, `servicio_id`, `coordinador_id`, `nombre`, `descripcion`, `fecha_inicio`, `tipo`, `estado`, `motivo_rechazo`
- Agregar `$casts`: `fecha_inicio => date`

**Test:** `php artisan tinker --execute="App\Models\Proceso::factory()->make()->toArray();"`

---

### Paso 6: Relationships de Proceso

**Commit:** `feat(models): add relationships to Proceso`

**Agregar métodos:**
```php
public function cliente(): BelongsTo
{
    return $this->belongsTo(Cliente::class);
}

public function profesional(): BelongsTo
{
    return $this->belongsTo(User::class, 'profesional_id');
}

public function coordinador(): BelongsTo
{
    return $this->belongsTo(User::class, 'coordinador_id');
}

public function servicio(): BelongsTo
{
    return $this->belongsTo(Servicio::class);
}

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

**Test:** `php artisan tinker --execute="App\Models\Proceso::first()->cliente;"`

---

### Paso 7: Modelo Turno

**Commit:** `feat(models): add fillable, SoftDeletes and casts to Turno`

**Archivo:** `app/Models/Turno.php`

**Cambios:**
- Importar `SoftDeletes`
- Agregar trait `SoftDeletes`
- Agregar `$fillable`: `cliente_id`, `profesional_id`, `proceso_id`, `fecha_hora`, `es_externo`, `detalle_externo`, `tipo`
- Agregar `$casts`: `fecha_hora => datetime`, `es_externo => boolean`

**Test:** `php artisan tinker --execute="App\Models\Turno::factory()->make()->toArray();"`

---

### Paso 8: Relationships de Turno

**Commit:** `feat(models): add relationships to Turno`

**Agregar métodos:**
```php
public function cliente(): BelongsTo
{
    return $this->belongsTo(Cliente::class);
}

public function profesional(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function proceso(): BelongsTo
{
    return $this->belongsTo(Proceso::class);
}
```

**Test:** `php artisan tinker --execute="App\Models\Turno::first()->cliente;"`

---

### Paso 9: Modelo Documento

**Commit:** `feat(models): add fillable, SoftDeletes and relationships to Documento`

**Archivo:** `app/Models/Documento.php`

**Cambios:**
- Importar `SoftDeletes`
- Agregar trait `SoftDeletes`
- Agregar `$fillable`: `proceso_id`, `archivo_path`, `tipo_documento`, `nombre`
- Agregar método `proceso()`: `return $this->belongsTo(Proceso::class);`

**Test:** `php artisan tinker --execute="App\Models\Documento::factory()->make()->toArray();"`

---

### Paso 10: Modelo Reporte

**Commit:** `feat(models): add fillable, SoftDeletes and relationships to Reporte`

**Archivo:** `app/Models\Reporte.php`

**Cambios:**
- Importar `SoftDeletes`
- Agregar trait `SoftDeletes`
- Agregar `$fillable`: `proceso_id`, `profesional_id`, `contenido`, `fecha`
- Agregar `$casts`: `fecha => date`
- Agregar métodos `proceso()` y `profesional()` (belongsTo)

**Test:** `php artisan tinker --execute="App\Models\Reporte::factory()->make()->toArray();"`

---

### Paso 11: Modelo ComprobantePago

**Commit:** `feat(models): add fillable, casts and relationships to ComprobantePago`

**Archivo:** `app/Models/ComprobantePago.php`

**Cambios:**
- Agregar `$fillable`: `cliente_id`, `archivo_path`, `fecha_subida`, `descripcion`
- Agregar `$casts`: `fecha_subida => date`
- Agregar método `cliente()` (belongsTo)

**Test:** `php artisan tinker --execute="App\Models\ComprobantePago::factory()->make()->toArray();"`

---

### Paso 12: Migration para SoftDeletes de Notificaciones

**Commit:** `feat(migration): add deleted_at to notificaciones table`

**Migration:** `php artisan make:migration add_deleted_at_to_notificaciones_table`

**En up():**
```php
Schema::table('notificaciones', function (Blueprint $table) {
    $table->softDeletes();
});
```

**En down():**
```php
Schema::table('notificaciones', function (Blueprint $table) {
    $table->dropSoftDeletes();
});
```

**Run:** `php artisan migrate`

---

### Paso 13: Modelo Notificacione

**Commit:** `feat(models): add fillable, SoftDeletes, casts and relationships to Notificacione`

**Archivo:** `app/Models/Notificacione.php`

**Cambios:**
- Importar `Illuminate\Database\Eloquent\SoftDeletes`
- Agregar trait `SoftDeletes`
- Agregar `$fillable`: `user_id`, `cliente_id`, `canal`, `mensaje`, `fecha_envio`, `estado`
- Agregar `$casts`: `fecha_envio => datetime`
- Agregar métodos `user()` y `cliente()` (belongsTo)

**Test:** `php artisan tinker --execute="App\Models\Notificacione::factory()->make()->toArray();"`

---

### Paso 14: Modelo User - SoftDeletes

**Commit:** `feat(models): add SoftDeletes to User`

**Archivo:** `app/Models/User.php`

**Cambios:**
- Importar `Illuminate\Database\Eloquent\SoftDeletes`
- Agregar trait `SoftDeletes` a la clase (ya tiene HasFactory, Notifiable, HasRoles)

---

### Paso 15: Relationships en User

**Commit:** `feat(models): add relationships to User`

**Archivo:** `app/Models/User.php`

**Agregar métodos:**
```php
public function turnos(): HasMany
{
    return $this->hasMany(Turno::class);
}

public function reportes(): HasMany
{
    return $this->hasMany(Reporte::class);
}

public function procesosComoProfesional(): HasMany
{
    return $this->hasMany(Proceso::class, 'profesional_id');
}

public function procesosComoCoordinador(): HasMany
{
    return $this->hasMany(Proceso::class, 'coordinador_id');
}

public function notificaciones(): HasMany
{
    return $this->hasMany(Notificacione::class);
}
```

**Test:** `php artisan tinker --execute="App\Models\User::first()->turnos;"`

---

## Resumen de pasos

| Paso | Commit | Archivos |
|------|--------|----------|
| 1 | feat(models): add fillable, SoftDeletes and casts to Cliente | Cliente.php |
| 2 | feat(models): add relationships to Cliente | Cliente.php |
| 3 | feat(models): add fillable, SoftDeletes and casts to Servicio | Servicio.php |
| 4 | feat(models): add relationships to Servicio | Servicio.php |
| 5 | feat(models): add fillable, SoftDeletes and casts to Proceso | Proceso.php |
| 6 | feat(models): add relationships to Proceso | Proceso.php |
| 7 | feat(models): add fillable, SoftDeletes and casts to Turno | Turno.php |
| 8 | feat(models): add relationships to Turno | Turno.php |
| 9 | feat(models): add fillable, SoftDeletes and relationships to Documento | Documento.php |
| 10 | feat(models): add fillable, SoftDeletes and relationships to Reporte | Reporte.php |
| 11 | feat(models): add fillable, casts and relationships to ComprobantePago | ComprobantePago.php |
| 12 | feat(migration): add deleted_at to notificaciones table | migration |
| 13 | feat(models): add fillable, SoftDeletes, casts and relationships to Notificacione | Notificacione.php |
| 14 | feat(models): add SoftDeletes to User | User.php |
| 15 | feat(models): add relationships to User | User.php |

---

## Verificación final

```bash
php artisan tinker --execute="
\$models = ['Cliente', 'Servicio', 'Proceso', 'Turno', 'Documento', 'Reporte', 'ComprobantePago', 'Notificacione', 'User'];
foreach (\$models as \$m) {
    echo \$m . ': ';
    try {
        \$class = 'App\\Models\\' . \$m;
        \$instance = \$class::factory()->make();
        echo 'OK - ' . count(\$instance->getAttributes()) . ' attrs' . PHP_EOL;
    } catch (\Exception \$e) {
        echo 'ERROR: ' . \$e->getMessage() . PHP_EOL;
    }
}
"
```
