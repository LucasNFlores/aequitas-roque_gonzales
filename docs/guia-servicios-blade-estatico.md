# Gestión de servicios con Blade y Laravel

La ruta `/servicios-viejo` implementa el CRUD clásico del módulo de servicios.
Esta versión utiliza únicamente Laravel del lado del servidor y vistas Blade.
No carga Alpine.js, Livewire ni JavaScript para controlar la interfaz.

## Estructura

| Elemento | Archivo | Responsabilidad |
| --- | --- | --- |
| Rutas | `routes/web.php` | Declara las rutas REST del recurso. |
| Controlador | `app/Http/Controllers/ServicioController.php` | Consulta, crea, actualiza y elimina servicios. |
| Validación de alta | `app/Http/Requests/StoreServicioRequest.php` | Valida los datos recibidos al crear. |
| Validación de edición | `app/Http/Requests/UpdateServicioRequest.php` | Valida los datos recibidos al editar. |
| Modelo | `app/Models/Servicio.php` | Representa la tabla `servicios`. |
| Layout | `resources/views/layouts/static.blade.php` | Estructura HTML común sin scripts interactivos. |
| Navegación | `resources/views/layouts/static-navigation.blade.php` | Menú compuesto por enlaces y formularios HTML. |
| Listado | `resources/views/servicios-viejo/index.blade.php` | Muestra los servicios paginados. |
| Alta | `resources/views/servicios-viejo/create.blade.php` | Muestra el formulario de creación. |
| Edición | `resources/views/servicios-viejo/edit.blade.php` | Muestra el formulario de modificación. |
| Formulario | `resources/views/servicios-viejo/_form.blade.php` | Reutiliza los campos de alta y edición. |

## Flujo del listado

1. El navegador solicita `GET /servicios-viejo`.
2. `ServicioController@index()` consulta los servicios con paginación.
3. El controlador devuelve `servicios-viejo.index` con la variable `$servicios`.
4. Blade genera el HTML completo en el servidor.
5. El navegador recibe y muestra la página.

La paginación genera enlaces normales. Al seleccionar otra página se realiza una
nueva petición HTTP y Laravel vuelve a renderizar la vista completa.

## Flujo de creación

1. El enlace `Nuevo Servicio` solicita `GET /servicios-viejo/create`.
2. `ServicioController@create()` devuelve la vista con el formulario.
3. El formulario se envía mediante `POST /servicios-viejo`.
4. `StoreServicioRequest` valida los campos antes de ejecutar `store()`.
5. `ServicioController@store()` utiliza únicamente `$request->validated()`.
6. Laravel guarda el servicio y redirige al listado con un mensaje de sesión.
7. El listado muestra el mensaje `success` en el siguiente renderizado.

El formulario usa `@csrf` para proteger la petición y no necesita `wire:submit`,
`wire:model`, `x-data` ni eventos JavaScript.

Las vistas de esta ruta tampoco utilizan etiquetas de componentes Blade con
prefijo `x-`. Ese prefijo no significa Alpine por sí mismo: por ejemplo,
`<x-app-layout>` es un componente Blade. Sin embargo, se evita aquí para que la
ruta sea explícitamente HTML, directivas Blade y comportamiento del servidor.

## Flujo de edición

1. El enlace `Editar` solicita `GET /servicios-viejo/{servicio}/edit`.
2. Laravel resuelve `{servicio}` mediante binding implícito del modelo.
3. `ServicioController@edit()` devuelve el formulario con el servicio cargado.
4. El formulario envía `POST` junto con `@method('PUT')`.
5. `UpdateServicioRequest` valida los campos.
6. `ServicioController@update()` actualiza el modelo y redirige al listado.

## Flujo de eliminación

1. El listado contiene un formulario HTML con `method="POST"`.
2. `@method('DELETE')` convierte la petición en `DELETE` para Laravel.
3. `@csrf` protege la petición contra falsificación.
4. `ServicioController@destroy()` recibe el modelo mediante binding.
5. `$servicio->delete()` aplica la baja lógica configurada en el modelo.
6. Laravel redirige al listado y muestra el resultado mediante sesión.

La confirmación no se implementa con Alpine. Si se requiere confirmación visual,
debe resolverse mediante una página o formulario adicional del lado del servidor.
El enfoque actual prioriza una interfaz completamente estática.

## Diferencia con `/servicios`

`/servicios` conserva la versión interactiva con Livewire y Alpine.js: actualiza
partes de la pantalla sin recargarla y utiliza modales controlados en el navegador.

`/servicios-viejo` utiliza el flujo MVC tradicional y carga únicamente la hoja de
estilos mediante Vite; no carga `resources/js/app.js`, Alpine.js ni Livewire:

```text
Ruta → Controlador → Form Request → Modelo → Redirección → Vista Blade completa
```

Ambas versiones utilizan el mismo modelo, las mismas reglas de validación y las
mismas restricciones de autorización del módulo.
