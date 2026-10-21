# Eliminar la Guía Técnica

## Problema

La guía técnica ya no es necesaria, pero sigue publicada en `/tutorial`, aparece en ambas variantes de navegación y la especificación vigente exige mantenerla disponible.

## Alcance

- Eliminar la ruta autenticada y la vista de la guía.
- Quitar el enlace de ambas navegaciones.
- Ajustar las pruebas para cubrir la ausencia del enlace y la respuesta 404.
- Actualizar el requisito vigente de navegación.

## Restricciones

- Mantener sin cambios las demás pantallas y destinos.
- Conservar las decisiones históricas ya completadas de HU-21.

## Áreas afectadas

Rutas web, layouts Blade, prueba de navegación y especificación de Gestión jurídica.

## Enfoque

Retirar ruta, enlaces y vista; validar el comportamiento HTTP y ambas navegaciones por rol.

## Riesgos

Los accesos guardados a `/tutorial` dejarán de funcionar y devolverán 404.
