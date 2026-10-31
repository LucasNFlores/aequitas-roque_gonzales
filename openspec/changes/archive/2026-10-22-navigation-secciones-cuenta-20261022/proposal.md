# Agrupar navegación y desplegar acciones de cuenta

## Why

La navegación lateral presenta los destinos operativos en una lista continua y las acciones de tema, perfil y cierre de sesión ocupan espacio fijo aunque se usen ocasionalmente.

## What Changes

- Agrupar los enlaces autorizados en Inicio, Gestión jurídica, Administración e Informes.
- Mostrar un encabezado solo cuando el usuario tenga al menos un destino autorizado dentro de ese grupo.
- Mover Tema, Perfil y Cerrar sesión a un desplegable activado desde la tarjeta que muestra la sesión actual.
- Mantener intactas las rutas y las reglas de autorización del servidor.

## Enfoque

Actualizar la navegación lateral autenticada con encabezados de sección accesibles y una tarjeta de usuario que expande las acciones de cuenta. Mantener la navegación estática y el conjunto de destinos autorizados existente.

## Riesgos

- Un cambio de agrupación podría ocultar accidentalmente un enlace para algún rol; las pruebas de matriz por rol deben seguir cubriendo ambos formatos de navegación.
- El desplegable debe funcionar con teclado y exponer su estado mediante atributos ARIA.
