# HU-14 — Análisis y Diseño: Administrar Categorías de Documentos (CU37)

## 1. Historia
**Como** coordinador o administrador **quiero** crear, modificar, activar y desactivar categorías de documentos **para** clasificar consistentemente la documentación de los legajos.

Fuente: Matriz Aequitas V2, CU37. Sin borrado físico.

## 2. Alcance confirmado
- CRUD de categorías/tipos documentales con `nombre` único (case-insensitive, trimmeado) + `estado` activo/inactivo.
- `categoria_id` obligatoria en `documentos` para nuevas cargas (CU9, CU13).
- Baja lógica: inactiva no se ofrece en cargas nuevas, conserva historial.
- Validar: vacíos, duplicados, edición sin romper FK, reactivación, en uso.
- Campos mínimos acordados: `id, nombre, descripcion(nullable), activo(default true), created_by/updated_by(nullable), timestamps`.

## 3. Modelo de datos

```
categorias_documento (1) ───< (N) documentos
```

```sql
categorias_documento(id PK, nombre VARCHAR(100) UNIQUE, descripcion VARCHAR(255) NULL,
  activo BOOLEAN DEFAULT TRUE, created_by INT NULL, updated_by INT NULL,
  created_at, updated_at)
documentos.categoria_id INT NOT NULL → FK → categorias_documento.id
  ON UPDATE CASCADE ON DELETE RESTRICT
```

Collation `utf8mb4_unicode_ci`: el UNIQUE ya es case-insensitive (`DNI = dni`).
Índice adicional recomendado: `INDEX idx_cat_activo_nombre (activo, nombre)` para el dropdown de carga.

Seed inicial: `Identidad`, `Comprobante ARCA`, `Informe profesional`, `Contrato/Honorarios`, `Judicial` (todas activas).

## 4. Reglas de negocio (RN)
- RN1 Normalización: `trim()` + colapsar espacios múltiples antes de validar/guardar.
- RN2 Unicidad: `nombre` min 3, max 100, único sin distinguir mayúsculas/espacios.
- RN3 Edición no cambia `id`; los documentos conservan `categoria_id`.
- RN4 Desactivar siempre permitido (aun en uso); solo oculta de cargas nuevas.
- RN5 Reactivar vuelve a ofrecerla.
- RN6 Prohibido `DELETE` físico. `ON DELETE RESTRICT` como resguardo DB.
- RN7 Mensajes accionables:
  - vacío → "El nombre es obligatorio."
  - duplicado → "Ya existe una categoría con ese nombre. Use otro nombre o reactive la existente."
  - en uso al desactivar → "Tiene X documentos históricos que se conservarán. Dejará de ofrecerse para nuevas cargas."

## 5. CU37 — Autorización (Matriz V2)
| Rol | Gestionar categorías |
|---|---|
| Coordinador | ✅ crear, editar, activar, desactivar, listar |
| Administrador | ✅ igual |
| Secretario, Profesional, Directivo | ❌ 403 incluso por URL / POST directo |
| Invitado | redirect login |

Aplicación en 3 capas: `middleware role` en rutas + `$this->authorize()` en controller/policy + ocultamiento de menú/UI + test directo.

## 6. Diseño de API / Rutas (web.php)
```
GET    /categorias                      → index
GET    /categorias/crear                → create
POST   /categorias                      → store
GET    /categorias/{id}/editar          → edit
PUT    /categorias/{id}                 → update
PATCH  /categorias/{id}/activar         → activate
PATCH  /categorias/{id}/desactivar      → deactivate
```
Todas con `['auth', 'role:coordinador,administrador']`. Sin `destroy`.

## 7. UI
- **Listado** (`index`): tabla Nombre | Descripción | Estado (badge) | N.º docs | Acciones. Filtro Todas/Activas/Inactivas. Botón "Nueva categoría". Solo menú coord/admin.
- **Crear/Editar** (`form`): input nombre + textarea descripcion + errores inline. Banner si `enUso`: "Tiene X documentos asociados. La edición no afectará el historial."
- **Desactivar**: modal confirmación con conteo de docs históricos.
- **Carga de documento** (CU9/CU13): `<select>` alimentado solo con `CategoriaDocumento::activas()->ordenadas()->get()`. Documentos viejos con categoría inactiva se muestran como etiqueta gris readonly.

## 8. Matriz de pruebas (criterios Dado/Cuando/Entonces)
| # | Criterio HU | Test |
|---|---|---|
| CA1 | Autorizado registra válida → se guarda y disponible | `test_coordinador_puede_crear_y_queda_disponible` |
| CA2 | Modificar → actualiza sin perder asociaciones | `test_editar_no_rompe_asociaciones` |
| CA3 | Desactivar en uso → conserva historial, oculta en cargas | `test_desactivar_en_uso_conserva_historial_y_oculta` + `test_reactivar_vuelve_a_ofrecerse` |
| CA4 | Duplicado → informa duplicidad | `test_no_permite_duplicados_case_insensitive` + `test_valida_nombre_vacio` |
| SEG | Roles no autorizados denegados por URL directa | `test_roles_no_autorizados_reciben_403_en_todo` (secretario, profesional, directivo × index/store/update/desactivar) |
| SEG | Invitado → login | `test_invitado_redirige_login` |
| INT | Solo activas en carga nueva | `test_solo_activas_en_carga_nueva` |

## 9. DoD — verificación
- [x] Migración + modelo + FK RESTRICT + seed
- [x] Validaciones (FormRequests) con mensajes accionables
- [x] Policy + rutas + controller + UI con control por rol
- [x] Baja lógica + exclusión de inactivas en cargas
- [x] Tests de roles y aceptación (ejecutar `php artisan test --filter=CategoriaDocumentoTest`)
