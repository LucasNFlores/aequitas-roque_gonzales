# Guía de Laravel: MVC, Blade, Livewire, Alpine y APIs

Esta guía explica cómo se organiza una pantalla de Laravel y qué ocurre cuando el usuario interactúa con ella. Está pensada como referencia para quienes están comenzando a trabajar con el proyecto.

## 1. Qué significa MVC

MVC significa **Modelo, Vista y Controlador**.

```text
Usuario
  ↓
Ruta
  ↓
Controlador o componente Livewire
  ↓
Modelo
  ↓
Base de datos
```

### Modelo

El modelo representa una entidad del sistema y permite trabajar con la base de datos mediante Eloquent.

Ejemplo: `app/Models/Servicio.php` representa un servicio ofrecido por el estudio.

```php
$servicios = Servicio::latest()->paginate(10);
```

La consulta a la base de datos debe estar en una clase PHP, no dentro de una vista Blade.

### Vista

La vista construye el HTML que verá el usuario. En Laravel normalmente se escribe con Blade:

```text
resources/views/
```

Una vista puede mostrar datos, formularios y botones, pero no debería contener reglas de negocio ni consultas directas a la base de datos.

### Controlador

El controlador recibe una petición, valida los datos, llama al modelo y decide qué respuesta devolver.

Ejemplo del CRUD clásico:

```php
public function store(StoreServicioRequest $request): RedirectResponse
{
    Servicio::create($request->validated());

    return redirect()->route('servicios-viejo.index');
}
```

## 2. Cómo funciona una vista Blade tradicional

En el CRUD clásico de `/servicios-viejo`, cada acción genera una petición HTTP completa:

```text
GET /servicios-viejo
  → ServicioController@index
  → consulta servicios
  → devuelve una vista Blade

GET /servicios-viejo/create
  → ServicioController@create
  → devuelve el formulario

POST /servicios-viejo
  → ServicioController@store
  → valida y guarda
  → redirige al listado
```

La página se vuelve a cargar después de crear, editar o eliminar. Este enfoque es simple y sigue el MVC tradicional.

## 3. Qué es Livewire

Livewire permite usar componentes PHP para crear interfaces interactivas sin construir un frontend separado con React o Vue.

Un componente Livewire tiene dos partes:

```text
app/Livewire/Servicios/Index.php
  → estado y lógica del servidor

resources/views/livewire/servicios/index.blade.php
  → HTML e interfaz
```

El componente `Index` mantiene propiedades como:

```php
public string $nombre = '';
public string $costoServicio = '';
public string $search = '';
```

Cuando el usuario realiza una acción, Livewire envía una petición interna al servidor, ejecuta un método PHP y actualiza sólo las partes necesarias de la página.

## 4. Las directivas `wire:` no son llamadas API manuales

En una vista Livewire pueden aparecer instrucciones como estas:

```blade
<button wire:click="editServicio({{ $servicio->id }})">
    Editar
</button>

<form wire:submit="saveServicio">
    <!-- campos -->
</form>
```

Parecen llamadas a una API, pero no son endpoints API escritos por el desarrollador. Son directivas que conectan HTML con métodos PHP del componente Livewire.

El flujo es:

```text
El usuario hace clic
  ↓
Livewire envía una petición interna
  ↓
Se ejecuta un método PHP
  ↓
Se verifica el permiso y se validan los datos
  ↓
El modelo consulta o modifica la base de datos
  ↓
Livewire devuelve los cambios de HTML
```

Por ejemplo, esta directiva:

```blade
wire:click="createServicio"
```

ejecuta este método:

```php
public function createServicio(): void
{
    $this->authorizeManagement();
    $this->resetForm();
    $this->showModal = true;
}
```

La vista sólo indica qué acción debe ejecutarse. La autorización y la lógica siguen estando en PHP.

## 5. Qué hace Alpine.js

Alpine.js controla interacciones pequeñas que no necesitan consultar al servidor.

En `/servicios`, Alpine controla si los modales están abiertos:

```blade
<div x-data="{ formOpen: false }">
    <button @click="formOpen = true">
        Nuevo Servicio
    </button>

    <div x-show="formOpen">
        <!-- contenido del modal -->
    </div>
</div>
```

En este caso:

- `x-data` define el estado local del navegador.
- `@click` cambia ese estado inmediatamente.
- `x-show` muestra u oculta un elemento.
- `x-transition` agrega una transición visual.
- `x-cloak` evita que el modal aparezca brevemente antes de iniciar Alpine.

Alpine no debe reemplazar las validaciones ni los permisos del servidor. Sólo controla la experiencia visual.

## 6. Por qué se combinan Alpine y Livewire

Cada herramienta resuelve una responsabilidad diferente:

| Herramienta | Responsabilidad |
|---|---|
| Laravel MVC | Rutas, modelos, controladores y reglas generales |
| Blade | HTML inicial y presentación |
| Tailwind CSS | Estilos y diseño responsive |
| Alpine.js | Interacciones locales e inmediatas |
| Livewire | Operaciones que necesitan el servidor |

Al presionar **Nuevo Servicio**:

1. Alpine abre el modal inmediatamente.
2. Livewire prepara el formulario y verifica el permiso.
3. El usuario completa los campos.
4. `wire:submit` envía el formulario sin recargar toda la página.
5. Livewire valida y guarda usando el modelo `Servicio`.
6. Livewire informa el resultado y Alpine cierra el modal.

## 7. Qué no se debe hacer en una vista

No se deben colocar consultas o reglas de negocio directamente en Blade:

```blade
{{-- Evitar --}}
{{ DB::table('servicios')->get() }}
```

Tampoco se deben realizar llamadas HTTP externas desde la vista:

```blade
{{-- Evitar --}}
{{ Http::get('https://ejemplo.com/servicios') }}
```

La vista debe mostrar datos y emitir acciones. Las consultas deben estar en modelos, controladores, componentes Livewire o clases de servicio.

## 8. MVC, Livewire y API no son opciones excluyentes

Usar Livewire no convierte automáticamente la aplicación en una API. El proyecto continúa usando Laravel MVC.

Una API separada suele ser necesaria cuando:

- Existe una aplicación móvil.
- Hay varios clientes frontend independientes.
- React, Vue o Angular se despliegan como una aplicación separada.
- Otros sistemas externos necesitan consumir los datos.

Para una pantalla administrativa de un sistema Laravel, Blade + Alpine + Livewire suele ser suficiente y evita mantener un frontend y un backend completamente separados.

Si en el futuro se necesita React sin crear una API separada, una alternativa intermedia es usar Inertia. Si se necesita un frontend totalmente independiente, entonces Laravel puede funcionar como backend API.

## 9. Formato del código con Pint

Laravel Pint formatea automáticamente el código PHP. Es una herramienta de desarrollo y no afecta el funcionamiento de la aplicación.

Con PHP y Composer instalados localmente:

```bash
vendor/bin/pint --dirty --format agent
```

Con Docker:

```bash
docker compose exec -T laravel.test vendor/bin/pint --dirty --format agent
```

Se utiliza una sola opción, según el entorno de cada desarrollador. No es necesario usar Docker para ejecutar Pint.

## 10. Regla práctica para decidir dónde colocar cada cosa

Antes de agregar código, hacerse estas preguntas:

1. ¿Es sólo una decisión visual o una interacción local? Usar Alpine.js.
2. ¿Necesita consultar, validar o guardar en el servidor? Usar Livewire.
3. ¿Es una página simple sin interacción dinámica? Usar Blade + Tailwind.
4. ¿Es una regla de negocio o una consulta? Colocarla en PHP, no en Blade.
5. ¿Necesitan consumirla otros sistemas o aplicaciones? Evaluar crear una API.

Esta separación mantiene el código más fácil de entender, probar y mantener.
