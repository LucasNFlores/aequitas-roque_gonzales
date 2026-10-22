# Propuesta: habilitar el informe documental al Directivo

## Problema

El informe PDF documental de CU 39 está disponible para Coordinador y Administrador, pero la matriz Aequitas debe incluir también al Directivo. La autorización actual depende de `gestionar_categorias_documentos`, lo que mezcla consulta de informes con administración de categorías.

## Alcance

- Habilitar al Directivo para consultar el informe y descargar su PDF.
- Mantener acceso para Coordinador y Administrador.
- Introducir un permiso específico de consulta para no otorgar al Directivo facultades de gestión de categorías ni acceso adicional a archivos de legajos.
- Actualizar menú, rutas, controlador, RoleSeeder, pruebas, OpenSpec y documentación local; reflejar el rol en la matriz de Drive.

## Enfoque

Crear `consultar_informe_documental`, asignarlo a Coordinador y Directivo y conservar al Administrador por su acceso de superusuario. La misma autorización protegerá la vista, la descarga directa y los enlaces del menú. La gestión CRUD de categorías conservará su permiso actual.

## Criterios de salida

- Directivo, Coordinador y Administrador pueden abrir el informe y generar su PDF.
- Secretario y Profesional reciben 403 en ambas rutas y no ven el enlace.
- El Directivo no obtiene `gestionar_categorias_documentos`, `visualizar_documentacion` ni `descargar_documentacion` por este cambio.
- La matriz online, su transcripción local, OpenSpec y el seeder reflejan el alcance acordado.
- Pasa la prueba PHPUnit focalizada, Pint, y `git diff --check`.

## Riesgos

- Reutilizar el permiso de gestión expondría acciones administrativas; se evita con un permiso separado.
- El Administrador se mantiene autorizado mediante la regla existente de superusuario.
