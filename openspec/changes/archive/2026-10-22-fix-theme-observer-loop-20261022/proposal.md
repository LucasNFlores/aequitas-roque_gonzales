# Evitar que el selector de tema bloquee el dashboard

## Why

Permitir que el dashboard autenticado termine de cargar y siga siendo interactivo cuando aparece el selector de tema.

## What Changes

El observador global de mutaciones llama a `syncThemeToggle`, que reemplaza el texto del selector aunque no haya cambiado. Esa escritura vuelve a disparar el mismo observador y mantiene ocupado el hilo principal del navegador.

## Alcance

- Sincronizar el selector únicamente en la inicialización, al cambiar el tema y durante la navegación de Livewire.
- Mantener el cambio claro/oscuro y la preferencia guardada en `localStorage`.
- Verificar la carga del dashboard en navegador y la prueba PHPUnit focalizada.

## Fuera de alcance

- Cambiar estilos, colores, almacenamiento de la preferencia o comportamiento de autenticación.
- Optimizar consultas o recursos ajenos al ciclo de mutaciones.

## Criterios de salida

- El dashboard carga y responde a interacciones sin un ciclo continuo de mutaciones.
- El selector cambia entre claro y oscuro y conserva su etiqueta e icono correctos.
- La prueba de layout, el build de Vite y la comprobación de navegador terminan correctamente.

## Riesgos

- Si Livewire reemplaza la navegación sin emitir `livewire:navigated`, el control podría quedar sin sincronizar. El evento ya existente cubre la navegación soportada por la aplicación.
