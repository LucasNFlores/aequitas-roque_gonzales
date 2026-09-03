# Organización de componentes Blade

## Instrucción para el equipo

Antes de crear o modificar un componente, revisar la carpeta
`resources/views/components/` y este documento. Cada componente debe guardarse en
la subcarpeta que corresponda a su responsabilidad. La carpeta raíz no debe
convertirse en un listado plano de archivos.

## Mapa de carpetas

| Carpeta | Contenido | Ejemplos |
| --- | --- | --- |
| `branding` | Logotipos e identidad visual. | `application-logo.blade.php` |
| `buttons` | Botones reutilizables. | `primary-button`, `secondary-button`, `danger-button` |
| `feedback` | Mensajes de estado y notificaciones visuales. | `auth-session-status` |
| `forms` | Labels, inputs y errores de validación. | `input-label`, `text-input`, `input-error` |
| `navigation` | Enlaces, menús y navegación responsive. | `nav-link`, `dropdown`, `dropdown-link` |
| `overlays` | Modales y diálogos superpuestos. | `modal` |

La estructura actual es:

```text
resources/views/components/
├── branding/
├── buttons/
├── feedback/
├── forms/
├── navigation/
└── overlays/
```

## Convención de nombres

La ruta relativa del archivo se convierte en el nombre del componente Blade:

```text
resources/views/components/forms/text-input.blade.php
        ↓
<x-forms.text-input>
```

Al cambiar la ubicación de un archivo, hay que actualizar todas sus referencias
en `resources/views/`, `app/` y las pruebas. Las búsquedas recomendadas son:

```bash
rg -n "<x-|</x-" resources/views app tests
rg --files resources/views/components
```

## Componentes Blade y Alpine.js

El prefijo `x-` identifica un componente Blade cuando aparece como
`<x-forms.text-input>`. No significa automáticamente que el componente utilice
Alpine.js.

Alpine.js se reconoce por directivas como:

```blade
x-data
x-show
x-on:click
@click
```

Por eso, cada programador debe revisar el contenido del archivo antes de
reutilizarlo en una vista que deba ser estática.

## Componentes de clase

Los componentes de clase PHP se encuentran en `app/View/Components/`. No deben
mezclarse con los componentes anónimos de `resources/views/components/` sin una
razón clara.

Ejemplos actuales:

```text
app/View/Components/AppLayout.php
app/View/Components/GuestLayout.php
```

Sus nombres se resuelven como `<x-app-layout>` y `<x-guest-layout>`.

## Excepción: `/servicios-viejo`

La ruta `/servicios-viejo` es un CRUD MVC server-rendered y no carga componentes
interactivos. Utiliza `resources/views/layouts/static.blade.php`, directivas Blade,
HTML nativo, Form Requests y el `ServicioController`.

No debe agregarse allí:

- Alpine.js.
- Livewire.
- `resources/js/app.js`.
- Directivas `x-data`, `x-show`, `x-on`, `@click` o `wire:*`.

Esta excepción está documentada en
`docs/guia-servicios-blade-estatico.md` y debe mantenerse al modificar esa ruta.
