# Diseño: eliminar el ciclo del observador del selector de tema

## Contexto

El layout observa mutaciones de todos los nodos de `document.documentElement`. Cada sincronización escribe el `textContent` de la etiqueta del botón; esa operación genera otra mutación y la sincronización se repite sin fin en las páginas autenticadas.

## Decisión

Eliminar el `MutationObserver` global. La etiqueta e iconos ya se sincronizan en `applyTheme`, `DOMContentLoaded`, `pageshow` y `livewire:navigated`, que cubren los cambios de tema y los ciclos de navegación usados por el layout.

## Compatibilidad

No cambian los valores de `localStorage`, el atributo `data-theme`, las clases CSS, el control accesible ni la persistencia entre páginas.

## Riesgo y mitigación

Una inserción arbitraria del botón sin evento de navegación podría no sincronizarlo; la navegación de Livewire existente emite `livewire:navigated`, y la carga inicial cubre `DOMContentLoaded`/`pageshow`.
