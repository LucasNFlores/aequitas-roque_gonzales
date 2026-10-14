# Diseño: historial de ediciones de reportes

## Persistencia

- Añadir una tabla de versiones con referencia al reporte, contenido y fecha anteriores, ID y nombre capturado del actor, y fecha-hora del cambio.
- La relación con `Reporte` conserva versiones durante la baja lógica. El nombre capturado mantiene atribución aunque la cuenta del actor se dé de baja o elimine.
- Cada actualización compara los campos persistidos con los valores validados y, si existe una diferencia, crea el snapshot y actualiza el reporte dentro de una misma transacción.

## Autorización y alcance

- Mantener la policy CU22: Profesional autor o Administrador puede editar; la policy se aplica en la petición directa además del permiso de ruta.
- Proteger lectura del historial con el permiso de CU24 y `ReportePolicy::view`; Profesional debe ser autor y estar asignado al proceso vigente. Coordinador, Directivo y Administrador consultan los reportes permitidos en los procesos alcanzables. Secretario no tiene permiso.
- No exponer versiones en la respuesta a usuarios que no puedan consultar el reporte por CU24. Mantener la baja lógica CU23 y sus permisos actuales.

## Interfaz

- Agregar acceso a versiones anteriores junto al reporte en el legajo cuando la policy de consulta lo permita.
- Presentar cada snapshot con contenido, fecha del reporte, actor y fecha-hora de edición.
- Seguir estilos existentes de Blade/Tailwind y no incorporar notificaciones.

## Verificación

- Probar edición real, cambios múltiples, no-op, actor correcto y retención tras baja lógica.
- Probar rutas directas CU22/CU24 para Profesional autor, Profesional ajeno/no asignado, Coordinador, Directivo, Administrador y Secretario.
- Revisar interfaz autenticada en navegador contra una instalación ejecutándose.
