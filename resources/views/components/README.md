# Componentes Blade

Antes de crear o modificar un componente, revisar esta estructura y guardar el
archivo en la carpeta que corresponda a su responsabilidad. No agregar archivos
`.blade.php` directamente en `resources/views/components/` salvo que se defina una
excepción documentada.

## Estructura

```text
resources/views/components/
├── branding/       Identidad visual y logotipos.
├── buttons/        Botones reutilizables.
├── feedback/       Mensajes de estado, éxito y error.
├── forms/          Labels, inputs y errores de formularios.
├── navigation/     Enlaces, menús y navegación.
└── overlays/       Modales, diálogos y capas superpuestas.
```

## Cómo se invocan

La carpeta se refleja en el nombre del componente Blade:

| Archivo | Uso en Blade |
| --- | --- |
| `forms/input-label.blade.php` | `<x-forms.input-label>` |
| `forms/text-input.blade.php` | `<x-forms.text-input>` |
| `forms/input-error.blade.php` | `<x-forms.input-error>` |
| `buttons/primary-button.blade.php` | `<x-buttons.primary-button>` |
| `navigation/nav-link.blade.php` | `<x-navigation.nav-link>` |
| `overlays/modal.blade.php` | `<x-overlays.modal>` |

Los componentes de clase se mantienen separados en
`app/View/Components/`. Por ejemplo, `AppLayout` y `GuestLayout` se invocan como
`<x-app-layout>` y `<x-guest-layout>` porque tienen una clase PHP asociada.

## Reglas de organización

1. Revisar primero si ya existe un componente reutilizable antes de crear otro.
2. Ubicar cada componente según su función principal, no según la pantalla que
   actualmente lo utiliza.
3. Mantener nombres en kebab-case para los archivos Blade.
4. Si el componente se usa en varias pantallas, conservarlo en esta carpeta; si
   pertenece a un único módulo, guardarlo junto con las vistas de ese módulo.
5. Al mover un componente, actualizar todas sus invocaciones `<x-...>` con
   `rg` antes de ejecutar las pruebas.
6. Revisar el contenido del componente para saber si utiliza Alpine.js, Livewire
   o sólo Blade. La carpeta y el prefijo `x-` no determinan por sí solos la
   tecnología utilizada.

## Componentes estáticos

Las vistas de `servicios-viejo` no utilizan estos componentes. Su layout y sus
formularios están escritos con `@extends`, `@section` y HTML nativo para que la
ruta no cargue Alpine.js ni Livewire.
