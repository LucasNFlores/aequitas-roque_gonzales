# Conservar el historial de ediciones de reportes (CU22)

## Objetivo

Completar CU22 para que cada cambio autorizado al contenido o fecha de un reporte conserve una versión anterior con el actor y la fecha del cambio, y que las versiones estén disponibles solo mediante CU24 y el alcance de consulta correspondiente.

## Alcance

- Profesional autor y Administrador pueden editar reportes; la autorización se valida también en la petición directa.
- Cada edición que cambie contenido o fecha conserva la versión anterior, el actor y el instante del cambio. Una petición sin cambios no crea versión.
- El historial admite varias versiones y se conserva si CU23 da de baja lógicamente el reporte.
- Profesional consulta solo sus reportes dentro de procesos asignados; Coordinador, Directivo y Administrador consultan según CU24. Secretario no accede.
- Mostrar el historial desde el legajo y proteger su URL directa con CU24 y alcance del proceso.

## Fuera de alcance

- Notificaciones, restauración de reportes, cambios a la matriz, ampliación de CU24, auditoría genérica o eliminación física.

## Criterios de salida

- Versiones inmutables guardan contenido/fecha anteriores, autor de la edición y fecha-hora del cambio.
- Múltiples ediciones generan múltiples versiones; una edición sin diferencias no agrega filas.
- La baja lógica CU23 no elimina versiones.
- Actualizar y consultar historial valida permisos por petición directa y respeta autoría, rol y proceso asignado.
- La vista muestra historial únicamente a quienes pueden consultar ese reporte por CU24.
- Pruebas PHPUnit focalizadas, Pint, build/manifest y verificación de navegador completan correctamente.

## Riesgos

- Una grabación parcial podría dejar una versión sin su reporte editado o a la inversa; ambas escrituras deben ser transaccionales.
- El historial puede revelar contenido jurídico si se comprueba solo el rol y no el acceso al reporte y proceso.
- El Administrador puede editar un reporte ajeno, por lo que autor de la edición debe distinguirse del profesional autor del reporte.
